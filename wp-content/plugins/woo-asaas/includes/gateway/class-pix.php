<?php
/**
 * Pix class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Gateway;

use WC_Asaas\Meta_Data\Order;
use WC_Asaas\Meta_Data\Subscription_Meta;
use WC_Asaas\Admin\Settings\Pix as Pix_Settings;
use WC_Asaas\Billing_Type\Pix as Pix_Type;
use WC_Asaas\Api\Response\Error_Response;
use WC_Asaas\WC_Asaas;

/**
 * Asaas pix gateway
 */
class Pix extends Gateway {

	/**
	 * Init the gateway
	 */
	public function __construct() {
		$this->id           = 'asaas-pix';
		$this->has_fields   = true;
		$this->method_title = __( 'Asaas Pix', 'woo-asaas' );
		/* translators: %s: Asaas website URL  */
		$this->method_description = sprintf( __( 'Use <a href="%s">Asaas</a> to allow your customer buy your products by Pix.', 'woo-asaas' ), 'https://www.asaas.com/' );

		$this->type = new Pix_Type();
		$this->init_logger();
		$this->admin_settings = new Pix_Settings( $this );
		$this->validation_errors = new \WP_Error();

		parent::__construct();

		$this->supports = array(
			'products',
			'refunds',
		);

		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'append_html_to_thankyou_page' ) );
		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'append_html_to_thankyou_page' ) );
		add_action( 'woocommerce_view_order', array( $this, 'append_html_to_thankyou_page' ) );
	}

	/**
	 * Add ticker URL to thankyou page.
	 *
	 * @param  int $order_id WC Order id.
	 * @return void
	 */
	public function append_html_to_thankyou_page( $order_id ) {
		$order = new Order( $order_id );

		if ( $this->id !== $order->get_wc()->get_payment_method() ) {
			return;
		}

		WC_Asaas::get_instance()->get_template_file( 'order/pix-thankyou.php', array( 'order' => $order, 'show_copy_and_paste' => $this->show_copy_and_paste(), 'validity_days' => $this->settings['validity_days'] ) );
		$expiration_settings = $this->expiration_settings();

		$data = array(
			'order'               => $order,
			'show_copy_and_paste' => $this->show_copy_and_paste(),
			'expiration_time'     => $this->expiration_time( $expiration_settings ),
			'expiration_period'   => $this->expiration_period( $expiration_settings )
		);

		WC_Asaas::get_instance()->get_template_file( 'order/pix-thankyou.php', $data );
	}

	/**
	 * Check if the copy and paste code should be displayed.
	 *
	 * @return bool
	 */
	private function show_copy_and_paste() : bool {
		if ( 'no' === $this->settings['copy_and_paste'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Create due date for pix
	 *
	 * Check in Asaas the minimum due date for the pix. If the validity date is less than the minimum, use the
	 * minimum as due date.
	 *
	 * @param string $reference_date (Optional) The reference date to calculate the due date.
	 * @param bool   $ignore_validity_days (Optional) Flag to ignore the sum of validity days.
	 * @return \DateTime The due date.
	 */
	public function create_due_date( $reference_date = 'now', $ignore_validity_days = false ) {
		$expiration_period = '0d';
		if ( false === $ignore_validity_days ) {
			$expiration_settings = $this->expiration_settings();
			$expiration_time     = $this->expiration_time( $expiration_settings );
			$expiration_period   = $this->expiration_period( $expiration_settings );
		}

		$due_date = new \DateTime( $reference_date . sprintf( '+ %d %s', $expiration_time, $expiration_period ) );

		$mininum_due_date = $this->api->payments()->minimum_bank_slip_due_date();
		$mininum_due_date = $mininum_due_date->get_json()->minimumDueDate;
		$mininum_due_date = new \DateTime( $mininum_due_date );

		return $mininum_due_date > $due_date ? $mininum_due_date : $due_date;
	}

	/**
	 * Get expiration setting
	 *
	 * @return string The expiration setting.
	 */
	public function expiration_settings() {
		$expiration = $this->admin_settings->get_default_pix_validity_days();
		if ( isset( $this->settings['validity_days'] ) ) {
			$expiration = $this->settings['validity_days'];
		}

		return $expiration;
	}

	/**
	 * Get expiration period
	 *
	 * @return string The experiation period.
	 */
	private function expiration_period( string $expiration_settings ) {
		$valid_period = array( 'm' => 'minute', 'h' => 'hour', 'd' => 'day' );
		$period = substr( $expiration_settings, -1 );

		if( false === array_key_exists( $period, $valid_period ) ) {
			$period = 'd';
		}

		$period = strtr( $period, $valid_period );

		return $period;
	}

	/**
	 * Get expiration time
	 *
	 * @return int The experiation time.
	 */
	private function expiration_time( string $expiration_settings ) {
		$valid_period = array( 'm' => 'minute', 'h' => 'hour', 'd' => 'day' );
		$period = substr( $expiration_settings, -1 );
		$expiration_value = intval( substr( $expiration_settings, 0, -1 ) );

		if( false === array_key_exists( $period, $valid_period ) ) {
			$expiration_value = intval( $expiration_settings );
		}

		return $expiration_value;
	}

	/**
	 * Process a pix order to Asaas API
	 *
	 * If is a new payment processing of the an existing order and the pix is the last try, is unnecessary generate
	 * a new payment. The same pix is used.
	 *
	 * @param  int $order_id WC Order id.
	 * @return array|null
	 */
	public function process_payment( $order_id ) {
		$order    = new Order( $order_id );
		$wc_order = $order->get_wc();
		$customer = $this->get_customer();
		if ( null !== $customer ) {
			$customer_meta = $customer->get_meta();
		}

		if ( is_wc_endpoint_url( 'order-pay' ) || false !== $this->get_payment_id_from_order( $wc_order ) ) {
			// The payment has already been generated previously. So, redirects to the received order page.
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $wc_order ),
			);
		}

		$meta = $order->get_meta_data();
		if ( $meta ) {
			if ( $this->type->get_id() === $meta->billingType ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
				return $this->success( $wc_order );
			}

			$this->asaas_customer_id = $meta->customer;
		}

		$total = $wc_order->get_total();

		// Legacy code support.
		$id = version_compare( WC()->version, '3.0.0', '<' ) ? $wc_order->id : $wc_order->get_id();

		$payment_data = array(
			'customer'          => false === is_null( $this->asaas_customer_id ) ? $this->asaas_customer_id : $customer_meta['id'],
			'billingType'       => $this->type->get_id(),
			'value'             => $total,
			'dueDate'           => $this->create_due_date()->format( 'Y-m-d' ),
			'externalReference' => $id,
			/* translators: %d: the order id  */
			'description'       => sprintf( __( 'Order #%d', 'woo-asaas' ), $id ),
		);

		// Process the transactions queue.
		$transactions_queue = $this->generate_transactions_queue( $this, $order_id, $payment_data );
		foreach ( $transactions_queue as $transaction ) {
			switch ( $transaction['type'] ) {
				// Product item / Subscription sign up fee / Subscription 1st payment (depending on the cart situation).
				case 'single':
					/* @var Response $response The API response. */
					$response = apply_filters( 'woocommerce_asaas_process_payment_api_response', $this->api->payments()->create( $transaction['payment_data'] ), $transaction['payment_data'] );

					if ( is_a( $response, Error_Response::class ) ) {
						// Order rollback when the process fails. Print the messages because the payment handle is after the checkout validation.
						$rollback = $this->process_transactions_rollback( $order_id );
						$this->send_checkout_failure_response( $response );
						return;
					}

					$payment_created = $response->get_json();

					$pix_info_response = $this->api->payments()->pix_info( $payment_created->id );
					if ( is_a( $pix_info_response, Error_Response::class ) ) {
						$this->send_checkout_failure_response( $pix_info_response );
						return;
					}

					$pix_info = $pix_info_response->get_json();
					$json     = $this->join_responses( $payment_created, $pix_info );
					$order->set_meta_data( $json );
					$this->add_payment_id_to_order( $payment_created->id, $wc_order );
					break;

				// Subscription item payment.
				case 'subscription':
					/* @var Response $response The API response. */
					$response = apply_filters( 'woocommerce_asaas_process_subscription_api_response', $this->api->subscriptions()->create( $transaction['payment_data'] ), $transaction['payment_data'] );

					if ( is_a( $response, Error_Response::class ) ) {
						// Order rollback when the process fails. Print the messages because the payment handle is after the checkout validation.
						$rollback = $this->process_transactions_rollback( $order_id );
						$this->send_checkout_failure_response( $response );
						return;
					}

					$json = $response->get_json();

					$subscription_order = new Subscription_Meta( (int) $transaction['payment_data']['externalReference'] );
					$subscription_order->set_meta_data( $json );
					$subscription_order->set_subscription_id( $json->id );
					$subscription_order->set_first_payment_strategy( $transaction['first_payment_strategy'] );

					// Saves immediately the order meta to show up in the thank you page.
					if ( 0 !== $transaction['first_payment_strategy']['processed_by_parent_order'] && false === $order->get_meta_data() ) {
						$response = $this->api->subscriptions()->payments( $json->id );
						if ( is_a( $response, Error_Response::class ) ) {
							$rollback = $this->process_transactions_rollback( $order_id );
							$this->send_checkout_failure_response( $response );
							return;
						}

						if ( 0 < $response->get_json()->totalCount ) {
							$index           = count( $response->get_json()->data ) - 1;
							$payment_created = $response->get_json()->data[ $index ];

							$pix_info_response = $this->api->payments()->pix_info( $payment_created->id );
							if ( is_a( $pix_info_response, Error_Response::class ) ) {
								$this->send_checkout_failure_response( $pix_info_response );
								return;
							}

							$pix_info = $pix_info_response->get_json();
							$json     = $this->join_responses( $payment_created, $pix_info );
							$order->set_meta_data( $json );
							$this->add_payment_id_to_order( $payment_created->id, $wc_order );
						} else {
							$rollback = $this->process_transactions_rollback( $order_id );
							$this->send_checkout_failure(
								array(
									__( 'There was a failure on subscription generation.', 'woo-asaas' ),
								)
							);
						}
					}
					break;
			}
		}

		$payment_created = $response->get_json();

		$pix_info_response = $this->api->payments()->pix_info( $payment_created->id );
		if ( is_a( $pix_info_response, Error_Response::class ) ) {
			$this->send_checkout_failure_response( $pix_info_response );
			return;
		}

		$pix_info = $pix_info_response->get_json();

		$json = $this->join_responses( $payment_created, $pix_info );

		$order->set_meta_data( $json );

		$this->add_payment_id_to_order( $payment_created->id, $wc_order );

		return $this->success( $wc_order );
	}

	/**
	 * Group the responses to the requests to save the payment data with the order.
	 *
	 * @param object $payment_created Asaas payment object.
	 * @param object $pix_info Store Pix info object.
	 * @return object
	 */
	public function join_responses( object $payment_created, object $pix_info ) : object  {
		$response = array_merge( (array) $payment_created, (array) $pix_info );
		return (object) $response;
	}

	/**
	 * Return a WooCommerce success response redirecting to order page
	 *
	 * @param \WC_Order $wc_order The WooCommerce order object.
	 * @return string[] The response data.
	 */
	private function success( $wc_order ) {
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $wc_order ),
		);
	}

	/**
	 * Removes the Asaas platform pix after the pix expires.
	 *
	 * @param \WC_Order $order The order.
	 * @return string Response message with removal result.
	 */
	public function remove_expired_pix( $order ) {
		$status = $order->get_status();
		if ( 'processing' === $status ) {
			return esc_html__( 'No expired Pix to remove.', 'woo-asaas' );
		}

		if ( 'completed' === $status ) {
			return esc_html__( 'No expired Pix to remove.', 'woo-asaas' );
		}
		$order_id = version_compare( WC()->version, '3.0.0', '<' ) ? $order->id : $order->get_id();

		$asaas_id = $order->get_meta('_asaas_id');
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
		$this->api->payments()->delete( $asaas_id );

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
		return sprintf( esc_html__( 'The PIX from Order %s was removed', 'woo-asaas' ), $order_id );
	}

	/**
	 * {@inheritDoc}
	 */
	public function prefix() : string {
		return 'pix';
	}
}
