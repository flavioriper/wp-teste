<?php
/**
 * Default Installment Settings class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Installments\Admin\Settings;

use WC_Asaas\Api\Api_Limit;
use WC_Asaas\Gateway\Gateway;

/**
 * Credit card settings values
 */
class Default_Installment_Settings {

	private $gateway_prefix;

	private $api_limit;

	public function __construct( Gateway $gateway, Api_Limit $api_limit ) {
		$this->gateway_prefix = $gateway->prefix();
		$this->api_limit = $api_limit;
	}

	/**
	 * The maximum installments allowed in a order
	 *
	 * @return int The max installments
	 */
	public function get_max_installments() {
		$max_installments_limit = $this->api_limit->max_installments( $this->gateway_prefix );

		return apply_filters( "woocommerce_asaas_{$this->gateway_prefix}_max_installments", $max_installments_limit );
	}

	/**
	 * The minimum value for each installment
	 *
	 * @return number The installment value
	 */
	public function get_min_installment_value() {
		$min_installment_value_limit = $this->api_limit->min_installment_value();

		return apply_filters( "woocommerce_asaas_{$this->gateway_prefix}_min_installment_value", $min_installment_value_limit );
	}
}
