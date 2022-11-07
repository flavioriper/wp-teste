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
class Card_Expiration extends Form_Field {

	/**
	 * The unique WooCommerce field type
	 *
	 * @var string
	 */
	public function get_type() {
		return 'asaas-card-expiration';
	}

	/**
	 * Get month and year fields
	 *
	 * @param string $key The field key.
	 * @param string $args The field args.
	 * @return array The subfields of expiration date.
	 */
	public function get_subfields( $key, $args ) {
		$args['class'] = is_array( $args['class'] ) ? $args['class'] : array();

		return apply_filters(
			'woocommerce_asaas_card_expiration_subfields', array(
				$key . '_label' => array_merge(
					$args, array(
						'type'  => Label::get_instance()->get_type(),
						'label' => apply_filters( 'woocommerce_asaas_card_expiration_label', __( 'Expiration date', 'woo-asaas' ) ),
						'id'    => 'assas-cc-expiration-label',
						'class' => array_merge( $args['class'], array( 'asaas-cc-form-field-no-margin' ) ),
					)
				),
				$key . '_month' => $this->month_args( $args ),
				$key . '_year'  => $this->year_args( $args ),
			), $key
		);
	}

	/**
	 * Render card expiration field
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
		$field          = '';

		foreach ( $this->get_subfields( $key, $args ) as $sub_key => $args ) {
			$field .= woocommerce_form_field( $sub_key, $args, $value );
		}

		if ( $return ) {
			return $field;
		}

		echo $field; // WPCS: XSS ok.
	}

	/**
	 * Add commom args between date fields
	 *
	 * @param array $args The field args.
	 * @return array The field args adding commom args.
	 */
	public function date_args( $args ) {
		// Remove wide class to put month and year in the same row.
		$wide_key = array_search( 'form-row-wide', $args['class'], true );
		if ( false !== $wide_key ) {
			unset( $args['class'][ $wide_key ] );
		}

		$args['type']     = 'text';
		$args['required'] = true;

		return $args;
	}

	/**
	 * Define month field args
	 *
	 * @link https://unmanner.github.io/imaskjs/guide.html#pattern
	 *
	 * @param array $args The field args.
	 * @return array The month field args.
	 */
	public function month_args( $args ) {
		$args['label']             = __( 'Month', 'woo-asaas' );
		$args['id']               .= '-month';
		$args['class'][]           = 'form-row-first';
		$args['placeholder']       = __( 'MM', 'woo-asaas' );
		$args['maxlength']         = 2;
		$args['custom_attributes'] = array(
			'data-mask' => '00',
		);

		return $this->date_args( $args );
	}

	/**
	 * Define year field args
	 *
	 * @link https://unmanner.github.io/imaskjs/guide.html#pattern
	 *
	 * @param array $args The field args.
	 * @return array The year field args.
	 */
	public function year_args( $args ) {
		$args['label']             = __( 'Year', 'woo-asaas' );
		$args['id']               .= '-year';
		$args['class'][]           = 'form-row-last';
		$args['placeholder']       = __( 'YYYY', 'woo-asaas' );
		$args['maxlength']         = 4;
		$args['custom_attributes'] = array(
			'data-mask' => '0000',
		);

		return $this->date_args( $args );
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
		$errors            = new \WP_Error();
		$validation_helper = new Validation_Helper();
		$expiration_month  = $data[ $key . '_expiration_month' ];
		$expiration_year   = $data[ $key . '_expiration_year' ];
		$current_month     = intval( date( 'n' ) );
		$current_year      = intval( date( 'Y' ) );

		foreach ( $this->get_subfields( $key . '_expiration', $field ) as $subkey => $subfield ) {
			/* translators: %s: the field label  */
			$subfield['label'] = sprintf( __( 'Expiration %s', 'woo-asaas' ), $subfield['label'] );
			$validation_helper->validate_required( $gateway, $subkey, $subfield, $data );

			if ( $key . '_expiration_month' === $subkey ) {
				$this->validate_month( $gateway, $errors, $expiration_month, $subfield['label'] );
			}

			if ( $key . '_expiration_year' === $subkey ) {
				$this->validate_year( $gateway, $errors, $expiration_year, $subfield['label'] );
			}
		}

		if ( intval( $expiration_year ) === $current_year && intval( $expiration_month ) < $current_month ) {
			$errors->add( $gateway->get_error_code(), $this->invalid_expiration_message() );
		}

		do_action( 'woocommerce_asaas_card_expiration_validate', $errors, $gateway, $key, $field, $data );

		$gateway->add_validation_errors( $errors );
		return empty( $errors->get_error_codes() );
	}

	/**
	 * Validate month field
	 *
	 * @param Gateway   $gateway The gateway.
	 * @param \WP_Error $errors The gateway errors.
	 * @param string    $expiration_month The expiration year value.
	 * @param string    $field_label The field label.
	 */
	private function validate_month( $gateway, $errors, $expiration_month, $field_label ) {
		if ( ! empty( $expiration_month ) && ( 0 === preg_match( '/^\d{2}$/', $expiration_month ) || intval( $expiration_month ) < 1 || intval( $expiration_month ) > 12 ) ) {
			$errors->add( $gateway->get_error_code(), $this->invalid_expiration_month_message( $field_label ) );
		}
	}

	/**
	 * Validate if the year data is valid
	 *
	 * Add 2000 to the year if the year has less than 4 digits.
	 *
	 * @param Gateway   $gateway The gateway.
	 * @param \WP_Error $errors The gateway errors.
	 * @param string    $expiration_year The expiration year value.
	 * @param string    $field_label The field label.
	 */
	private function validate_year( $gateway, $errors, $expiration_year, $field_label ) {
		$expiration_year_int = intval( $expiration_year );

		if ( 1000 > $expiration_year_int ) {
			$expiration_year_int += 2000;
		}

		if ( ! empty( $expiration_year ) && ( 0 === preg_match( '/^\d{2,}$/', $expiration_year ) || $expiration_year_int < intval( date( 'Y' ) ) ) ) {
			$errors->add( $gateway->get_error_code(), $this->invalid_expiration_year_message( $field_label ) );
		}
	}

	/**
	 * Get invalid expiration date message
	 *
	 * @return string The message.
	 */
	public function invalid_expiration_message() {
		return apply_filters(
			'woocommerce_asaas_checkout_invalid_expiration_message',
			__( '<strong>Expiration</strong> must be a non-past date.', 'woo-asaas' )
		);
	}

	/**
	 * Get invalid expiration month message
	 *
	 * @param string $field_label The field label.
	 * @return string The message.
	 */
	public function invalid_expiration_month_message( $field_label ) {
		return apply_filters(
			'woocommerce_asaas_checkout_invalid_expiration_month_message',
			/* translators: %s: field name */
			sprintf( __( '%s must have two digits and be a valid month number.', 'woo-asaas' ), '<strong>' . $field_label . '</strong>' )
		);
	}

	/**
	 * Get invalid expiration year message
	 *
	 * @param string $field_label The field label.
	 * @return string The message.
	 */
	public function invalid_expiration_year_message( $field_label ) {
		return apply_filters(
			'woocommerce_asaas_checkout_invalid_expiration_year_message',
			/* translators: %s: field name */
			sprintf( __( '%s must at least 2 digits and be a non-past year.', 'woo-asaas' ), '<strong>' . $field_label . '</strong>' )
		);
	}
}
