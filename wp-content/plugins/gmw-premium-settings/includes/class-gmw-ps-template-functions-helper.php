<?php
/**
 * GMW Premium Settings - Template functions helper class.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 *
 * Premium Settings Helper class
 */
class GMW_PS_Template_Functions_Helper {

	/**
	 * Radius slider
	 *
	 * @param array $args field arguments.
	 *
	 * @return HTML element
	 */
	public static function get_radius_slider( $args = array() ) {

		$defaults = array(
			'id'            => 0,
			'default_value' => '50',
			'max_value'     => '200',
			'min_value'     => '0',
			'label'         => 'Miles',
			'steps'         => '1',
		);

		$args = wp_parse_args( $args, $defaults );

		// Deprecated - misspelled.
		$args = apply_filters( 'gmw_search_forms_range_slider_args', $args );

		// New filter.
		$args = apply_filters( 'gmw_search_form_range_slider_args', $args );

		$url_px = gmw_get_url_prefix();

		$id            = absint( $args['id'] );
		$default_value = ( '' !== $args['default_value'] ) ? $args['default_value'] : '50';
		$default_value = isset( $_GET[ $url_px . 'distance' ] ) ? esc_attr( $_GET[ $url_px . 'distance' ] ) : esc_attr( $default_value ); // WPSC: CSRF ok, sanitization ok.
		$max_value     = ( '' !== $args['max_value'] ) ? $args['max_value'] : '200';
		$min_value     = ( '' !== $args['min_value'] ) ? $args['min_value'] : '0';

		$output  = '<label class="gmw-radius-slider-label gmw-radius-range-output">';
		$output .= '<output class="slider-value">' . $default_value . '</output>';
		$output .= '<span class="units">' . esc_html( $args['label'] ) . '</span>';
		$output .= '</label>';
		$output .= '<input type="range" class="gmw-radius-slider gmw-range-slider" min="' . esc_attr( $min_value ) . '" max="' . esc_attr( $max_value ) . '" step="' . esc_attr( $args['steps'] ) . '" value="' . $default_value . '" name="' . $url_px . 'distance" />';

		return $output;
	}
}
