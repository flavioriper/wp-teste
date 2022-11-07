<?php
/**
 * Admin Subscription class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Subscription\Admin;

use Exception;
use WC_Asaas\Helper\Subscriptions_Helper;
use WC_Asaas\Meta_Data\Subscription_Meta;
use WC_Asaas\Admin\Settings;
use WC_Asaas\Api\Api;
use WC_Asaas\Api\Response\Error_Response;

/**
 * Subscription Admin class
 */
class Subscription_Admin {

	/**
	 * Instance of this class
	 *
	 * @var self
	 */
	protected static $instance = null;


	/**
	 * Is not allowed to call from outside to prevent from creating multiple instances.
	 */
	private function __construct() {
	}

    /**
     * Prevent the instance from being cloned.
     */
    private function __clone() {
    }

    /**
     * Prevent from being unserialized.
	 * 
	 * @throws Exception If create a second instance of it.
     */
    public function __wakeup() {
        throw new Exception( __( 'Cannot unserialize singleton', 'woo-asaas' ) );
    }

	/**
	 * Return an instance of this class
	 *
	 * @return self A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Checks if date field was changed
	 *
	 * @param string $current_date   Current subscription date property in Unix timestamp format (e.g: 1660668703).
	 * @param string $submitted_date Submitted date property in Unix timestamp format (e.g: 1660668703).
	 * @return bool  True if date was changed. Otherwise, false.
	 */
	public function date_changed( $current_date, $submitted_date ) {
		// Converts both dates into GMT 0.
		$current_date   = date( 'Y-m-d H:i', $current_date );
		$submitted_date = date( 'Y-m-d H:i', $submitted_date );

		return $current_date !== $submitted_date;
	}

	/**
	 * Sends validation erro
	 *
	 * @param string $error_message The error message.
	 * @return void
	 */
	public function send_validation_error( $error_message ) {
		wp_die(
			'<b>' . esc_html__( 'Subscription update error:', 'woo-asaas' ) . '</b> ' . esc_html( $error_message ),
			esc_html__( 'Subscription update error', 'woo-asaas' ),
			array( 'back_link' => true )
		);
	}

	/**
	 * Validates changes rom subscription admin page
	 *
	 * @param int   $post_ID Current post ID.
	 * @param array $post_data Current post data.
	 * @return void
	 */
	public function validate_subscription_changes( $post_ID, $post_data ) {

		if ( ! is_admin() ) {
			return;
		}

		if ( 'shop_subscription' !== get_post_type( $post_ID ) ) {
			return;
		}

		if ( 'editpost' !== sanitize_text_field( $_POST['action'] ) ) {
			return;
		}

		// Subscription current object.
		$subscription = wcs_get_subscription( $post_ID );
		if ( false === $subscription ) {
			return;
		}

		// Is not using Asaas payment gateway?
		$payment_gateway = wc_get_payment_gateway_by_order( $subscription );
		if ( false === $payment_gateway || false === strpos( $payment_gateway->id, 'asaas-' ) ) {
			return;
		}

		// Submited subscription data.
		$_POST                  = wp_unslash( $_POST );
		$billing_interval       = sanitize_text_field( $_POST['_billing_interval'] );
		$billing_period         = sanitize_text_field( $_POST['_billing_period'] );
		$start_timestamp        = sanitize_text_field( $_POST['start_timestamp_utc'] ); // Timestamp for start date
		$trial_end_timestamp    = sanitize_text_field( $_POST['trial_end_timestamp_utc'] ); // Timestamp for trial end date
		$next_payment_timestamp = sanitize_text_field( $_POST['next_payment_timestamp_utc'] ); // Timestamp for subscription next payment
		$end_date_timestamp     = sanitize_text_field( $_POST['end_timestamp_utc'] ); // Timestamp for subscription end date

		// Validations
		$updated_subscription_data = array();
		if ( $this->date_changed( $subscription->get_time( 'start' ), $start_timestamp ) ) {
			$this->send_validation_error( __( 'Asaas does not support changes on subscription start date.', 'woo-asaas' ) );
			return;
		}
		if ( $this->date_changed( $subscription->get_time( 'trial_end' ), $trial_end_timestamp ) ) {
			$this->send_validation_error( __( 'Asaas does not support changes on trial date.', 'woo-asaas' ) );
			return;
		}

		$now = date( 'Y-m-d' );
		if ( date( 'Y-m-d', $next_payment_timestamp ) <= $now ) {
			$this->send_validation_error( __( 'The next payment date must be greater than the current one.', 'woo-asaas' ) );
			return;
		}
		if ( '' !== $end_date_timestamp && '0' !== $end_date_timestamp && date( 'Y-m-d', $end_date_timestamp ) <= $now ) {
			$this->send_validation_error( __( 'The end subscription date must be greater than the current one.', 'woo-asaas' ) );
			return;
		}

		$subscriptions_helper   = new Subscriptions_Helper();
		$new_billing_cycle      = $subscriptions_helper->convert_period( $billing_interval, $billing_period );
		$previous_billing_cycle = $subscriptions_helper->convert_period( $subscription->get_billing_interval(), $subscription->get_billing_period() );
		if ( false === $new_billing_cycle ) {
			$this->send_validation_error( 
				/* translators: %s: supported billing cicles  */
				sprintf( __( 'Asaas does not support the chosen billing period. Please, choose a valid period: %s.', 'woo-asaas' ), $subscriptions_helper->get_supported_billing_periods_string() ) 
			);
			return;
		}

		// Needs tokenization and is it unavailable?
		if ( 'asaas-credit-card' === $payment_gateway->id ) {
			if ( $new_billing_cycle !== $previous_billing_cycle ||
				$this->date_changed( $subscription->get_time( 'next_payment' ), $next_payment_timestamp ) || 
				$this->date_changed( $subscription->get_time( 'end' ), $end_date_timestamp ) ) {
				
				$tokenization_available = $payment_gateway->get_admin_settings()->is_tokenization_available();
				if ( false === $tokenization_available ) {
					$this->send_validation_error( 
						__( 'To update the billing cycle, next payment date or subscription end date you need to have tokenization enabled in your Asaas account.', 'woo-asaas' ) .
						__( 'Please, contact your Asaas manager.' )
					);
					return;
				}
			}
		}

		// Updates the Asaas subscription
		$api               = new Api( $payment_gateway );
		$subscription_meta = new Subscription_Meta( $subscription->get_id() );
		$subscription_id   = $subscription_meta->get_subscription_id();
		if ( $subscription_id ) {
			$updated_subscription_data = array(
				'cycle'       => $new_billing_cycle,
				'nextDueDate' => date( 'Y-m-d', $next_payment_timestamp ),
			);
			if ( '' !== $end_date_timestamp && '0' !== $end_date_timestamp ) {
				$updated_subscription_data['endDate'] = date( 'Y-m-d', $end_date_timestamp );
			}
			$response = $api->subscriptions()->update( $subscription_id, $updated_subscription_data );
			if ( is_a( $response, Error_Response::class ) ) {
				foreach ( $response->get_errors()->get_error_messages() as $message ) {
					$this->send_validation_error( $message );
					return;
				}
			}
		}

	}

}
