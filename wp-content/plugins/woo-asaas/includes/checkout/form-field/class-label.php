<?php
/**
 * Just label field class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Checkout\Form_Field;

use WC_Asaas\Gateway\Gateway;

/**
 * Checkout card expiration fields
 */
class Label extends Form_Field {

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Asaas\Checkout\Form_Field\Form_Field::get_type()
	 */
	public function get_type() {
		return 'asaas-label';
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Asaas\Checkout\Form_Field\Form_Field::get_subfields()
	 *
	 * @param string $key The field key.
	 * @param array  $args The field args.
	 * @return array The subfield list.
	 */
	public function get_subfields( $key, $args ) {
		return array();
	}

	/**
	 * Render label field
	 *
	 * @see woocommerce_form_field()
	 *
	 * @param string $field The field HTML, not used.
	 * @param string $key The field key.
	 * @param array  $args The field args.
	 * @param string $value The current field value.
	 */
	public function field( $field, $key, $args, $value ) {
		$sort          = ! empty( $args['priority'] ) ? $args['priority'] : '';
		$args['class'] = is_array( $args['class'] ) ? $args['class'] : array();

		$required = '';
		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required        = ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>';
		}

		$field_container = '<p class="form-row %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</p>';
		$field_html      = '<label class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';

		$container_class = esc_attr( implode( ' ', $args['class'] ) );
		$container_id    = esc_attr( $args['id'] ) . '_field';
		$field           = sprintf( $field_container, $container_class, $container_id, $field_html );

		return $field;
	}

	/**
	 * The label doesn't need be validated because hasn't data
	 *
	 * @param Gateway $gateway The gateway is being processed.
	 * @param array   $key The field key.
	 * @param array   $field The field args.
	 * @param array   $data The payment form posted data.
	 * @return boolean True.
	 */
	public function validate( $gateway, $key, $field, $data ) {
		return true;
	}
}
