<?php
/**
 * Credit Card billing type class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Billing_Type;

/**
 * Credit Card billing type
 */
class Credit_Card extends Billing_Type {

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Asaas\Billing_Type\Billing_Type::get_id()
	 */
	public function get_id() {
		return 'CREDIT_CARD';
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Asaas\Billing_Type\Billing_Type::get_name()
	 */
	public function get_slug() {
		return 'credit-card';
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Asaas\Billing_Type\Billing_Type::get_name()
	 */
	public function get_name() {
		return __( 'Credit Card', 'woo-asaas' );
	}

	/**
	 * Get avalailable credit card brands with the name and validation regex to number and security code
	 *
	 * @return array The avalailable credit cards.
	 */
	public function brands() {
		return apply_filters(
			'woocommerce_asaas_cc_available_brands', array(
				'visa'       => array(
					'name'                     => __( 'Visa', 'woo-asaas' ),
					'number_validation'        => '/^4\d{12}(\d{3})?$/',
					'security_code_validation' => '/^[0-9]{3}$/',
				),
				'mastercard' => array(
					'name'                     => __( 'Mastercard', 'woo-asaas' ),
					'number_validation'        => '/^(5[1-5]\d{14}|2(22[1-9]\d{12}|2[3-9]\d{13}|[3-6]\d{14}|7[0-1]\d{13}|720\d{12})|6(0[0,3]\d{13}|3[7,9]\d{13}|7\d\d{13})|975230\d{10})$/',
					'security_code_validation' => '/^[0-9]{3}$/',
				),
				'amex'       => array(
					'name'                     => __( 'Amex', 'woo-asaas' ),
					'number_validation'        => '/^3[47]\d{13}$/',
					'security_code_validation' => '/^[0-9]{4}$/',
				),
				'diners'     => array(
					'name'                     => __( 'Diners', 'woo-asaas' ),
					'number_validation'        => '/^3(0[0-5]|[68]\d)\d{11}$/',
					'security_code_validation' => '/^[0-9]{3}$/',
				),
				'elo'        => array(
					'name'                     => __( 'Elo', 'woo-asaas' ),
					'number_validation'        => '/^((((438935)|(431274)|(457393)|(504175)|(451416)|(627780)|(636297)|(636368))\d{0,10})|((506[6,7])|(4576)|(4011)|(509\d)|(650\d)|(6516[5-7])|(65500[0-2,4-9])|(6550[1-2,4-9])|(65503[1-9]))\d{0,12})$/',
					'security_code_validation' => '/^[0-9]{3}$/',
				),
				'discover'   => array(
					'name'                     => __( 'Discover', 'woo-asaas' ),
					'number_validation'        => '/^6(?:011|5[0-9]{2})\d{12}$/',
					'security_code_validation' => '/^[0-9]{3}$/',
				),
				'hipercard'   => array(
					'name'                     => __( 'Hipercard', 'woo-asaas' ),
					'number_validation'        => '/^(606282\d{10}(\d{3})?)|(3841\d{15})$/',
					'security_code_validation' => '/^[0-9]{3}$/',
				),
			)
		);
	}
}
