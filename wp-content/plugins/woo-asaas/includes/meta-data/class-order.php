<?php
/**
 * Abastract WC_Order for Asaas features.
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Meta_Data;

/**
 * Abastract WC_Order for Asaas features.
 */
class Order {
	/**
	 * Meta key name.
	 *
	 * @var string
	 */
	const META_KEY = '__ASAAS_ORDER';

	/**
	 * Woocommerce order.
	 *
	 * @var \WC_Order
	 */
	protected $wc;

	/**
	 * Woocommerce order meta data.
	 *
	 * @var \stdClass
	 */
	protected $meta_data;

	/**
	 * Constructor.
	 *
	 * @param  int $order_id WC_Order id.
	 * @return void
	 */
	public function __construct( $order_id ) {
		$this->wc = new \WC_Order( $order_id );
	}

	/**
	 * Check if user is guest.
	 *
	 * @return boolean
	 */
	public function is_guest() {
		return $this->get_user() === false;
	}

	/**
	 * Get order customer.
	 *
	 * @return array
	 */
	public function get_user() {
		return $this->wc->get_user();
	}

	/**
	 * Store order meta data.
	 *
	 * @param  \stdClass $data Store Asaas object.
	 * @return void
	 */
	public function set_meta_data( $data ) {
		$data = wp_json_encode( $data );

		// Legacy code support.
		if ( version_compare( WC()->version, '3.0.0', '<' ) ) {
			update_post_meta( $this->wc->id, self::META_KEY, $data );
			return;
		}

		$this->wc->update_meta_data( self::META_KEY, $data );
		$this->wc->save();
	}

	/**
	 * Get order meta data.
	 *
	 * @return \stdClass|boolean The Asaas payment meta if exists. False, otherwise.
	 */
	public function get_meta_data() {
		if ( $this->meta_data ) {
			return $this->meta_data;
		}

		// Legacy code support.
		if ( version_compare( WC()->version, '3.0.0', '<' ) ) {
			$meta = get_post_meta( $this->wc->id, self::META_KEY, true );
		} else {
			$meta = $this->wc->get_meta( self::META_KEY );
		}

		if ( ! $meta ) {
			return false;
		}

		$this->meta_data = json_decode( $meta );
		return $this->meta_data;
	}

	/**
	 * Get user meta data by key name.
	 *
	 * @param  string $name Propery name.
	 * @return string
	 */
	public function __get( $name ) {
		if ( is_object( $this->meta_data ) && property_exists( $this->meta_data, $name ) ) {
			return $this->meta_data->{$name};
		}
	}

	/**
	 * Checks if the order contains subscription.
	 *
	 * @param string $order_type Can include 'parent', 'renewal', 'resubscribe' or 'switch'. Defaults to 'any' orders.
	 * @return bool  True, if the order has subscription. Otherwise, false.
	 */
	public function has_subscription( $order_type = 'any' ) {
		if ( function_exists( '\wcs_order_contains_subscription' ) && \wcs_order_contains_subscription( $this->wc, array( $order_type ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Change order status to complete.
	 *
	 * @param string $transaction_id Optional transaction id to store in post meta.
	 * @return void
	 */
	public function complete( $transaction_id = '' ) {
		global $woocommerce;
		$woocommerce->cart->empty_cart();
		$this->get_wc()->payment_complete( $transaction_id );

		// Legacy code support.
		if ( version_compare( WC()->version, '3.0.0', '<' ) ) {
			$this->wc->reduce_order_stock();
			return;
		}

		wc_reduce_stock_levels( $this->wc->get_id() );
	}

	/**
	 * Get WC_Order instance.
	 *
	 * @return \WC_Order
	 */
	public function get_wc() {
		return $this->wc;
	}
}
