<?php
/**
 * GMW Premium Settings - Search form template functions.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Pre defined options selector
 *
 * @param html  $output  the element output.
 *
 * @param array $args    array of arguments.
 *
 * @param array $options array of options.
 *
 * @return HTML element.
 */
function gmw_search_form_pre_defined_options_selector( $output, $args, $options ) {

	$name_tag = esc_attr( $args['name_tag'] );

	$output .= '<input type="hidden" name="' . $name_tag . '[]" value="" />';

	return $output;
}
add_filter( 'gmw_search_form_pre_defined_options_selector', 'gmw_search_form_pre_defined_options_selector', 5, 3 );

/**
 * Checkboxes options selector
 *
 * @param html  $output  the element output.
 *
 * @param array $args    array of arguments.
 *
 * @param array $options array of options.
 *
 * @return HTML element.
 */
function gmw_search_form_checkboxes_options_selector( $output, $args, $options ) {

	$id_tag   = ( '' !== $args['id_tag'] ) ? 'id="' . esc_attr( $args['id_tag'] ) . '"' : '';
	$name_tag = esc_attr( $args['name_tag'] );
	$object   = esc_attr( $args['object'] );

	$output = '<ul ' . $id_tag . ' class="gmw-' . $object . '-checkboxes gmw-checkboxes-options-selector ' . esc_attr( $args['class_tag'] ) . '">';

	foreach ( $options as $value => $name ) {

		$checked = ( isset( $_GET[ $name_tag ] ) && in_array( $value, $_GET[ $name_tag ], true ) ) ? 'checked="checked"' : ''; // WPCS: CSRF ok, sanitization ok.

		$output .= '<li class="gmw-' . $object . '-checkbox-wrapper ' . esc_attr( $value ) . '">';
		$output .= '<label>';
		$output .= '<input type="checkbox" name="' . $name_tag . '[]" class="gmw-' . $object . '-checkbox" value="' . esc_attr( $value ) . '" ' . $checked . '>';
		$output .= esc_html( $name );
		$output .= '</label></li>';
	}

	$output .= '</ul>';

	return $output;
}
add_filter( 'gmw_search_form_checkboxes_options_selector', 'gmw_search_form_checkboxes_options_selector', 5, 3 );
add_filter( 'gmw_search_form_checkbox_options_selector', 'gmw_search_form_checkboxes_options_selector', 5, 3 );

/**
 * Smartbox options selector
 *
 * @param html  $output  the element output.
 *
 * @param array $args    array of arguments.
 *
 * @param array $options array of options.
 *
 * @return HTML element.
 */
function gmw_search_form_smartbox_options_selector( $output, $args, $options ) {

	$id_tag   = ( '' !== $args['id_tag'] ) ? 'id="' . esc_attr( $args['id_tag'] ) . '"' : '';
	$multiple = ( 'smartbox_multiple' === $args['usage'] ) ? 'multiple' : '';

	$output .= '<select name="' . esc_attr( $args['name_tag'] ) . '[]" data-placeholder="' . esc_attr( $args['show_options_all'] ) . '" ' . $id_tag . ' class="gmw-form-field gmw-' . esc_attr( $args['object'] ) . '-field gmw-smartbox" ' . $multiple . '>';

	if ( 'smartbox' === $args['usage'] && ! empty( $args['show_options_all'] ) ) {
		$output .= '<option value="">' . esc_attr( $args['show_options_all'] ) . '</option>';
	}

	foreach ( $options as $value => $name ) {

		$selected = ( isset( $_GET[ $args['name_tag'] ] ) && in_array( $value, $_GET[ $args['name_tag'] ], true ) ) ? 'selected="selected"' : ''; // WPCS: CSRF ok, sanitization ok.

		$output .= '<option value="' . esc_attr( $value ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
	}

	$output .= '</select>';

	gmw_ps_enqueue_smartbox();

	return $output;
}
add_filter( 'gmw_search_form_smartbox_options_selector', 'gmw_search_form_smartbox_options_selector', 5, 3 );
add_filter( 'gmw_search_form_smartbox_multiple_options_selector', 'gmw_search_form_smartbox_options_selector', 5, 3 );

/**
 * Search form keywords field
 *
 * @param array $gmw gmw form.
 *
 * @return HTML element
 */
function gmw_get_search_form_keywords_field( $gmw ) {

	if ( empty( $gmw['search_form']['keywords']['usage'] ) ) {
		return;
	}

	$settings = $gmw['search_form']['keywords'];
	$id       = absint( $gmw['ID'] );

	$output = '<div class="gmw-form-field-wrapper gmw-keywords-field-wrapper">';

	if ( '' !== $settings['label'] ) {
		$output .= '<label for="gmw-keywords-' . $id . '" class="gmw-field-label">' . esc_html( $settings['label'] ) . '</label>';
	}

	$args = array(
		'id'          => $id,
		'placeholder' => isset( $settings['placeholder'] ) ? $settings['placeholder'] : __( 'Enter keywords', 'gmw-premium-settings' ),
	);

	$output .= GMW_Search_Form_Helper::keywords_field( $args );
	$output .= '</div>';

	return $output;
}

/**
 * Output keywords field.
 *
 * @param  array $gmw gmw form.
 */
function gmw_search_form_keywords_field( $gmw = array() ) {

	do_action( 'gmw_before_search_form_keywords_field', $gmw );

	echo gmw_get_search_form_keywords_field( $gmw ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_keywords_field', $gmw );
}

/**
 * Append the keywords field to the search form dynamically using a hook.
 *
 * @param  array $gmw GEO my WP form.
 */
function gmw_append_keywords_field_to_search_form( $gmw = array() ) {
	echo gmw_get_search_form_keywords_field( $gmw ); // WPCS: XSS ok.
}
add_action( 'gmw_before_search_form_address_field', 'gmw_append_keywords_field_to_search_form' );

/**
 * Multiple address field
 *
 * @param  array $output address field output.
 *
 * @param  array $gmw    gmw form.
 *
 * @return address fields HTML element.
 */
function gmw_get_search_form_address_fields( $output, $gmw ) {

	// abort if using single address field.
	if ( empty( $gmw['search_form']['address_field']['usage'] ) || 'single' === $gmw['search_form']['address_field']['usage'] ) {
		return $output;
	}

	if ( empty( $gmw['search_form']['address_field']['multiple'] ) ) {
		return $output;
	}

	$id = absint( $gmw['ID'] );

	$output = '<div class="gmw-address-fields-wrapper">';

	foreach ( $gmw['search_form']['address_field']['multiple'] as $field_name => $field_args ) {

		// sanitize fields.
		array_map( 'esc_attr', $field_args );

		$placeholder = '';
		$mandatory   = isset( $field_args['mandatory'] ) ? 'mandatory' : '';
		$usage       = isset( $field_args['usage'] ) ? $field_args['usage'] : 'disabled';
		$field_name  = esc_attr( $field_name );
		$url_px      = esc_attr( gmw_get_url_prefix() );
		$placeholder = isset( $field_args['placeholder'] ) ? $field_args['placeholder'] : '';

		if ( 'default' === $usage ) {

			$output .= "<input type='hidden' id='gmw-{$field_name}-field-{$id}'  name='{$url_px}address[{$field_name}]' value='{$field_args['value']}' />";

		} elseif ( 'include' === $usage ) {

			$value   = ! empty( $_GET[ $url_px . 'address' ][ $field_name ] ) ? esc_attr( stripslashes( $_GET[ $url_px . 'address' ][ $field_name ] ) ) : ''; // WPCS: CSRF ok, sanitization ok.
			$output .= "<div class=\"gmw-form-field-wrapper gmw-{$field_name}-field-wrapper\">";

			// create label.
			if ( ! empty( $field_args['title'] ) ) {

				if ( isset( $field_args['within'] ) ) {

					$placeholder = $field_args['title'];

				} else {

					$output .= "<label for='gmw-{$field_name}-field-{$id}' class='gmw-field-label'>{$field_args['title']}</label>";
				}
			}

			if ( 'country' !== $field_name ) {
				// input text field.
				$output .= "<input type='text' id='gmw-{$field_name}-field-{$id}' name='{$url_px}address[{$field_name}]' class='gmw-address {$field_name} {$mandatory}' value='{$value}' size='20' placeholder='{$placeholder}' />";
			} else {

				$placeholder = ! empty( $placeholder ) ? $placeholder : __( 'Select country...', 'gmw-premium-settings' );

				$output .= "<select id='gmw-{$field_name}-field-{$id}' data-placeholder='{$placeholder}' name='{$url_px}address[country]' class='gmw-smartbox gmw-saf-{$field_name} gmw-address'>";
				$output .= '<option value="" selected="selected">' . $placeholder . '</option>';

				foreach ( gmw_get_countries_array() as $country ) {

					$selected = ( $value === $country['name'] ) ? 'selected="selected"' : '';

					$output .= '<option value="' . esc_attr( $country['name'] ) . '" ' . $selected . '>' . esc_attr( $country['name'] ) . '</option>';
				}

				$output .= '</select>';

				gmw_ps_enqueue_smartbox();
			}

			$output .= '</div>';
		}
	}
	$output .= '</div>';

	return $output;

}
add_filter( 'gmw_search_form_address_field', 'gmw_get_search_form_address_fields', 10, 2 );

/**
 * Output address fields.
 *
 * @param  array $gmw gmw form.
 */
function gmw_search_form_address_fields( $gmw ) {

	do_action( 'gmw_before_search_form_address_fields', $gmw );

	echo gmw_get_search_form_address_fields( $gmw ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_address_fields', $gmw );
}

/**
 * Get radius slider field element
 *
 * @param array $gmw gmw form.
 *
 * @return HTML element
 */
function gmw_get_search_form_radius_slider( $gmw ) {

	if ( empty( $gmw['search_form']['radius_slider']['enabled'] ) ) {
		return false;
	}

	$field_settings = $gmw['search_form']['radius_slider'];

	$default_value = isset( $field_settings['default_value'] ) ? $field_settings['default_value'] : '50';
	$max_value     = isset( $field_settings['max_value'] ) ? $field_settings['max_value'] : '200';
	$min_value     = isset( $field_settings['min_value'] ) ? $field_settings['min_value'] : '0';
	$slabel        = ( 'imperial' === $gmw['search_form']['units'] ) ? __( 'Miles', 'gmw-premium-settings' ) : __( 'Kilometers', 'gmw-premium-settings' );
	$label         = ( 'both' === $gmw['search_form']['units'] ) ? __( 'Radius: ', 'gmw-premium-settings' ) : $slabel;

	$args = array(
		'id'            => $gmw['ID'],
		'default_value' => $default_value,
		'max_value'     => $max_value,
		'min_value'     => $min_value,
		'label'         => $label,
	);

	$output = '<div class="gmw-form-field-wrapper gmw-radius-slider-wrapper">';

	$output .= GMW_PS_Template_Functions_Helper::get_radius_slider( $args );

	$output .= '</div>';

	return $output;
}

/**
 * Output radius slider in a search form.
 *
 * @param  array $gmw gmw form.
 */
function gmw_search_form_radius_slider( $gmw = array() ) {

	do_action( 'gmw_before_search_form_radius_slider', $gmw );

	echo gmw_get_search_form_radius_slider( $gmw ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_radius_slider', $gmw );
}

/**
 * Append radius slider field to the search form using action hook
 *
 * @param HTML  $output radius field html element.
 *
 * @param array $gmw    gmw form.
 *
 * @return HTML element
 */
function gmw_replace_radius_field_with_slider( $output, $gmw ) {

	$slider = gmw_get_search_form_radius_slider( $gmw );

	if ( ! $slider ) {
		return $output;
	} else {
		return $slider;
	}
}
add_filter( 'gmw_radius_dropdown_output', 'gmw_replace_radius_field_with_slider', 10, 2 );
