<?php
/**
 * Payment Installments class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Installments\Gateway;

use Exception;
use WC_Asaas\Gateway\Gateway;
use WC_Asaas\Installments\Checkout\Installments_Checkout;
use WC_Asaas\Installments\Checkout\Order_Interest_Handler;
use WC_Order;

/**
 * Handle init payment installments.
 */
class Payment_Installments {

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
	 * Handle with Gateway payment installments.
	 *
	 * @param int $installments Number of installments.
	 * @param WC_Order $wc_order WC Order object.
	 * @param Gateway $gateway Current payment gateway.
	 */
	public function installment_payment_data( array $payment_data, WC_Order $wc_order, Gateway $gateway ) : array {
		$gateway_prefix = $this->get_gateway_fields_prefix( $gateway->id );
		$posted_data    = $gateway->get_posted_data();
		$installments   = absint( $posted_data[ "asaas_{$gateway_prefix}_installments" ] );

		if ( 0 === $installments ) {
			return $payment_data;
		}

		$have_interest_on_installments = ( new Installments_Checkout( $gateway ) )->have_interest_on_installments( $installments );
		if ( true === $have_interest_on_installments ) {
			$this->add_interest_on_order( $installments, $wc_order, $gateway );
		}

		unset( $payment_data['value'] );

		$total = $wc_order->get_total();

		$payment_data['totalValue']       = $total;
		$payment_data['installmentCount'] = $installments;

		return $payment_data;
	}

	/**
	 * Get the gateway fields prefix.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @return string
	 */
	private function get_gateway_fields_prefix( string $gateway_id ) : string {
		if ( 'asaas-ticket' === $gateway_id ) {
			return 'ticket';
		}

		return 'cc';
	}

	/**
	 * Add interest on order.
	 *
	 * @param int $installments Number of installments.
	 * @param WC_Order $wc_order WC Order object.
	 * @param Gateway $gateway Current payment gateway.
	 */
	private function add_interest_on_order( int $installments, WC_Order $wc_order, Gateway $gateway ) {
		$interest_installment   = $gateway->settings['interest_installment'];
		$order_interest_handler = new Order_Interest_Handler( $wc_order );
		$order_interest_handler->add_interest_on_order( $installments, $interest_installment );
	}
}
