<?php
/**
 * Abastract WC_Order for Asaas subscriptions features.
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Meta_Data;

/**
 * Abastract WC_Order for Asaas subscriptinos features.
 */
class Subscription_Meta {
	/**
	 * Meta key name.
	 *
	 * @var string
	 */
	const META_KEY = '__ASAAS_SUBSCRIPTION';

	/**
	 * Meta key name for Assas subscription id.
	 *
	 * @var string
	 */
	const META_KEY_SUBSCRIPTION_ID = '_asaas_subscription_id';

	/**
	 * Meta key name for the first payment strategy.
	 *
	 * @var string
	 */
	const META_KEY_FIRST_PAYMENT_STRATEGY = '_asaas_subscription_first_payment_strategy';

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
	 * The Asaas subscription id.
	 *
	 * @var string|false
	 */
	protected $subscription_id;

	/**
	 * The first payment strategy.
	 *
	 * @var array
	 */
	protected $first_payment_strategy;

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
	 * Get subscription meta data by key name.
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
	 * Sets the asaas subscription id
	 *
	 * @param string $subscription_id The Asaas subscription id.
	 * @return void
	 */
	public function set_subscription_id( $subscription_id ) {
		// Legacy code support.
		if ( version_compare( WC()->version, '3.0.0', '<' ) ) {
			update_post_meta( $this->wc->id, self::META_KEY_SUBSCRIPTION_ID, $subscription_id );
			return;
		}

		$this->wc->update_meta_data( self::META_KEY_SUBSCRIPTION_ID, $subscription_id );
		$this->wc->save();
	}

	/**
	 * Gets the Asaas subscription id
	 *
	 * @return string|false The Asaas subscription id if defined. False, otherwise.
	 */
	public function get_subscription_id() {
		if ( $this->subscription_id ) {
			return $this->subscription_id;
		}

		// Legacy code support.
		if ( version_compare( WC()->version, '3.0.0', '<' ) ) {
			$meta = get_post_meta( $this->wc->id, self::META_KEY_SUBSCRIPTION_ID, true );
		} else {
			$meta = $this->wc->get_meta( self::META_KEY_SUBSCRIPTION_ID );
		}

		if ( ! $meta ) {
			return false;
		}

		$this->subscription_id = $meta;
		return $this->subscription_id;
	}

	/**
	 * Sets the first payment strategy
	 *
	 * @param array $first_payment_strategy The first payment strategy containing processed_by_parent_order and included_in_single_transaction values.
	 * @return void
	 */
	public function set_first_payment_strategy( $first_payment_strategy ) {
		$data = wp_json_encode( $first_payment_strategy );

		// Legacy code support.
		if ( version_compare( WC()->version, '3.0.0', '<' ) ) {
			update_post_meta( $this->wc->id, self::META_KEY_FIRST_PAYMENT_STRATEGY, $data );
			return;
		}

		$this->wc->update_meta_data( self::META_KEY_FIRST_PAYMENT_STRATEGY, $data );
		$this->wc->save();
	}

	/**
	 * Gets the first payment strategy
	 *
	 * @return \stdClass|boolean The Asaas first payment strategy meta if exists. False, otherwise.
	 */
	public function get_first_payment_strategy() {
		if ( $this->first_payment_strategy ) {
			return $this->first_payment_strategy;
		}

		// Legacy code support.
		if ( version_compare( WC()->version, '3.0.0', '<' ) ) {
			$meta = get_post_meta( $this->wc->id, self::META_KEY_FIRST_PAYMENT_STRATEGY, true );
		} else {
			$meta = $this->wc->get_meta( self::META_KEY_FIRST_PAYMENT_STRATEGY );
		}

		if ( ! $meta ) {
			return false;
		}

		$this->first_payment_strategy = json_decode( $meta );
		return $this->first_payment_strategy;
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
