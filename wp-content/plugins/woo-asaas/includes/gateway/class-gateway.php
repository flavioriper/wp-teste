<?php
/**
 * Gateway base class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Gateway;

use WC_Asaas\Api\Api;
use WC_Asaas\WC_Asaas;
use WC_Asaas\Billing_Type\Billing_Type;
use WC_Asaas\Admin\Settings\Settings;
use WC_Asaas\Log\Logger;
use WC_Asaas\Helper\WP_Error_Helper;
use WC_Asaas\Meta_Data\Customer;
use WC_Asaas\Api\Response;
use WC_Asaas\Api\Response\Error_Response;
use WC_Asaas\Log\Logger_Legacy;
use WC_Asaas\Meta_Data\Order;
use WC_Asaas\Helper\Subscriptions_Helper;
use WC_Asaas\Subscription\Subscription;
use WC_Order;

/**
 * Gateway base class
 */
abstract class Gateway extends \WC_Payment_Gateway {

	/**
	 * Prefix log.
	 *
	 * @var string
	 */
	const PREFIX_LOG = 'Asaas: ';

	/**
	 * Asaas API wrapper.
	 *
	 * @var Api
	 */
	protected $api;

	/**
	 * The API key
	 *
	 * @var Api
	 */
	protected $api_key;

	/**
	 * Gateway billing type
	 *
	 * @var Billing_Type
	 */
	protected $type;

	/**
	 * Gateway admin settings
	 *
	 * @var Settings
	 */
	protected $admin_settings;

	/**
	 * Gateway logger
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 * Payment form validation errors
	 *
	 * @var \WP_Error
	 */
	protected $validation_errors;

	/**
	 * The Asaas customer id used to process the payment
	 *
	 * @var string
	 */
	protected $asaas_customer_id;

	/**
	 * Request customer cache
	 *
	 * @var Customer
	 */
	protected $customer;

	/**
	 * Initialize the form fields, settings and add hook to process the settings form
	 */
	public function __construct() {
		$this->init_form_fields();
		$this->init_settings();
		$this->setup_api();

		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		$this->validation_errors = new \WP_Error();

		$this->supports          = array(
			'subscriptions',
			'products',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'multiple_subscriptions',
			'gateway_scheduled_payments',
			'refunds',
		);

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_settings_checkout', array( $this, 'replace_config_url' ) );

		$setting_option = "woocommerce_{$this->id}_settings";
		add_action( "update_option_{$setting_option}", array( $this, 'enable_disable_customer_notifications' ), 10, 3 );

		add_action( 'woocommerce_checkout_update_user_meta', array( $this, 'set_customer' ), 10, 2 );
		add_action( 'woocommerce_after_checkout_validation', array( $this, 'add_checkout_validation_errors' ), 99, 2 );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Settings_API::init_form_fields()
	 */
	public function init_form_fields() {
		$this->form_fields = $this->admin_settings->fields();
	}

	/**
	 * Setup API wrapper.
	 *
	 * @return void
	 */
	private function setup_api() {
		$this->api_key = $this->get_option( 'api_key' );
		$this->api     = new Api( $this );
	}

	/**
	 * Get API key setting
	 *
	 * @return string The API key.
	 */
	public function get_api_key() {
		return $this->api_key;
	}

	/**
	 * Get Asaas customer metadata object
	 *
	 * @param int $user_id (Optional) The user ID. Default: Current logged user.
	 * @return Customer|null The customer object. Null, if the $user_id doesn't exist or user not logged in.
	 */
	public function get_customer( $user_id = null ) {
		if ( ! is_null( $this->customer ) ) {
			return $this->customer;
		}

		$user_id = is_null( $user_id ) ? get_current_user_id() : $user_id;

		if ( 0 === $user_id ) {
			return null;
		}

		$this->customer = new Customer( $user_id );
		return $this->customer;
	}

	/**
	 * Get the gateway billing type
	 *
	 * @return Billing_Type The gateway billing type.
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Get gateway admin settings
	 *
	 * @return Settings The gateway admin settings.
	 */
	public function get_admin_settings() {
		return $this->admin_settings;
	}

	/**
	 * Get gateway logger
	 *
	 * @return Logger The gateway logger.
	 */
	public function get_logger() {
		return $this->logger;
	}

	/**
	 * Initialize the logger
	 */
	public function init_logger() {
		// Legacy code support.
		if ( version_compare( WC()->version, '3.0.0', '<' ) ) {
			$this->logger = new Logger_Legacy( $this );
			return;
		}

		$this->logger = new Logger( $this );
	}

	/**
	 * The error code for checkout validation errors
	 *
	 * @return string
	 */
	public function get_error_code() {
		return 'asaas-invalid';
	}

	/**
	 * Process shared options between the plugin gateways
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Settings_API::process_admin_options()
	 */
	public function process_admin_options() {
		parent::process_admin_options();

		$shared_fields = $this->get_shared_fields();
		if ( 0 === count( $shared_fields ) ) {
			return;
		}

		$gateways = WC_Asaas::get_instance()->get_gateways();
		foreach ( $gateways as $id => $gateway ) {
			if ( $this->id === $gateway->id ) {
				continue;
			}

			$this->process_admin_shared_options( $gateway, $shared_fields );
		}
	}

	/**
	 * Get fields that are shared between the plugin gateways
	 *
	 * @return array The shared fields
	 */
	public function get_shared_fields() {
		$shared_fields = array();

		foreach ( $this->get_form_fields() as $field ) {
			if ( isset( $field['shared'] ) && true === $field['shared'] ) {
				$shared_fields[ $field['id'] ] = $field;
			}
		}

		return $shared_fields;
	}

	/**
	 * Process shared options in another gateway
	 *
	 * This function is very similar WC_Settings_API::process_admin_options function. The difference that is processed just the shared fields.
	 *
	 * @param Gateway $other_gateway The another gateway.
	 * @param array   $shared_fields The current gateway shared fields list.
	 * @return bool True if settings was updated. False, otherwise.
	 */
	public function process_admin_shared_options( $other_gateway, $shared_fields ) {
		$post_data = $this->get_post_data();

		foreach ( $shared_fields as $key => $field ) {
			if ( 'title' !== $this->get_field_type( $field ) ) {
				try {
					$other_gateway->settings[ $key ] = $this->get_field_value( $key, $field, $post_data );
				} catch ( \Exception $e ) {
					$this->add_error( $e->getMessage() );
				}
			}
		}

		return update_option( $other_gateway->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $other_gateway->id, $other_gateway->settings ) );
	}

	/**
	 * Replace config URL after update the settings
	 */
	public function replace_config_url() {
		$this->form_fields = $this->admin_settings->replace_config_url();
	}

	/**
	 * Integrate the order with Asaas
	 *
	 * Create or update an Asaas customer to process the order. If the API returns some error, the process will be
	 * stopped and the erros will be returned to show in the checkout page.
	 *
	 * @param int   $customer_id The WooCommerce customer id.
	 * @param array $data The checkout data.
	 */
	public function set_customer( $customer_id, $data ) {
		if ( $this->id !== $data['payment_method'] ) {
			return;
		}

		$customer                              = null;
		$action                                = 'create';
		$params                                = array();
		$customer_data                         = Customer::extract_data_from_checkout( $data );
		$customer_data['notificationDisabled'] = 'yes' !== $this->settings['notification'];

		if ( 0 < $customer_id ) {
			$customer                           = $this->get_customer( $customer_id );
			$customer_data['externalReference'] = $customer_id;

			if ( $customer->has_meta() ) {
				$action   = 'update';
				$meta     = $customer->get_meta();
				$params[] = $meta['id'];
			}
		}

		$params[] = $customer_data;

		$params = apply_filters( 'woocommerce_asaas_set_customer_params', $params, $customer, $action );
		$action = apply_filters( 'woocommerce_asaas_set_customer_action', $action, $customer, $params );

		/* @var Response $response The API response. */
		$response = apply_filters( 'woocommerce_asaas_set_customer_api_response', call_user_func_array( array( $this->api->customers(), $action ), $params ) );

		if ( is_a( $response, Error_Response::class ) ) {
			// print the messages because the customer handle is after the checkout validation.
			$this->send_checkout_failure_response( $response );
		}

		$this->asaas_customer_id = $response->id;

		if ( ! is_null( $customer ) ) {
			$customer->set_meta( 'id', $response->id );
		}
	}

	/**
	 * Add error to be show together another checkout errors
	 *
	 * @param \WP_Error $error The checkout error.
	 */
	public function add_validation_errors( $error ) {
		$wp_error_helper = new WP_Error_Helper();
		$wp_error_helper->merge( $this->validation_errors, $error );
	}

	/**
	 * Add the gateway checkout errors to WooCommerce error area.
	 *
	 * This function is called by `woocommerce_after_checkout_validation` hook.
	 *
	 * Create the error object if the $erros parameter is false.
	 *
	 * @param array             $data The submitted data.
	 * @param \WP_Error|boolean $errors The checkout errors. Default: false.
	 */
	public function add_checkout_validation_errors( $data, $errors = false ) {
		if ( ! $errors ) {
			$errors = new \WP_Error();
		}

		$wp_error_helper = new WP_Error_Helper();
		$wp_error_helper->merge( $errors, $this->validation_errors );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Payment_Gateway::validate_fields()
	 */
	public function validate_fields() {
		return empty( $this->validation_errors->get_error_codes() );
	}

	/**
	 * Send API warnings to the WooCommerce checkout notice area
	 *
	 * @param Response $response The API response.
	 */
	public function send_checkout_failure_response( $response ) {
		$messages = array();
		foreach ( $response->get_errors()->get_error_messages() as $message ) {
			$messages[] = $message;
		}

		$this->send_checkout_failure( $messages );
	}

	/**
	 * Stop the checkout processing and send errors to checkout page
	 *
	 * Based on WC_Checkout::send_ajax_failure_response(). Just work with AJAX requests.
	 *
	 * @see \WC_Checkout::send_ajax_failure_response()
	 *
	 * @param array $messages The messages to be showed in the checkout.
	 * @return void|null
	 */
	public function send_checkout_failure( $messages ) {
		foreach ( $messages as $message ) {
			wc_add_notice( $message, 'error' );
		}

		if ( ! is_ajax() ) {
			return;
		}

		ob_start();
		wc_print_notices();
		$output = ob_get_clean();

		$response = array(
			'result'   => 'failure',
			'messages' => $output,
		);

		wp_send_json( $response );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Payment_Gateway::is_available()
	 */
	public function is_available() {
		if ( ! parent::is_available() ) {
			return false;
		}

		if ( null === WC()->cart ) {
			return true;
		}

		if ( empty( $this->settings['min_total'] ) ) {
			return true;
		}

		$total     = wc()->cart->get_total( false );
		$min_total = floatval( str_replace( ',', '.', $this->settings['min_total'] ) );

		return $total > $min_total;
	}

	/**
	 * Update all customers notificationDisabled, if the value is changed in the settings form
	 *
	 * Not process if it isn`t the gateway that the options is updated to avoid process the same request many times.
	 * These would happen because notification is a shared option.
	 *
	 * @param string $old_value The value before the change.
	 * @param string $value The new value.
	 * @param string $option The option name that was updated.
	 */
	public function enable_disable_customer_notifications( $old_value, $value, $option ) {
		$post_data = $this->get_post_data();
		if ( ! isset( $post_data[ "woocommerce_{$this->id}_enabled" ] ) ) {
			return;
		}

		if ( "woocommerce_{$this->id}_settings" === $option && $old_value['notification'] !== $value['notification'] ) {
			$notification_disabled = 'yes' !== $value['notification'];
			$customers             = Customer::get_customers();

			foreach ( $customers as $customer ) {
				$this->api->customers()->update(
					$customer->id, array(
						'notificationDisabled' => $notification_disabled,
					)
				);
			}
		}
	}

	/**
	 * Validate the minimum total accepted
	 *
	 * The total must be positive.
	 *
	 * @param string $key The min total key.
	 * @param string $value The input value.
	 * @return string The value sanitized.
	 */
	public function validate_min_total_field( $key, $value ) {
		$value = floatval( str_replace( ',', '.', $value ) );

		if ( 0 > $value ) {
			return 0;
		}

		return str_replace( '.', ',', $value );
	}

	/**
	 * Returns the total order amount based on payment via the "Checkout" page or "Order Pay" page.
	 *
	 * @return float Order total amount.
	 */
	public function get_order_total() {
		if ( ! array_key_exists( 'key', $_GET ) ) {
			// Legacy code support.
			$cart = wc()->cart;
			return version_compare( WC()->version, '3.2.0', '<' ) ? $cart->total : $cart->get_total( false );
		}

		// Payment from "My Account" page.
		$key      = sanitize_text_field( $_GET['key'] );
		$order_id = wc_get_order_id_by_order_key( $key );
		$order    = wc_get_order( $order_id );
		return $order->get_total();
	}

	/**
	 * Returns a bool indicating whether the order has a signature.
	 *
	 * @return bool True if order has subscription. Otherwise, false.
	 */
	public function order_has_subscription() {
		if ( ! array_key_exists( 'key', $_GET ) ) {
			$subscriptions_helper = new Subscriptions_Helper();
			if ( WC()->cart && WC()->cart->get_cart_contents_count() > 0 ) {
				foreach ( WC()->cart->get_cart() as $key => $value ) {
					$product = $value['data'];
					if ( in_array( $product->get_type(), $subscriptions_helper->subscription_product_types ) ) {
						return true;
					}
				}
			}
			return false;
		}

		// Payment from "My Account" page.
		$key      = sanitize_text_field( $_GET['key'] );
		$order_id = wc_get_order_id_by_order_key( $key );
		if ( 0 !== $order_id ) {
			$order = new Order( $order_id );
			if ( $order->has_subscription() ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Process a refund.
	 *
	 * @param  int    $order_id Order ID.
	 * @param  float  $amount Refund amount.
	 * @param  string $reason Refund reason.
	 * @return bool|WP_Error
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order  = wc_get_order( $order_id );

		if ( false === $order ) {
			return new \WP_Error( 'error', __( 'Order not found.', 'woo-asaas' ) );
		}

		if ( ! $this->can_refund_order( $order ) ) {
			return new \WP_Error( 'error', __( 'This order cannot be refunded via this flow. Please, refund through your payment gateway or externally.', 'woo-asaas' ) );
		}

		if ( $amount < $order->get_total() ) {
			return new \WP_Error( 'error', __( 'Partial refunds are not possible.', 'woo-asaas' ) );
		}

		if ( $this->id === 'asaas-ticket' ) {
			return new \WP_Error( 'error', __( 'Due to payment method, to return funds to the customer you will need to issue a refund through your payment gateway or externally.', 'woo-asaas' ) );
		}

		$payment_id = $this->get_payment_id_from_order( $order );
		if ( false !== $payment_id ) {
			/* @var Response $response The API response. */
			$response = apply_filters( 'woocommerce_asaas_process_payment_refund_api_response', $this->api->payments()->refund( $payment_id ), $this );
			if ( is_a( $response, Error_Response::class ) ) {
				return $response->get_errors();
			}

			/* translators: %s: Asaas payment id  */
			$note = sprintf( __( '%s payment refund request has been processed successfully.', 'woo-asaas' ), $payment_id );
			$order->add_order_note( self::PREFIX_LOG . ' ' . $note );
		} else {
			return new \WP_Error( 'error', __( 'Payment not found. The order cannot be refunded via this flow.', 'woo-asaas' ) );
		}

		return true;
	}

	/**
	 * Process the transactions rollback in fails case.
	 *
	 * @param  int  $order_id WC Order id.
	 * @return bool True if it did a payment refund. Otherwise, false.
	 */
	public function process_transactions_rollback( $order_id ) {
		$order    = new Order( $order_id );
		$wc_order = $order->get_wc();

		$did_payment_rollback = false;

		// Is the payment confirmed or received? Do the refund.
		$payment_id = $this->get_payment_id_from_order( $wc_order );
		if ( false !== $payment_id ) {
			$response = $this->api->payments()->find( $payment_id );

			if ( 200 === $response->code ) {
				$refundable_statuses = array( 'RECEIVED', 'CONFIRMED' );
				if ( in_array( $response->get_json()->status, $refundable_statuses, true ) ) {
					$refund = wc_create_refund( 
						array( 
							'order_id'       => $order_id,
							'amount'         => $wc_order->get_total(),
							'reason'         => __( 'Order automatically refunded due to total or partial processing failure.', 'woo-asaas' ),
							'refund_payment' => true,
						)
					);
					if ( ! is_wp_error( $refund ) ) {
						$did_payment_rollback = true;
					}
				}
			}
		}

		// Are there subscriptions created? Expire all.
		if ( true === $order->has_subscription() ) {
			$subscriptions = wcs_get_subscriptions_for_order( $wc_order, array( 'order_type' => array( 'any' ) ) );
			foreach ( $subscriptions as $subscription ) {
				Subscription::get_instance()->update_status( $subscription, 'cancelled' );
			}
		}

		return $did_payment_rollback;
	}

	/**
	 * Create default due date
	 *
	 * @param string $reference_date (Optional) The reference date to set the due date.
	 * @param bool   $ignore_validity_days (Optional) Flag to ignore the sum of validity days;
	 * @return \DateTime The due date.
	 */
	public function create_due_date( $reference_date = 'now', $ignore_validity_days = false ) {
		$due_date = new \DateTime( $reference_date );

		return $due_date;
	}

	/**
	 * Checks if a subscription order needs an initial single payment transaction
	 *
	 * @param WC_Order $order The checkout WooCommerce Order.
	 * @param WC_Subscription[] $subscriptions The related order subscriptions (if exists).
	 * @return bool True if the order and needs an initial (or unique) single payment. Otherwise, false.
	 */
	protected function need_single_payment_transaction( $wc_order, $subscriptions ) {
		if ( count( $subscriptions ) > 1 ) {
			// The order has more than one signature.
			return true;
		}

		foreach ( $subscriptions as $subscription ) {
			if ( $subscription->get_sign_up_fee() > 0 ) {
				// One or more subscription has sign up fee.
				return true;
			}
		}

		$subscriptions_helper = new Subscriptions_Helper();
		foreach ( $wc_order->get_items() as $item_id => $item ) {
			$product = $item->get_product();
			if ( false !== $product && false === in_array( $product->get_type(), $subscriptions_helper->subscription_product_types ) && $item->get_total() > 0 ) {
				// The order has one or more non-free product.
				return true;
			}
		}

		return false;
	}

	/**
	 * Generates the transactions queue to support complex cart processing
	 *
	 * @param Gateway $gateway The payment gateway.
	 * @param int     $order_id The checkout order id.
	 * @param array   $payment_data The basic payment data structure (payload for /payments Api resource)
	 * @return array  The transactions queue.
	 */
	protected function generate_transactions_queue( $gateway, $order_id, $payment_data ) {
		$order    = new Order( $order_id );
		$wc_order = $order->get_wc();
		$total    = $wc_order->get_total();

		// Build the transactions queue.
		$transactions_queue = array();
		if ( true === $order->has_subscription() ) {
			// Order with one or more subscription items: parse WC_Subscription[] to populate transactions queue.
			$sign_up_fees                    = 0;
			$imediate_subscriptions_charges  = 0;
			$subscriptions_helper            = new Subscriptions_Helper();
			$subscriptions                   = wcs_get_subscriptions_for_order( $wc_order, array( 'order_type' => array( 'any' ) ) );
			$need_single_payment_transaction = $this->need_single_payment_transaction( $wc_order, $subscriptions );
			foreach ( $subscriptions as $subscription ) {
				$subscription_total = 0;
				$trial_end          = $subscription->get_date( 'trial_end', 'site' );
				$items_names        = array();
				$subscription_items = $subscription->get_items();
				foreach ( $subscription_items as $subscription_item ) {
					$line_item = wcs_find_matching_line_item( $wc_order, $subscription_item, $match_type = 'match_product_ids' );
					$product   = $line_item->get_product();

					// The subscription item total does not include the sign-up fee.
					$subscription_total += $subscription_item->get_total();
					$sign_up_fees       += ( $subscription->get_items_sign_up_fee( $subscription_item ) * $line_item->get_quantity() );

					$items_names[] = $subscription_item->get_name();
				}

				// Subscription payment data.
				$subscription_payment_data = $payment_data;
				unset( $subscription_payment_data['dueDate'] );

				$subscription_payment_data['value'] = $subscription_total;
				$first_payment_strategy = array(
					'processed_by_parent_order'      => 0, // parent order id (if the payment will be processed there)
					'included_in_single_transaction' => false,
				);
				if ( 0 === $trial_end ) {
					$first_payment_strategy['processed_by_parent_order'] = $order_id;
					if ( false === $need_single_payment_transaction ) {
						$subscription_payment_data['nextDueDate'] = $gateway->create_due_date( date_i18n( 'Y-m-d' ) )->format( 'Y-m-d' );
					} else {
						// The first charge is included in single transaction. So, here we inform the date of the second/next charge and includes the 1st charge in the single transaction.
						$first_payment_strategy['included_in_single_transaction'] = true;
						$imediate_subscriptions_charges                          += $subscription_total;
						$subscription_payment_data['nextDueDate']                 = $gateway->create_due_date( $subscription->get_date( 'next_payment', 'site' ) )->format( 'Y-m-d' );
					}
				} else {
					$subscription_payment_data['nextDueDate'] = $gateway->create_due_date( $subscription->get_date( 'next_payment', 'site' ), true )->format( 'Y-m-d' );
				}
				$subscription_payment_data['cycle'] = $subscriptions_helper->convert_period( $subscription->get_billing_interval(), $subscription->get_billing_period() );

				$end_date = $subscription->get_date( 'end', 'site' );
				if ( 0 !== $end_date ) {
					$subscription_payment_data['endDate'] = date( 'Y-m-d', strtotime( $end_date ) );
				}

				/* translators: 1: the subscription order id, 2: The items names  */
				$subscription_payment_data['description'] = sprintf( __( 'Subscription #%1$d - %2$s', 'woo-asaas' ), $subscription->get_id(), implode( ', ', $items_names ) );

				$subscription_payment_data['externalReference'] = $subscription->get_id();
				$subscription_payment_data = apply_filters( 'woocommerce_asaas_subscription_payment_data', $subscription_payment_data, $wc_order, $subscription, $subscription_item, $gateway );
				$transactions_queue[]      = array(
					'type'                   => 'subscription',
					'payment_data'           => $subscription_payment_data,
					'first_payment_strategy' => $first_payment_strategy,
				);
			}

			// Single charge.
			if ( true === $need_single_payment_transaction && $total > 0 ) {
				$payment_data['value'] = $total;
				if ( 'asaas-pix' !== $this->id ) {
					$payment_data = apply_filters( 'woocommerce_asaas_payment_data', $payment_data, $wc_order, $gateway );
				}

				$single_charge_transaction = array(
					'type'         => 'single',
					'payment_data' => $payment_data,
				);

				// Single charge at first position.
				array_unshift( $transactions_queue, $single_charge_transaction );
			}
		} else {
			// Order without subscription items. Only single charge transaction.
			$need_single_payment_transaction = true;

			if ( 'asaas-pix' !== $this->id ) {
				$payment_data = apply_filters( 'woocommerce_asaas_payment_data', $payment_data, $wc_order, $gateway );
			}
			
			$transactions_queue[] = array(
				'type'         => 'single',
				'payment_data' => $payment_data,
			);
		}

		return apply_filters( 'woocommerce_asaas_transactions_queue', $transactions_queue, $wc_order, $gateway );
	}

	/**
	 * Adds Asaas payment id to order meta
	 *
	 * @param string $payment_id Asaas payment id.
	 * @param WC_Order $order The checkout order.
	 * @return void
	 */
	public function add_payment_id_to_order( $payment_id, $order ) {
		$order->add_meta_data( '_asaas_id', $payment_id );
		$order->save();
	}

	/**
	 * Gateway prefix for utilities
	 *
	 * @return string
	 */
	abstract public function prefix();
	
	/**
	 * Gets Asaas payment id from order meta
	 *
	 * @param WC_Order $order The checkout order.
	 * @return string|false
	 */
	public function get_payment_id_from_order( $order ) {
		$payment_id = $order->get_meta( '_asaas_id', true );
		return '' !== $payment_id ? $payment_id : false;
	}
}
