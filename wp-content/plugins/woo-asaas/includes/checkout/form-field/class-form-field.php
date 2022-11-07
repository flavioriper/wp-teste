<?php
/**
 * Abstract Form Field class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Checkout\Form_Field;

use WC_Asaas\Gateway\Gateway;
use WC_Asaas\WC_Asaas;

/**
 * Abstract class to custom form fields
 */
abstract class Form_Field {

	/**
	 * Get the form type unique identification
	 *
	 * @return string The type identification.
	 */
	abstract public function get_type();

	/**
	 * Get the subfield list of this field
	 *
	 * The list accept primary woocommerce fields or any Asaas form field type.
	 *
	 * @see woocommerce_form_field()
	 *
	 * @param string $key The field key.
	 * @param array  $args The field args.
	 * @return array The subfield list.
	 */
	abstract public function get_subfields( $key, $args );

	/**
	 * Validate the submitted data
	 *
	 * If not valid, the errors must be added to checkout errors.
	 *
	 * @param Gateway $gateway The gateway is being processed.
	 * @param array   $key The field key.
	 * @param array   $field The field args.
	 * @param array   $data The payment form posted data.
	 * @return boolean True, if the data is valid. False, otherwise.
	 */
	abstract public function validate( $gateway, $key, $field, $data );

	/**
	 * Instance of this class
	 *
	 * @var self
	 */
	private static $instances = array();

	/**
	 * Block external object instantiation.
	 */
	protected function __construct() {
		add_filter( 'woocommerce_form_field_' . $this->get_type(), array( $this, 'field' ), 10, 4 );
	}

	/**
	 * Return an instance of this class
	 *
	 * @return self A single instance of this class.
	 */
	public static function get_instance() {
		$class = get_called_class();
		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new $class();
		}

		return self::$instances[ $class ];
	}

	/**
	 * Render simple WooCommerce field
	 *
	 * @see woocommerce_form_field()
	 *
	 * @param string $field The field HTML, not used.
	 * @param string $key The field key.
	 * @param array  $args The field args.
	 * @param string $value The current field value.
	 */
	public function field( $field, $key, $args, $value ) {
		$return         = $args['return'];
		$args['return'] = true;

		$field = '';
		foreach ( $this->get_subfields( $key, $args ) as $subfield ) {
			$field .= woocommerce_form_field( $key, $subfield, $value );
		}

		if ( $return ) {
			return $field;
		}

		echo $field; // WPCS: XSS ok.
	}

	/**
	 * Process the field data on payment process
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
			$field_type = WC_Asaas::get_instance()->get_form_field_object_from_type( $subfield['type'] );

			if ( is_null( $field_type ) ) {
				$data[ $subkey ] = isset( $_POST[ $subkey ] ) ? sanitize_text_field( wp_unslash( $_POST[ $subkey ] ) ) : ''; // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
				continue;
			}

			if ( $field_name . '_expiration' === $subkey ) {
				$key = $subkey;
			}

			$field_type->process_data( $key, $subfield, $data, $subkey );
			$key = $field_name;
		}
	}
}
