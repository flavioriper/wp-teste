<?php
/**
 * One Click options fields class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Checkout\Form_Field;

use WC_Asaas\Helper\Validation_Helper;
use WC_Asaas\WC_Asaas;
use WC_Asaas\Gateway\Gateway;

/**
 * Radio group to select one option
 */
class One_Click_Options extends Form_Field {

	/**
	 * The unique WooCommerce field type
	 *
	 * @var string
	 */
	public function get_type() {
		return 'asaas-one-click-options';
	}

	/**
	 * Render options fields
	 *
	 * First create a string with all fields HTML and after return or echo based in the `$args['return']` value.
	 *
	 * @see woocommerce_form_field()
	 *
	 * @param string $field The field HTML, not used.
	 * @param string $key The field key.
	 * @param array  $args The field args.
	 * @param string $value The current field value.
	 */
	public function field( $field, $key, $args, $value ) {
		$subfields      = $this->get_subfields( $key, $args );
		$return         = $args['return'];
		$args['return'] = true;
		$field          = '';

		foreach ( $subfields as $subkey => $subfield ) {
			if ( $key === $subkey ) {
				// the card fields is render together with the new credit card radio option.
				continue;
			}

			if ( $key . '_options' === $subkey ) {
				foreach ( $subfield['options'] as $option_name => $option_value ) {
					$field .= $this->radio_field( $subkey, $option_name, $option_value, 'credit-card-0' );
				}

				// Add filter to inject the credit card after the new credit card option.
				add_filter( 'woocommerce_form_field_radio', array( self::get_instance(), 'add_card_fields' ), 10, 4 );
				$field .= $this->radio_field( $subkey, 'credit-card-new', __( 'New Credit Card', 'woo-asaas' ) );
				remove_filter( 'woocommerce_form_field_radio', array( self::get_instance(), 'add_card_fields' ) );
				continue;
			}

			$field .= woocommerce_form_field( $subkey, $subfield, $value );
		}

		if ( $return ) {
			return $field;
		}

		echo $field; // WPCS: XSS ok.
	}

	/**
	 * Get the one click buy option fields
	 *
	 * @param string $key The field key.
	 * @param array  $args The field args.
	 * @return array The subfield list.
	 */
	public function get_subfields( $key, $args ) {
		return apply_filters(
			'woocommerce_asaas_one_click_options_subfields', array(
				$key . '_label'   => array_merge(
					$args, array(
						'type'     => Label::get_instance()->get_type(),
						'label'    => $args['label'],
						'id'       => 'assas-cc-one-click-buy-label-field',
						'class'    => array( 'one-click-buy-label' ),
						'required' => false,
					)
				),
				$key . '_options' => array_merge(
					$args, array(
						'class'    => array( 'one-click-buy-option' ),
						'options'  => $args['options'],
						'return'   => true,
						'required' => true,
						'type'     => 'radio',
					)
				),
				$key              => array_merge(
					$args, array(
						'type'     => Card::get_instance()->get_type(),
						'return'   => true,
						'required' => false,
					)
				),
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
	 * @return boolean
	 */
	public function validate( $gateway, $key, $field, $data ) {
		$subfields         = $this->get_subfields( $key, $field );
		$validation_helper = new Validation_Helper();
		$errors            = new \WP_Error();

		foreach ( $subfields as $subkey => $subfield ) {
			if ( $key === $subkey && 'credit-card-new' !== $data[ $key . '_options' ] ) {
				// validate card fields just if is a new card.
				continue;
			}

			$validation_helper->validate_required( $gateway, $subkey, $subfield, $data );

			$field_type = WC_Asaas::get_instance()->get_form_field_object_from_type( $subfield['type'] );

			if ( ! is_null( $field_type ) ) {
				$field_type->validate( $gateway, $subkey, $subfield, $data );
			}
		}

		$gateway->add_validation_errors( $errors );
		return empty( $errors->get_error_codes() );
	}

	/**
	 * Create a radio field
	 *
	 * Replace the container id to avoid repetition.
	 *
	 * @param string $field_name   The field name.
	 * @param string $option_key   The option key.
	 * @param string $option_value The option value name.
	 * @param string $default The field default value.
	 * @return string The field HTML.
	 */
	private function radio_field( $field_name, $option_key, $option_value, $default = false ) {
		$args = array(
			'class'   => array( 'one-click-buy-option' ),
			'default' => false !== $default ? $default : '',
			'options' => array( $option_key => $option_value ),
			'return'  => true,
			'type'    => 'radio',
		);

		$field = woocommerce_form_field( $field_name, $args );
		$field = str_replace( $field_name . '_field', $field_name . $option_key . '_field', $field );

		return $field;
	}

	/**
	 * Add credit card fields HTML markup after the label
	 *
	 * @see Card
	 *
	 * @param string $field The field HTML markup.
	 * @param string $key The field key.
	 * @param array  $args The field args.
	 * @param array  $value The field default values.
	 * @return string The field markup with the credit card fields.
	 */
	public function add_card_fields( $field, $key, $args, $value ) {
		$card_fields = woocommerce_form_field(
			'asaas_cc', array(
				'type'   => Card::get_instance()->get_type(),
				'return' => true,
			)
		);

		return preg_replace( '/(.*)(<\/p>)/', '$1' . $card_fields . '$2', $field );
	}
}
