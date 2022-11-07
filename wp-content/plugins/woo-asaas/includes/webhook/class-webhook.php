<?php
/**
 * File for class Webhook
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Webhook;

use WC_Asaas\Gateway\Gateway;
use WC_Asaas\Api\Api;
use WC_Asaas\Subscription\Subscription;
use WC_Asaas\Meta_Data\Subscription_Meta;
use WC_Asaas\Meta_Data\Order;

/**
 * Webhook
 */
class Webhook {

	/**
	 * Attributes of the webhook
	 *
	 * @var object $data
	 */
	private $data;

	/**
	 * Order related to webhook
	 *
	 * @var \WC_Order
	 */
	private $order;

	/**
	 * Subscription related to webhook
	 *
	 * @var \WC_Subscription|null
	 */
	private $subscription;

	/**
	 * Discounts values
	 *
	 * @var float
	 */
	private $discounts_values;

	const PREFIX_LOG = 'Asaas: ';

	const PAYMENT_CREATED = 'PAYMENT_CREATED';

	const PAYMENT_UPDATED = 'PAYMENT_UPDATED';

	const PAYMENT_CONFIRMED = 'PAYMENT_CONFIRMED';

	const PAYMENT_RECEIVED = 'PAYMENT_RECEIVED';

	const PAYMENT_OVERDUE = 'PAYMENT_OVERDUE';

	const PAYMENT_REFUNDED = 'PAYMENT_REFUNDED';

	const PAYMENT_DELETED = 'PAYMENT_DELETED';

	const PAYMENT_RESTORED = 'PAYMENT_RESTORED';

	const PERCENTAGE_CALCULUS_TYPE = 'PERCENTAGE';

	const FIXED_CALCULUS_TYPE = 'FIXED';

	const CREDIT_CARD_PAYMENT_TYPE = 'CREDIT_CARD';

	/**
	 * Initialize the object
	 *
	 * @param Gateway               $gateway The payment gateway.
	 * @param \WC_Order             $order The webhook that will be processed.
	 * @param \WC_Subscription|null $subscription The subscription related to the webhook.
	 * @param \stdClass             $data The webhook data.
	 */
	public function __construct( Gateway $gateway, \WC_Order $order, ?\WC_Subscription $subscription = null, \stdClass $data ) {
		$this->gateway      = $gateway;
		$this->order        = $order;
		$this->subscription = $subscription;
		$this->data         = $data;

		$this->order->set_date_modified( new \WC_DateTime( date( 'Y-m-d H:i:s' ) ) );

		add_action( 'woocommerce_order_after_calculate_totals', array( $this, 'set_order_discount' ) );
	}

	/**
	 * Magic function to access attributes
	 *
	 * @param  string $name name of the attribute.
	 * @return mixed The value of the attribute.
	 */
	public function __get( $name ) {
		return $this->data->{$name};
	}

	/**
	 * Process the event according to its type
	 *
	 * @throws \Exception Case event not found.
	 */
	public function process_event() {
		switch ( $this->event ) {
			case Webhook::PAYMENT_CONFIRMED:
				$this->on_payment_confirmed();
				break;

			case Webhook::PAYMENT_CREATED:
				$this->on_payment_created();
				break;

			case Webhook::PAYMENT_DELETED:
				$this->on_payment_deleted();
				break;

			case Webhook::PAYMENT_OVERDUE:
				$this->on_payment_overdue();
				break;

			case Webhook::PAYMENT_RECEIVED:
				$this->on_payment_confirmed();
				break;

			case Webhook::PAYMENT_REFUNDED:
				$this->on_payment_refunded();
				break;

			case Webhook::PAYMENT_RESTORED:
				$this->on_payment_restored();
				break;

			case Webhook::PAYMENT_UPDATED:
				$this->on_payment_updated();
				break;

			default:
				/* translators: %s: event name  */
				die( esc_html( sprintf( __( 'Untreated event: %s', 'woo-asaas' ), $this->event ) ) );
		}
	}

	/**
	 * Method used when payment is confirmed
	 */
	private function on_payment_confirmed() {
		$this->process_payment();
		$this->add_order_note( __( 'Payment confirmed.', 'woo-asaas' ) );
	}

	private function process_payment() {
		$order_data = $this->order->get_data();

		$paid_statuses = wc_get_is_paid_statuses();
		if ( $this->order->has_status( $paid_statuses ) ) {
			throw new Inconsistency_Data_Exception( esc_html__( 'This order was already paid.', 'woo-asaas' ) );
		}

		// Value for change total of order.
		if ( $order_data['total'] !== $this->payment->value && self::CREDIT_CARD_PAYMENT_TYPE === $this->payment->billingType ) {
			$this->update_order_values_on_credit_card();
		}

		$this->order->payment_complete( $this->payment->id );
	}

	/**
	 * Method used when payment is created.
	 */
	private function on_payment_created() {
		if ( isset( $this->data->payment->subscription ) ) {
			// Treats subscription payment created event.
			$api      = new Api( $this->gateway );
			$response = $api->subscriptions()->payments( $this->data->payment->subscription );

			if ( 200 !== $response->code ) {
				throw new \Exception( sprintf( 'Error getting payments for a subscription in Asaas. Response HTTP status: %d', $response->code ) );
			}

			// Subscription meta.
			$subscription_meta       = new Subscription_Meta( $this->subscription->get_id() );
			$first_payment_strategy  = $subscription_meta->get_first_payment_strategy();
			$create_renewal          = true;
			if ( 1 === $response->get_json()->totalCount ) {
				if ( 0 !== $first_payment_strategy->processed_by_parent_order && false === $first_payment_strategy->included_in_single_transaction ) {
					// The first payment was processed by parent order, but it wasn't included in single transaction. So, this is the first.
					$create_renewal = false;
				}
			}
			if ( false === $create_renewal ) {
				// Parent order.
				$transaction_order = $this->subscription->get_parent();
			} else {
				// Creates the renewal order (for the >=2nd payment or 1st payment when the subscription has trial period).
				$transaction_order = wcs_create_renewal_order( $this->subscription );
				$transaction_order->set_payment_method( wc_get_payment_gateway_by_order( $this->subscription ) );
				if ( is_callable( array( $transaction_order, 'save' ) ) ) { // WC 3.0+ We need to save the payment method.
					$transaction_order->save();
				}
			}

			// Saves order subscription payment meta data.
			$this->gateway->add_payment_id_to_order( $this->data->payment->id, $transaction_order );
			$order_meta = new Order( $transaction_order->get_id() );
			if ( false === $order_meta->get_meta_data() ) {
				if ( 'asaas-credit-card' === $this->gateway->id ) {
					$order_meta->set_meta_data( $this->data->payment );
				} elseif ( 'asaas-ticket' === $this->gateway->id ) {
					if ( property_exists( $this->data->payment, 'installment' ) ) {
						$installments                      = $api->payments()->installment_list( $this->data->payment->installment );
						$this->data->payment->installments = $installments->get_json();
					}
					$order_meta->set_meta_data( $this->data->payment );
				} elseif ( 'asaas-pix' === $this->gateway->id ) {
					$pix_info_response = $api->payments()->pix_info( $this->data->payment->id );
					if ( is_a( $pix_info_response, Error_Response::class ) ) {
						wp_delete_post( $transaction_order->get_id(), true );
						throw new \Exception( sprintf( 'Error getting PIX information for a payment subscription in Asaas. Response HTTP status: %d', $pix_info_response->code ) );
					}

					$pix_info = $pix_info_response->get_json();
					$json     = $this->gateway->join_responses( $this->data->payment, $pix_info );
					$order_meta->set_meta_data( $json );
				}
			}

			// Tries to set the new externalReference for payment in Asaas (this is only possible if the payment is still pending).
			$payment_data = array(
				'externalReference' => $transaction_order->get_id(),
			);
			$api->payments()->update( $this->data->payment->id, $payment_data );

			// Updates the next due date on WC_Subscriptions object.
			$response = $api->subscriptions()->find( $this->data->payment->subscription );
			if ( 200 === $response->code ) {
				$dates = array(
					'next_payment' => date( 'Y-m-d H:i:s', strtotime( $response->nextDueDate . ' 12:00:00' ) ),
				);
				try {
					$this->subscription->update_dates( $dates );
				} catch ( \Exception $error ) {
					$this->gateway->get_logger()->log( 'FAILED TO UPDATE SUBSCRIPTION NEXT PAYMENT DATE ' . $error->getMessage() );
				}
			}

			$due_date = date_i18n( get_option( 'date_format' ), strtotime( $this->data->payment->dueDate ) );
			/* translators: 1: The due date, 2: The subscription order id  */
			$note = sprintf( __( 'Payment created. Due date: %1$s / Subscription id: %2$s', 'woo-asaas' ), $due_date, $this->subscription->get_id() );
			$transaction_order->add_order_note( self::PREFIX_LOG . ' ' . $note );
		} else {
			$this->order->update_status( 'pending' );
			$this->add_order_note( __( 'Payment created.', 'woo-asaas' ) );
		}

		if ( ! isset( $this->data->payment->creditCard ) && isset( $this->data->payment->invoiceNumber ) ) {
			$payment_url    = sprintf( 'https://%1$s.asaas.com/payment/show/%2$s', ( false === strpos( $this->data->payment->invoiceUrl, 'sandbox' ) ? 'www' : 'sandbox' ), $this->data->payment->invoiceNumber );
			$payment_anchor = sprintf( '<a href="%1$s" target="_blank">%1$s</a>', $payment_url );

			/* translators: 1: The payment link  */
			$note = sprintf( __( 'Payment link: %1$s', 'woo-asaas' ), $payment_anchor );
			if ( ! isset( $transaction_order ) ) {
				$this->add_order_note( $note );
			} else {
				$transaction_order->add_order_note( self::PREFIX_LOG . ' ' . $note );
			}
		}
	}

	/**
	 * Method used when payment is deleted
	 */
	private function on_payment_deleted() {
		if ( isset( $this->data->payment->subscription ) ) {
			Subscription::get_instance()->update_status( $this->subscription, 'on-hold', $this->event );
		} else {
			Subscription::get_instance()->update_subscriptions_related_to_parent_order( $this->order, 'on-hold', $this->event );
		}
		$this->order->update_status( 'cancelled' );
		$this->add_order_note( __( 'Payment deleted.', 'woo-asaas' ) );
	}

	/**
	 * Method used when payment is overdue
	 */
	private function on_payment_overdue() {
		if ( isset( $this->data->payment->subscription ) ) {
			Subscription::get_instance()->update_status( $this->subscription, 'on-hold', $this->event );
		} else {
			Subscription::get_instance()->update_subscriptions_related_to_parent_order( $this->order, 'on-hold', $this->event );
		}
		$this->order->update_status( 'failed' );
		$this->add_order_note( __( 'Payment overdue.', 'woo-asaas' ) );
	}

	private function update_order_values_on_credit_card() {
		if( $this->payment->value > $this->payment->originalValue ) {
			// Add tax for fine.
			if( isset( $this->payment->fine->value ) ) {
				$item_total = $this->calculate_item_total( $this->payment->fine, $this->payment->originalValue );

				$item_name = __( 'Fine tax', 'woo-asaas' );
				$this->add_item_fee_to_order( $item_name, $item_total );
			}

			// Add tax for interest.
			if( isset( $this->payment->interest->value ) ) {
				$item_total = $this->calculate_item_total( $this->payment->interest, $this->payment->originalValue );

				$item_name = __( 'Interest', 'woo-asaas' );
				$this->add_item_fee_to_order( $item_name, $item_total );
			}
		}

		if ( $this->payment->value < $this->payment->originalValue ) {
			// Add order discount.
			if ( isset( $this->payment->discount->value ) ) {
				$item_total = $this->calculate_item_total( $this->payment->discount, $this->payment->originalValue );

				$this->discounts_values = $item_total;
			}
		}

		// Calculate totals order.
		$this->order->calculate_totals();
	}

	private function calculate_item_total( $item_object, $total ) {
		$fine_type = self::FIXED_CALCULUS_TYPE;
		if( isset( $item_object->type ) ) {
			$fine_type = $item_object->type;
		}

		$item_total = $this->calculate_item_fee_value( $total, $fine_type, $item_object->value );

		return $item_total;
	}

	private function calculate_item_fee_value( $current_value, $type, $amount ) {
		if( self::PERCENTAGE_CALCULUS_TYPE === $type ) {
			return ( $current_value * ( $amount / 100 ) );
		}

		return $amount;
	}

	private function add_item_fee_to_order( $name, $total ) {
		$item_fee = new \WC_Order_Item_Fee();
		$item_fee->set_name( $name );
		$item_fee->set_total( $total );
		$this->order->add_item( $item_fee );
	}

	/**
	 * Method used when payment is refunded
	 */
	private function on_payment_refunded() {
		if ( isset( $this->data->payment->subscription ) ) {
			// Treats subscription payment refunded event.
			$api      = new Api( $this->gateway );
			$response = $api->subscriptions()->payments( $this->data->payment->subscription );

			if ( 200 !== $response->code ) {
				throw new \Exception( sprintf( 'Error getting payments for a subscription in Asaas. Response HTTP status: %d', $response->code ) );
			}

			if ( $this->data->payment->id === $response->get_json()->data[0]->id ) {
				// Changes the subscription status if the payment is the most recent.
				Subscription::get_instance()->update_status( $this->subscription, 'on-hold', $this->event );
			}
		} else {
			// Single payment.
			Subscription::get_instance()->update_subscriptions_related_to_parent_order( $this->order, 'on-hold', $this->event );
		}
		$this->order->update_status( 'refunded' );
		$this->add_order_note( 'Payment refunded.', 'woo-asaas' );
	}

	/**
	 * Method used when payment is restored
	 */
	private function on_payment_restored() {
		if ( isset( $this->data->payment->subscription ) ) {
			Subscription::get_instance()->update_status( $this->subscription, 'on-hold', $this->event );
		} else {
			Subscription::get_instance()->update_subscriptions_related_to_parent_order( $this->order, 'on-hold', $this->event );
		}
		$this->order->update_status( 'pending' );
		$this->add_order_note( 'Payment restored.', 'woo-asaas' );
	}

	/**
	 * Method used when payment is updated
	 */
	private function on_payment_updated() {
		$this->order->set_total( $this->payment->value );
		if ( ! in_array( $this->payment->status, array( 'RECEIVED', 'CONFIRMED' ), true ) ) {
			$this->order->update_status( 'pending' );
		}
		$this->add_order_note( 'Payment updated', 'woo-asaas' );
	}

	/**
	 * Discount should be applied after calculate order totals
	 */
	public function set_order_discount() {
		if( null === $this->discounts_values ) {
			return;
		}

		$order_total = $this->order->get_total() - $this->discounts_values;

		$this->order->set_discount_total( $this->discounts_values );
		$this->order->set_total( $order_total );
	}

	/**
	 * Add note
	 *
	 * @param String $note note description.
	 */
	private function add_order_note( $note ) {
		$this->order->add_order_note( self::PREFIX_LOG . ' ' . $note );
	}
}
