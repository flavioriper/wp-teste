<?php
/**
 * Ticket class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Gateway;

use WC_Asaas\Meta_Data\Order;
use WC_Asaas\Meta_Data\Subscription_Meta;
use WC_Asaas\Admin\Settings\Ticket as Ticket_Settings;
use WC_Asaas\Billing_Type\Ticket as Ticket_Type;
use WC_Asaas\Api\Response\Error_Response;
use WC_Asaas\WC_Asaas;
use WC_Asaas\Helper\Validation_Helper;
use WC_Asaas\Installments\Admin\Settings\Installments_Settings;
use WC_Order;
use wpdb;

/**
 * Asaas ticket gateway
 */
class Ticket extends Gateway {
	/**
	 * Init the gateway
	 */
	public function __construct() {
		$this->id           = 'asaas-ticket';
		$this->has_fields   = true;
		$this->method_title = __( 'Asaas Ticket', 'woo-asaas' );
		/* translators: %s: Asaas website URL  */
		$this->method_description = sprintf( __( 'Use <a href="%s">Asaas</a> to allow your customer buy your products by ticket.', 'woo-asaas' ), 'https://www.asaas.com/' );

		$this->type                  = new Ticket_Type();
		$this->init_logger();
		$this->init_settings();
		$this->admin_settings        = new Ticket_Settings( $this );
		$this->validation_errors     = new \WP_Error();

		parent::__construct();

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

		if ( $this->id === $order->get_wc()->get_payment_method() ) {
			WC_Asaas::get_instance()->get_template_file( 'order/ticket-thankyou.php', array( 'order' => $order ) );
		}
	}

	/**
	 * Create due date for ticket
	 *
	 * Check in Asaas the minimum due date for the ticket. If the validity date is less than the minimum, use the
	 * minimum as due date.
	 *
	 * @param string $reference_date (Optional) The reference date to calculate the due date.
	 * @param bool   $ignore_validity_days (Optional) Flag to ignore the sum of validity days.
	 * @return \DateTime The due date.
	 */
	public function create_due_date( $reference_date = 'now', $ignore_validity_days = false ) {
		$validity_days = 0;
		if ( false === $ignore_validity_days ) {
			$validity_days = apply_filters( 'woocommerce_asaas_default_ticket_validity_days', 3 );
			if ( isset( $this->settings['validity_days'] ) ) {
				$validity_days = intval( $this->settings['validity_days'] );
			}
		}

		$due_date = new \DateTime( $reference_date . sprintf( ' + %d days', $validity_days ) );

		$mininum_due_date = $this->api->payments()->minimum_bank_slip_due_date();
		$mininum_due_date = $mininum_due_date->get_json()->minimumDueDate;
		$mininum_due_date = new \DateTime( $mininum_due_date );

		return $mininum_due_date > $due_date ? $mininum_due_date : $due_date;
	}

	/**
	 * Process a ticket order to Asaas API
	 *
	 * If is a new payment processing of the an existing order and the ticket is the last try, is unnecessary generate
	 * a new payment. The same ticket is used.
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

		// Legacy code support.
		$id = version_compare( WC()->version, '3.0.0', '<' ) ? $wc_order->id : $wc_order->get_id();

		$total = $wc_order->get_total();

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

					$json = $response->get_json();
					if ( property_exists( $json, 'installment' ) ) {
						$installments       = $this->api->payments()->installment_list( $json->installment );
						$json->installments = $installments->get_json();
					}

					$order->set_meta_data( $json );
					$this->add_payment_id_to_order( $json->id, $wc_order );
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

							$json = $response->get_json();
							if ( property_exists( $payment_created, 'installment' ) ) {
								$installments                  = $this->api->payments()->installment_list( $payment_created->installment );
								$payment_created->installments = $installments->get_json();
							}

							$order->set_meta_data( $payment_created );
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

		return $this->success( $wc_order );
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
	 * Get payment fields
	 *
	 * @return array fields
	 */
	private function get_payment_fields() {
		$fields = array();

		return apply_filters(
			'woocommerce_asaas_ticket_payment_fields',
			$fields,
			$this
		);
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Payment_Gateway::payment_fields()
	 */
	public function payment_fields() {
		$description = $this->get_description();
		if ( ! empty( $description ) ) {
			echo wp_kses_post( wpautop( wptexturize( $description ) ) );
		}

		$fields = '';
		foreach ( $this->get_payment_fields() as $key => $args ) {
			$args['return'] = true;
			$fields        .= woocommerce_form_field( $key, $args );
		}

		// Transform paragraphs in divs to avoid HTML markup errors.
		// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
		echo preg_replace( array( '/<p/', '/<\/p>/' ), array( '<div', '</div>' ), $fields );
	}

	/**
	 * Get payment form posted data
	 *
	 * @return array The posted data sanitized.
	 */
	public function get_posted_data() {
		$payment_fields = $this->get_payment_fields();
		$data           = array();

		foreach ( $payment_fields as $key => $field ) {
			$field_type = WC_Asaas::get_instance()->get_form_field_object_from_type( $field['type'] );

			if ( is_null( $field_type ) ) {
				$data[ $key ] = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : ''; // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
				continue;
			}

			$field_type->process_data( $key, $field, $data );
		}

		return apply_filters( 'woocommerce_asaas_ticket_posted_data', $data );
	}

	/**
	 * Validate checkout fields
	 */
	public function validate_fields() {
		$validation_helper = new Validation_Helper();
		$data              = $this->get_posted_data();

		$validation_helper->validate_fields( $this, $this->get_payment_fields(), $data );

		// Legacy code support.
		if ( version_compare( WC()->version, '3.0.0', '<' ) ) {
			foreach ( $this->validation_errors->get_error_messages() as $message ) {
				wc_add_notice( $message, 'error' );
			}

			remove_action( 'woocommerce_after_checkout_validation', array( $this, 'add_checkout_validation_errors' ), 99 );
		}

		return parent::validate_fields();
	}

	/**
	 * Render custom type attribute.
	 *
	 * @param string $key The interest installment value key.
	 * @param array $data Field config data.
	 * @return string
	 */
	public function generate_interest_installment_html( $key, $data ) : string {
		$installments_settings = new Installments_Settings( $this );
		return $installments_settings->generate_interest_installment_html( $key, $data );
	}

	/**
	 * Removes the Asaas platform ticket after the ticket expires.
	 *
	 * @return string Response message with removal result.
	 */
	public function remove_expired_ticket() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$validity_period = apply_filters( 'woocommerce_asaas_default_ticket_validity_period', '' );
		if ( isset( $this->settings['validity_period'] ) && '' !== $this->settings['validity_period'] ) {
			$validity_period = intval( $this->settings['validity_period'] );
		}

		if( '' === $validity_period ) {
			return esc_html__( 'Ignore remove tickets by empty setting.', 'woo-asaas' );
		}

		$due_date_start  = ( new \DateTime( '-30 days' ) )->format('Y-m-d');
		$due_date_end    = ( new \DateTime( "-{$validity_period} days" ) )->format('Y-m-d');

		$payments = $this->api->payments()->all_between_dates( $due_date_start, $due_date_end );

		if ( 200 !== $payments->code || 0 === count( $payments->items ) ) {
			return esc_html__( 'No expired ticket to remove.', 'woo-asaas' );
		}

		$totals = 0;
		foreach ( $payments->items as $payment ) {
			$query = $wpdb->prepare(
				"SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = '_asaas_id' AND meta_value = %s",
				array(
					'_asaas_id' => $payment->id,
				)
			);
			$order_meta_count = intval( $wpdb->get_var( $query ) );
			$is_woocommerce_payment = 0 < $order_meta_count;

			if( ! $is_woocommerce_payment ) {
				continue;
			}

			$this->api->payments()->delete( $payment->id );
			$totals++;
		}

		return sprintf( esc_html__( 'Total tickets removed: %s', 'woo-asaas' ), $totals );
	}

	/**
	 * {@inheritDoc}
	 */
	public function prefix() : string {
		return 'ticket';
	}
}
