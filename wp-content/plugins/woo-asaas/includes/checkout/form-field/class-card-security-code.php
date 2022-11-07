<?php
/**
 * Checkout card expiration fields class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Checkout\Form_Field;

use WC_Asaas\Gateway\Gateway;
use WC_Asaas\Helper\Validation_Helper;

/**
 * Checkout card expiration fields
 */
class Card_Security_Code extends Form_Field {

	/**
	 * The unique WooCommerce field type
	 *
	 * @var string
	 */
	public function get_type() {
		return 'asaas-card-security-code';
	}

	/**
	 * Get the card security code field
	 *
	 * @param string $key The field key.
	 * @param array  $args The field args.
	 * @return array The subfield list.
	 */
	public function get_subfields( $key, $args ) {
		$args['type']              = 'text';
		$args['custom_attributes'] = array(
			'data-mask' => '000[0]',
		);

		return apply_filters(
			'woocommerce_asaas_card_security_code_subfields', array(
				$key . '_security_code' => $args,
			), $key
		);
	}

	/**
	 * Validate the value if some option was checked
	 *
	 * @param Gateway $gateway The gateway is being processed.
	 * @param array   $key The field key.
	 * @param array   $field The field args.
	 * @param array   $data The payment form posted data.
	 * @return boolean True, if the data is valid. False, otherwise.
	 */
	public function validate( $gateway, $key, $field, $data ) {
		$validation_helper = new Validation_Helper();
		$errors            = new \WP_Error();

		foreach ( $this->get_subfields( $key, $field ) as $subkey => $subfield ) {
			$validation_helper->validate_required( $gateway, $subkey, $subfield, $data );
		}

		$gateway->add_validation_errors( $errors );
		return empty( $errors->get_error_codes() );
	}

	/**
	 * Get invalid security code message
	 *
	 * @return string The message.
	 */
	public function invalid_security_code_message() {
		return apply_filters( 'woocommerce_asaas_invalid_security_code_message', __( 'Invalid security code.', 'woo-asaas' ) );
	}
}
