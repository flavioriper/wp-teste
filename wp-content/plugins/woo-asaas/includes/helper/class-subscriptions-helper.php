<?php
/**
 * Subscriptions helper class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Helper;

/**
 * Subscriptions helper functions
 */
class Subscriptions_Helper {

	/**
	 * Allowed period combinations.
	 *
	 * @var array
	 */
	private $allowed_period_combinations = array();

	/**
	 * Allowed discount coupon types.
	 * The plugin doesn't supports the following types: recurring_fee, recurring_percent
	 *
	 * @var array
	 */
	private $allowed_discount_coupon_types = array();

	/**
	 * Subscription product types.
	 *
	 * @var array
	 */
	public $subscription_product_types = array( 'variable-subscription', 'subscription', 'subscription_variation' );

	/**
	 * Init the Subscription Helper class
	 */
	public function __construct() {
		$this->allowed_period_combinations = array(
			'1 week'  => array(
				'period'      => 'WEEKLY',
				'description' => __( 'WEEKLY', 'woo-asaas' ),
			),
			'2 week'  => array(
				'period'      => 'BIWEEKLY',
				'description' => __( 'BIWEEKLY', 'woo-asaas' ),
			),
			'1 month' => array(
				'period'      => 'MONTHLY',
				'description' => __( 'MONTHLY', 'woo-asaas' ),
			),
			'4 week'  => array(
				'period'      => 'MONTHLY',
				'description' => __( 'MONTHLY', 'woo-asaas' ),
			),
			'2 month' => array(
				'period'      => 'BIMONTHLY',
				'description' => __( 'BIMONTHLY', 'woo-asaas' ),
			),
			'3 month' => array(
				'period'      => 'QUARTERLY',
				'description' => __( 'QUARTERLY', 'woo-asaas' ),
			),
			'6 month' => array(
				'period'      => 'SEMIANNUALLY',
				'description' => __( 'SEMIANNUALLY', 'woo-asaas' ),
			),
			'1 year'  => array(
				'period'      => 'YEARLY',
				'description' => __( 'YEARLY', 'woo-asaas' ),
			),
		);

		$this->allowed_discount_coupon_types = array(
			'percent'             => __( 'Percentage discount', 'woo-asaas' ),
			'fixed_cart'          => __( 'Fixed cart discount', 'woo-asaas' ),
			'fixed_product'       => __( 'Fixed product discount', 'woo-asaas' ),
			'sign_up_fee'         => __( 'Sign Up Fee Discount', 'woo-asaas' ),
			'sign_up_fee_percent' => __( 'Sign Up Fee % Discount', 'woo-asaas' ),
		);
	}

	/**
	 * Return supported billing period string
	 *
	 * @return string.
	 */
	public function get_supported_billing_periods_string() {
		$periods = [];
		foreach ( $this->allowed_period_combinations as $key => $period ) {
			if ( ! in_array( $period['description'], $periods ) ) {
				$periods[] = $period['description'];
			}
		}

		return implode( ', ', $periods );
	}

	/**
	 * Convert combined period to allowed billing cycle
	 *
	 * @link https://asaasv3.docs.apiary.io/#reference/0/assinaturas/criar-nova-assinatura
	 *
	 * @param string $interval The subscription product billing interval.
	 * @param string $period The subscription product billing period.
	 * @return string|false The billing cycle or false if fails.
	 */
	public function convert_period( $interval = '', $period = '' ) {
		$combined_period = $interval . ' ' . $period;
		if ( array_key_exists( $combined_period, $this->allowed_period_combinations ) ) {
			return $this->allowed_period_combinations[ $combined_period ]['period'];
		}

		return false;
	}

	/**
	 * Checks if discount coupon is supported
	 *
	 * @param \WC_Coupon $coupon The discount coupon.
	 * @return bool True if coupon is supported.
	 */
	public function discount_coupon_supported( $coupon ) {
		if ( array_key_exists( $coupon->get_discount_type(), $this->allowed_discount_coupon_types ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Return supported coupon types string
	 *
	 * @return string
	 */
	public function get_supported_coupon_types_string() {
		$coupon_types = [];
		foreach ( $this->allowed_discount_coupon_types as $key => $coupon_type ) {
			if ( ! in_array( $coupon_type, $coupon_types ) ) {
				$coupon_types[] = $coupon_type;
			}
		}

		return implode( ', ', $coupon_types );
	}

	/**
	 * Gets Subscription object by Asaas subscription id
	 *
	 * @param  string $subscription_id The Asaas subscription id.
	 * @return WC_Subscription|bool WC_Subscription object if it found. Otherwise, false.
	 */
	public function get_subscription_by_id( $subscription_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$query = $wpdb->prepare(
			'SELECT ID FROM ' . $wpdb->posts . ' as P' .
			' INNER JOIN ' . $wpdb->postmeta . ' as PM' .
			' WHERE P.ID = PM.post_id ' .
			'   AND P.post_type   = %s' .
			'   AND PM.meta_key   = %s ' .
			'   AND PM.meta_value = %s',
			array(
				'shop_subscription',
				'_asaas_subscription_id',
				$subscription_id,
			)
		);

		$results = $wpdb->get_results( $query );

		if ( count( $results ) > 0 && empty( $wpdb->last_error ) && function_exists( '\wcs_get_subscription' ) ) {
			return \wcs_get_subscription( $results[0]->ID );
		}

		return false;
	}

	/**
	 * Gets order by Asaas payment id
	 *
	 * @param string $payment_id The Asaas payment id.
	 * @return WC_Order|bool WC_Order object if it found. Otherwise, false.
	 */
	public function get_order_by_payment_id( $payment_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$query = $wpdb->prepare(
			'SELECT post_id FROM ' . $wpdb->postmeta .
			' WHERE meta_key = %s' .
			'   AND meta_value = %s',
			array(
				'_asaas_id',
				$payment_id,
			)
		);

		$results = $wpdb->get_results( $query );

		if ( count( $results ) > 0 && empty( $wpdb->last_error ) ) {
			return wc_get_order( $results[0]->post_id );
		}

		return false;
	}
}
