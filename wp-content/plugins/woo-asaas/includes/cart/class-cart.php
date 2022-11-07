<?php
/**
 * Cart class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Cart;

use Exception;
use WC_Asaas\Helper\Subscriptions_Helper;

/**
 * Cart functions and validations
 */
class Cart {

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
	 * Disables Asaas gateways
	 * 
	 * @param array $available_gateways Available payment gateways.
	 * @return array List of payment gateways without Asaas
	 */
	public function disable_asaas_gateways($available_gateways) {
		unset( $available_gateways['asaas-credit-card'], $available_gateways['asaas-pix'], $available_gateways['asaas-ticket'] );
		return $available_gateways;
	}

	/**
	 * Shows the notice with the reason to disable Asaas gateways
	 * 
	 * @param string $notice_type The type/id of the notice.
	 * @param string $message The notice message
	 * @return void
	 */
	public function show_notice_disabled_gateway_reason($notice_type, $message) {
		$show_notice = true;

		$wc_notices = wc_get_notices( 'notice' );
		foreach( $wc_notices as $notice ) {
			if (isset($notice['data'][$notice_type])) {
				$show_notice = false;
				break;
			}
		}

		if ($show_notice === true && !wc_has_notice($message,'notice'))
			wc_add_notice( $message, 'notice', [ $notice_type => true ] );

	}

	/**
	 * Checks cart items and applied coupons to define available payment gateways
	 *
	 * @param array $available_gateways Available payment gateways.
	 * @return array Available payment gateways.
	 */
	public function check_available_payment_gateways( $available_gateways ) {

		if ( ! is_admin() ) {

			// Checks if cart is related to an order.
			$order_id = false;
			if ( is_wc_endpoint_url( 'order-pay' ) ) {
				global $wp;
				$order_id = absint( $wp->query_vars['order-pay'] );
			} else {
				if ( WC()->cart && WC()->cart->get_cart_contents_count() > 0 ) {
					foreach ( WC()->cart->get_cart() as $key => $value ) {
						if ( isset( $value['subscription_initial_payment'] ) ) {
							$order_id = absint( $value['subscription_initial_payment']['order_id'] );
							break;
						}
					}
				}
			}

			if ( false !== $order_id ) {
				// Handles order-pay or subscription renewal payment for existing order.
				$order = wc_get_order( $order_id );
				if ( $order ) {
					$order_payment_gateway = wc_get_payment_gateway_by_order( $order->get_id() );
					foreach ( $available_gateways as $gateway_id => $gateway ) {
						if ( $order_payment_gateway->id !== $gateway_id ) {
							unset( $available_gateways[ $gateway_id ] );
						}
					}
					return $available_gateways;
				}
			} else {
				// Handles cart and coupon situations.
				$subscriptions_helper = new Subscriptions_Helper();

				// Validates cart items.
				$cart_has_subscription_products = false;
				if ( WC()->cart && WC()->cart->get_cart_contents_count() > 0 ) {
					foreach ( WC()->cart->get_cart() as $key => $value ) {
						$product = $value['data'];
						if ( in_array( $product->get_type(), $subscriptions_helper->subscription_product_types ) ) {
							$cart_has_subscription_products = true;
							if ( false === $subscriptions_helper->convert_period( $product->get_meta( '_subscription_period_interval' ), $product->get_meta( '_subscription_period' )) ) {
								$available_gateways = $this->disable_asaas_gateways( $available_gateways );

								$message = __( 'The Asaas payment gateway was disabled because it does not support the billing cycle for one or more products in the cart.', 'woo-asaas' );
								$this->show_notice_disabled_gateway_reason( 'unsupported-asaas-billing-cycle', $message );

								break;
							}
						}
					}
				}

				// Validates applied coupons.
				if ( true === $cart_has_subscription_products && count( WC()->cart->get_applied_coupons() ) > 0 ) {
					foreach ( WC()->cart->get_applied_coupons() as $coupon_code ) {
						$coupon = new \WC_Coupon( $coupon_code );
						if ( false === $subscriptions_helper->discount_coupon_supported( $coupon ) ) {
							$available_gateways = $this->disable_asaas_gateways( $available_gateways );

							/* translators: %s: coupon code */
							$message = sprintf( __( 'The Asaas payment gateway was disabled because it does not support the following coupon code: %s.', 'woo-asaas' ), $coupon_code );
							$this->show_notice_disabled_gateway_reason( 'unsupported-asaas-discount-coupon', $message );

							break;
						}
					}
				}

				// Disables PIX.
				if ( true === $cart_has_subscription_products && array_key_exists( 'asaas-pix', $available_gateways ) ) {
					unset( $available_gateways['asaas-pix'] );
				}
			}
		}

		return $available_gateways;
	}
}
