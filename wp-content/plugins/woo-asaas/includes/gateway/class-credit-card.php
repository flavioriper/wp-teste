<?php
/**
 * Credit Card class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Gateway;

use WC_Asaas\Meta_Data\Order;
use WC_Asaas\Meta_Data\Customer;
use WC_Asaas\Meta_Data\Subscription_Meta;
use WC_Asaas\Admin\Settings\Credit_Card as Credit_Card_Settings;
use WC_Asaas\Billing_Type\Credit_Card as Credit_Card_Type;
use WC_Asaas\Api\Response\Error_Response;
use WC_Asaas\Checkout\Form_Field\Card;
use WC_Asaas\WC_Asaas;
use WC_Asaas\Helper\Validation_Helper;
use WC_Asaas\Checkout\Form_Field\One_Click_Options;
use WC_Asaas\Installments\Admin\Settings\Installments_Settings;

/**
 * Asaas credit card gateway
 */
class Credit_Card extends Gateway {

	/**
	 * Init the gateway
	 */
	public function __construct() {
		$this->id           = 'asaas-credit-card';
		$this->has_fields   = true;
		$this->method_title = __( 'Asaas Credit Card', 'woo-asaas' );
		/* translators: %s: Asaas website URL  */
		$this->method_description = sprintf( __( 'Use <a href="%s">Asaas</a> to allow your customer buy your products in installments using credit card.', 'woo-asaas' ), 'https://www.asaas.com/' );

		$this->type                  = new Credit_Card_Type();
		$this->init_logger();
		$this->init_settings();
		$this->admin_settings        = new Credit_Card_Settings( $this );
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
			WC_Asaas::get_instance()->get_template_file( 'order/credit-card-thankyou.php', array( 'order' => $order ) );
		}
	}

	/**
	 * Process a newly credit card order to Asaas API.
	 *
	 * @param  int $order_id WC Order id.
	 * @return array|null
	 */
	public function process_payment( $order_id ) {
		$order       = new Order( $order_id );
		$wc_order    = $order->get_wc();
		$token       = null;
		$posted_data = $this->get_posted_data();
		$customer    = $this->get_customer();

		if ( null !== $customer ) {
			$customer_meta = $customer->get_meta();
		}

		// Legacy code support.
		$id        = version_compare( WC()->version, '3.0.0', '<' ) ? $wc_order->id : $wc_order->get_id();
		$remote_ip = version_compare( WC()->version, '3.0.0', '<' ) ? $wc_order->customer_ip_address : $wc_order->get_customer_ip_address();

		// Verify if has selected credit card existent.
		if ( $this->is_one_click_buy() && 'credit-card-new' !== $posted_data['asaas_cc_options'] ) {
			$card_index = preg_replace( '/[^0-9]+/', '', $posted_data['asaas_cc_options'] );

			if ( empty( $customer_meta['credit_cards'][ $card_index ] ) ) {
				$this->send_checkout_failure(
					array(
						__( 'Are you sure that this card exists?', 'woo-asaas' ),
					)
				);
				return;
			}

			$token = $customer_meta['credit_cards'][ $card_index ]['creditCardToken'];
		}

		$total = $wc_order->get_total();

		$payment_data = array(
			'customer'          => false === is_null( $this->asaas_customer_id ) ? $this->asaas_customer_id : $customer_meta['id'],
			'billingType'       => 'CREDIT_CARD',
			'value'             => $total,
			'dueDate'           => date_i18n( 'Y-m-d' ),
			'externalReference' => $id,
			'remoteIp'          => $remote_ip,
			/* translators: %d: the order id  */
			'description'       => sprintf( __( 'Order #%d', 'woo-asaas' ), $id ),
		);

		// If has token, isn't necessary data credit card and holder info.
		if ( $token ) {
			$payment_data['creditCardToken'] = $token;
		} else {
			$payment_data['creditCard'] = array(
				'holderName'  => isset( $posted_data['asaas_cc_name'] ) ? $posted_data['asaas_cc_name'] : '',
				'number'      => isset( $posted_data['asaas_cc_number'] ) ? $posted_data['asaas_cc_number'] : '',
				'expiryMonth' => isset( $posted_data['asaas_cc_expiration_month'] ) ? $posted_data['asaas_cc_expiration_month'] : '',
				'expiryYear'  => isset( $posted_data['asaas_cc_expiration_year'] ) ? $posted_data['asaas_cc_expiration_year'] : '',
				'ccv'         => isset( $posted_data['asaas_cc_security_code'] ) ? $posted_data['asaas_cc_security_code'] : '',
			);

			$payment_data['creditCardHolderInfo'] = Customer::extract_data_from_order( $wc_order );
		}

		// Paying an existing order?		
		$payment_id = $this->get_payment_id_from_order( $wc_order );
		if ( false !== $payment_id ) {
			// The payment has already been generated previously. So, get payment details and try to pay it.
			$response = $this->api->payments()->find( $payment_id );
			if ( is_a( $response, Error_Response::class ) ) {
				$this->send_checkout_failure_response( $response );
				return;
			}

			$pending_statuses = array( 'PENDING', 'OVERDUE' );
			$payment          = $response->get_json();
			if ( ! in_array( $payment->status, $pending_statuses, true ) ) {
				$this->send_checkout_failure(
					array(
						__( 'The related payment isn\'t pending on the gateway.', 'woo-asaas' ),
					)
				);
				return;
			} elseif ( $total !== $payment->value ) {
				// Update the Asaas payment before try to pay it.
				$update_payment = array(
					'value' => $total,
				);
				$response = $this->api->payments()->update( $payment->id, $update_payment );
				if ( is_a( $response, Error_Response::class ) ) {
					$this->send_checkout_failure_response( $response );
					return;
				}
			}

			// Pay.
			$response = apply_filters( 'woocommerce_asaas_process_pay_with_credit_card_api_response', $this->api->payments()->pay_with_credit_card( $payment->id, $payment_data ), $payment_id, $payment_data );
			if ( is_a( $response, Error_Response::class ) ) {
				$this->send_checkout_failure_response( $response );
				return;
			}			

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $wc_order ),
			);
		}

		// Paying a new order. Process the transactions queue.
		$credit_card        = null;
		$transactions_queue = $this->generate_transactions_queue( $this, $order_id, $payment_data );
		$order_complete     = false;
		$transaction_id     = '';
		foreach ( $transactions_queue as $transaction ) {
			switch ( $transaction['type'] ) {
				// Product item / Subscription sign up fee / Subscription 1st payment (depending on the cart situation).
				case 'single':
					/* @var Response $response The API response. */
					$response = apply_filters( 'woocommerce_asaas_process_payment_api_response', $this->api->payments()->create( $transaction['payment_data'] ), $transaction['payment_data'] );

					if ( is_a( $response, Error_Response::class ) ) {
						// Order rollback when the process fails. Print the messages because the payment handle is after the checkout validation.
						$rollback = $this->process_transactions_rollback( $order_id );
						if ( true === $rollback ) {
							$this->send_checkout_failure(
								array(
									__( 'There was a payment failure and therefore the transaction was reversed.', 'woo-asaas' ),
								)
							);
						} else {
							$this->send_checkout_failure_response( $response );
						}
						return;
					}

					$json = $response->get_json();
					$order->set_meta_data( $json );
					$this->add_payment_id_to_order( $json->id, $wc_order );

					if ( in_array( $json->status, array( 'RECEIVED', 'CONFIRMED' ), true ) ) {
						$order_complete = true;
						$transaction_id = $json->id;
					}
					break;

				// Subscription item payment.
				case 'subscription':
					/* @var Response $response The API response. */
					$response = apply_filters( 'woocommerce_asaas_process_subscription_api_response', $this->api->subscriptions()->create( $transaction['payment_data'] ), $transaction['payment_data'] );

					if ( is_a( $response, Error_Response::class ) ) {
						// Order rollback when the process fails. Print the messages because the payment handle is after the checkout validation.
						$rollback = $this->process_transactions_rollback( $order_id );
						if ( true === $rollback ) {
							$this->send_checkout_failure(
								array(
									__( 'There was a payment failure and therefore the transaction was reversed.', 'woo-asaas' ),
								)
							);
						} else {
							$this->send_checkout_failure_response( $response );
						}
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

							$order->set_meta_data( $payment_created );
							$this->add_payment_id_to_order( $payment_created->id, $wc_order );

							if ( in_array( $payment_created->status, array( 'RECEIVED', 'CONFIRMED' ), true ) ) {
								$order_complete = true;
								$transaction_id = $payment_created->id;
							}
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

			$credit_card = ( null === $credit_card && ! empty( $json->creditCard ) ) ? $json->creditCard : $credit_card; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		}

		// Add credit card to customer meta data.
		if ( ! $order->is_guest() && null !== $credit_card ) {
			$customer->add_credit_card( $credit_card );
		}

		// Mark order as completed.
		if ( true === $order_complete ) {
			$order->complete( $transaction_id );
		}

		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order->get_wc() ),
		);
	}

	/**
	 * Get payment fields
	 *
	 * - Verify if customer has credit card registered to display the one click buy options.
	 *
	 * @return array fields
	 */
	private function get_payment_fields() {
		$customer = $this->get_customer();

		$fields             = array();
		$fields['asaas_cc'] = array(
			'type' => Card::get_instance()->get_type(),
		);

		if ( $this->is_one_click_buy() ) {
			$meta    = $customer->get_meta();
			$options = array();
			foreach ( $meta['credit_cards'] as $i => $card ) {
				$options[ 'credit-card-' . $i ] = WC_Asaas::get_instance()->get_template_file( 'one-click-buy-option.php', array( 'card' => $card ), true );
			}

			$fields['asaas_cc'] = array(
				'type'    => One_Click_Options::get_instance()->get_type(),
				'label'   => __( 'Select a credit card', 'woo-asaas' ),
				'options' => $options,
				'id'      => 'asaas_cc',
			);
		}

		return apply_filters(
			'woocommerce_asaas_cc_payment_fields',
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
			$fields        .= htmlspecialchars_decode( woocommerce_form_field( $key, $args ), ENT_QUOTES );
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

		return apply_filters( 'woocommerce_asaas_cc_posted_data', $data );
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
	 * Check if payment is with one click buy
	 *
	 * The setting in admin must be checked as yes and the customer must have at least one card registered.
	 *
	 * @param int $user_id The WP user id.
	 * @return boolean True, if the user is enabled to make one click buy. Otherwise, false.
	 */
	public function is_one_click_buy( $user_id = null ) {
		if ( 'yes' !== $this->settings['one_click_buy'] ) {
			return false;
		}

		$customer = $this->get_customer( $user_id );
		if ( is_null( $customer ) ) {
			return false;
		}

		$meta = $customer->get_meta();
		return ! empty( $meta['credit_cards'] );
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
	 * {@inheritDoc}
	 */
	public function prefix() : string {
		return 'cc';
	}
}
