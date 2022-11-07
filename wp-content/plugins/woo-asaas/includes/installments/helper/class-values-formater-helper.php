<?php
/**
 * Values Formater Helper class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Installments\Helper;

/**
 * Interest Installment formatter helper functions
 */
class Values_Formater_Helper {

	/**
	 * Convert the values from the settings form to save to the database.
	 * 
	 * @param array $values Interest installment form values.
	 * @return array
	 */
	public function convert_into_database_format( array $values ) {
		$formated_values = array();

		foreach ( $values as $key => $interest ) {
			$formated_values[ $key ] = $this->format_to_float_value( $interest );
		}
		
		return $formated_values;
	}

	/**
	 * Convert value to float format.
	 * 
	 * @param string $value Field value.
	 * @return float
	 */
	public function format_to_float_value( string $value ) {
		$formated_value = abs( floatval( str_replace( ',', '.', $value ) ) );
		return $formated_value;
	}

	/**
	 * Convert the values from the settings to be displayed on the frontend.
	 * 
	 * @param array $values Interest installment saved values.
	 * @return array
	 */
	public function convert_into_frontend_format( array $values ) {
		$formated_values = array();

		foreach ( $values as $key => $interest ) {
			$formated_values[ $key ] = $this->replace_dot_with_comma( $interest );
		}

		return $formated_values;
	}

	/**
	 * Format the value by replacing a dot with a comma.
	 * 
	 * @param string $value Field value.
	 * @return float
	 */
	public function replace_dot_with_comma( float $value ) {
		$formated_value = str_replace( '.', ',', $value );
		return $formated_value;
	}
}
