<?php
/**
 * WP_Error helper class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Helper;

/**
 * WP_Error extra functions
 */
class WP_Error_Helper {

	/**
	 * Merge two arguments
	 *
	 * Add second error data to the first.
	 *
	 * @param \WP_Error $error The error that will receive the data.
	 * @param \WP_Error $another_error The error that will pass the data.
	 */
	public function merge( $error, $another_error ) {
		if ( ! is_wp_error( $error ) || ! is_wp_error( $another_error ) ) {
			return;
		}

		$error_codes = $another_error->get_error_codes();
		foreach ( $error_codes as $error_code ) {
			// Merge error messages.
			$error_messages = $another_error->get_error_messages( $error_code );
			foreach ( $error_messages as $error_message ) {
				$error->add( $error_code, $error_message );
			}
			// Merge error data.
			$error_data = $another_error->get_error_data( $error_code );
			if ( $error_data ) {
				$prev_error_data = $error->get_error_data( $error_code );
				if ( ! empty( $prev_error_data ) && is_array( $error_data ) && is_array( $prev_error_data ) ) {
					$error->add_data( array_merge( $prev_error_data, $error_data ), $error_code );
				} else {
					$error->add_data( $error_data, $error_code );
				}
			}
		}
	}
}
