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
class Card_Number extends Form_Field {

	/**
	 * The unique WooCommerce field type
	 *
	 * @var string
	 */
	public function get_type() {
		return 'asaas-card-number';
	}

	/**
	 * Get the card field
	 *
	 * @param string $key The field key.
	 * @param array  $args The field args.
	 * @return array The subfield list.
	 */
	public function get_subfields( $key, $args ) {
		$args['type']              = 'text';
		$args['custom_attributes'] = array(
			'data-mask' => '0000 0000 0000 0000',
		);

		return apply_filters(
			'woocommerce_asaas_card_number_subfields', array(
				$key . '_number' => $args,
			), $key
		);
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @param Gateway $gateway The gateway is being processed.
	 * @param array   $key The field key.
	 * @param array   $field The field args.
	 * @param array   $data The payment form posted data.
	 */
	public function validate( $gateway, $key, $field, $data ) {
		$validation_helper = new Validation_Helper();
		$errors            = new \WP_Error();

		foreach ( $this->get_subfields( $key, $field ) as $subkey => $subfield ) {
			$validation_helper->validate_required( $gateway, $subkey, $subfield, $data );
		}

		return empty( $errors->get_error_codes() );
	}

	/**
	 * Process card number
	 *
	 * @param string         $key The field key.
	 * @param array          $field The field args.
	 * @param string         $data The field data.
	 * @param boolean|string $field_name The field name. Default: The key arg.
	 */
	public function process_data( $key, $field, &$data, $field_name = false ) {
		$field_name = false === $field_name ? $key : $field_name;
		$subfields  = $this->get_subfields( $key, $field );

		foreach ( $subfields as $subkey => $subfield ) {
			$subfield_data = isset( $_POST[ $subkey ] ) ? sanitize_text_field( wp_unslash( $_POST[ $subkey ] ) ) : ''; // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			$subfield_data = preg_replace( '/\s/', '', $subfield_data );

			$data[ $subkey ] = $subfield_data;
		}
	}
}
