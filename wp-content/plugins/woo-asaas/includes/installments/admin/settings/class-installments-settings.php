<?php
/**
 * Installments Settings class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Installments\Admin\Settings;

use WC_Asaas\Admin\View;
use WC_Asaas\Api\Api_Limit;
use WC_Asaas\Gateway\Gateway;
use WC_Asaas\Installments\Helper\Values_Formater_Helper;

/**
 * Installments settings common methods
 */
class Installments_Settings {

	/**
	 * The billing type object data
	 *
	 * @var Gateway
	 */
	protected $gateway;

	/**
	 * Init the default field sections
	 *
	 * @param Gateway $gateway The gateway that call the logger.
	 */
	public function __construct( $gateway ) {
		$this->gateway          = $gateway;
		$this->default_settings = $this->default_settings();
	}

	private function default_settings() {
		$api_limit = new Api_Limit();
		$default_settings = new Default_Installment_Settings( $this->gateway, $api_limit );

		return $default_settings;
	}

	/**
	 * Validate the max installments settings field to stay in the in a range between 0 and the value of
	 * get_max_installments function
	 *
	 * @param string $value The input value.
	 * @return int The value sanitized.
	 */
	public function validate_max_installments_field( string $value ) : int {
		$value            = absint( $value );
		$max_installments = $this->default_settings->get_max_installments();

		if ( $max_installments < $value ) {
			return $max_installments;
		}

		return $value;
	}

	/**
	 * Validate the minimum intallment value setting to be more than the value returned by get_min_installment_value
	 *
	 * @param string $value The input value.
	 * @return string The value sanitized.
	 */
	public function validate_min_installment_value_field( string $value ) : string {
		$value                 = floatval( str_replace( ',', '.', $value ) );
		$min_installment_value = $this->default_settings->get_min_installment_value();

		if ( $min_installment_value > $value ) {
			return $min_installment_value;
		}

		return str_replace( '.', ',', $value );
	}

	/**
	 * Render custom type attribute.
	 *
	 * @param string $key The interest installment value key.
	 * @param array $data Field config data.
	 * @return string
	 */
	public function generate_interest_installment_html( string $key, array $data ) : string {
		$max_installments = absint( $this->gateway->settings['max_installments'] );

		if ( 0 === $max_installments ) {
			return '';
		}

		$max_installments     = absint( $this->gateway->settings['max_installments'] );
		$field_key            = $this->gateway->get_field_key( $key );
		$value                = (array) $this->gateway->get_option( $key, array() );
		$interest_installment = ( new Values_Formater_Helper() )->convert_into_frontend_format( $this->gateway->settings['interest_installment'] );

		$args = array(
			'value'                => $value,
			'data'                 => $data,
			'field_key'            => $field_key,
			'max_installments'     => $max_installments,
			'interest_installment' => $interest_installment,
		);

		return View::get_instance()->get_template_file( 'installments-interest-list.php', $args, true );
	}

	/**
	 * Validate the interest installment values setting.
	 *
	 * @param array|null $value The input value.
	 * @return array|null The value sanitized.
	 */
	public function validate_interest_installment_field( $value ) {
		if ( false === is_array( $value ) ) {
			return $value;
		}

		$formated_values = ( new Values_Formater_Helper() )->convert_into_database_format( $value );
		return $formated_values;
	}
}
