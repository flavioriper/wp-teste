<?php
/**
 * Api Limit class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Api;

/**
 * Define the limit existent on Asaas API
 */
class Api_Limit {

	/**
	 * Max installments allowed on API
	 * 
	 * @param string $prefix Prefix payment method name 
	 *
	 * @return number Max installments number
	 */
	public function max_installments( $prefix ) {
		if( "ticket" === $prefix ) {
			return 60;
		}

		return 12;
	}

	/**
	 * The minimum value defined on API
	 *
	 * @return number The minimum installment value
	 */
	public function min_installment_value() {
		return 5;
	}
}
