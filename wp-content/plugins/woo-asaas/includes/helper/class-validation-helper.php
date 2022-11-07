<?php
/**
 * Validation helper class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Helper;

use WC_Asaas\Gateway\Gateway;
use WC_Asaas\WC_Asaas;

/**
 * Validation helper functions
 */
class Validation_Helper {

	/**
	 * Valide a list of fields.
	 *
	 * @param Gateway $gateway The gateway is being processed.
	 * @param array   $fields The field list.
	 * @param array   $data The submitted data.
	 */
	public function validate_fields( $gateway, $fields, $data ) {
		foreach ( $fields as $key => $field ) {
			$field_type = WC_Asaas::get_instance()->get_form_field_object_from_type( $field['type'] );

			if ( ! is_null( $field_type ) ) {
				$field_type->validate( $gateway, $key, $field, $data );
			}

			$this->validate_required( $gateway, $key, $field, $data );
		}
	}

	/**
	 * Validate required fields.
	 *
	 * If any errors are found, they are added to the checkout errors.
	 *
	 * @param Gateway $gateway The gateway is being processed.
	 * @param array   $key The field key.
	 * @param array   $field The field args.
	 * @param array   $data The payment form posted data.
	 * @return boolean True, if is valid. Otherwise, false.
	 */
	public function validate_required( $gateway, $key, $field, $data ) {
		if ( empty( $field['required'] ) ) {
			return true;
		}

		$errors = new \WP_Error();

		if ( ! empty( $field['required'] ) && '' === $data[ $key ] ) {
			$errors->add( $gateway->get_error_code(), $this->required_message( $field['label'] ) );
		}

		$gateway->add_validation_errors( $errors );
		return empty( $errors->get_error_codes() );
	}

	/**
	 * Get the required field message
	 *
	 * @param string $field_name The complete field name.
	 * @return string The message.
	 */
	public function required_message( $field_name ) {
		return apply_filters(
			'woocommerce_asaas_checkout_required_field_message',
			/* translators: %s: field name */
			sprintf( __( '%s is a required field.', 'woo-asaas' ), '<strong>' . esc_html( $field_name ) . '</strong>' ),
			$field_name
		);
	}
}
