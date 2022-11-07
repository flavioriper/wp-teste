<?php
/**
 * Checkout class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Checkout;

use Exception;

/**
 * Interact with WooCommerce Checkout
 */
class Checkout {

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
	 * Handles the usage of WooCommerce Subscriptions checkout process.
	 *
	 * @param \WC_Order $order The WooCommerce order.
	 * @return void
	 */
	public function handle_woocommerce_subscriptions_checkout_usage( $order ) {
		$payment_gateway = wc_get_payment_gateway_by_order( $order );
		if ( $payment_gateway ) {
			if ( in_array( $payment_gateway->id, array( 'asaas-credit-card', 'asaas-ticket', 'asaas-pix'), true ) ) {
				$payment_id = $payment_gateway->get_payment_id_from_order( $order );
				if ( false !== $payment_id ) {
					// If we are only processing payment for an existing asaas order, 
					// we remove the WooCommerce Subscriptions checkout process because it handles the signature object (delete and recreate)
					// which causes the payment flow break
					remove_action( 'woocommerce_checkout_order_processed', 'WC_Subscriptions_Checkout::process_checkout', 100 );
				}
			}
		}
	}

	/**
	 * Hides the first payment date which is displayed at checkout page.
	 *
	 * @param string                    $first_payment_date The first payment string.
	 * @param \WC_Subscriptions_Product $product The related subscription product.
	 * @return string
	 */
	public function hide_first_payment_date_string( $first_payment_date, $product ) {
		return '';
	}

}
