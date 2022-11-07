<?php
/**
 * WooCommerce My Account class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\My_Account;

use Exception;

/**
 * Interact with WooCommerce My Account settings
 */
class WooCommerce_My_Account {

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
	 * Filters my orders actions based on related payment gateways.
	 *
	 * @param array     $actions My orders actions.
	 * @param \WC_Order $order The WooCommerce order.
	 * @return array    $actions The filtered my orders actions.
	 */
	public function my_orders_actions( $actions, $order ) {
		foreach ( $actions as $key => $values ) {
			// Removes the pay action for Asaas Ticket and Asaas PIX orders.
			if ( 'pay' == $key && in_array( $order->get_payment_method(), array( 'asaas-ticket', 'asaas-pix' ), true ) ) {
				unset( $actions[ $key ] );
			}
		}
		return $actions;
	}

}
