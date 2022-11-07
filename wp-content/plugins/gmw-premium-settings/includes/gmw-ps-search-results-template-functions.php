<?php
/**
 * GMW Premium Settings - Search results template functions.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add map icons column to db fields.
 *
 * @param  array $fields default columns.
 *
 * @param  array $form  gmw form.
 *
 * @return array db columns.
 */
function gmw_ps_modify_form_db_fields( $fields, $form ) {

	$fields[] = 'map_icon';

	return $fields;
}
add_filter( 'gmw_form_db_fields', 'gmw_ps_modify_form_db_fields', 20, 2 );

/**
 * Add orderby filter to the search results via hook.
 *
 * @see function in geo-my-wp/includes/template-functions/gmw-search-results-template-functions.php.
 *
 * @param array $gmw gmw form.
 */
function gmw_ps_add_search_results_orderby_filter( $gmw = array() ) {
	if ( 'posts_locator' === $gmw['slug'] || 'members_locator' === $gmw['slug'] ) {
		gmw_search_results_orderby_filter( $gmw );
	}
}
add_action( 'gmw_search_results_after_results_message', 'gmw_ps_add_search_results_orderby_filter' );

/**
 * Prevent form from collecting info window content when using ajax for info-windows.
 *
 * @param  array $gmw gmw form.
 */
function gmw_ps_disable_info_window_content( $gmw ) {
	if ( ! empty( $gmw['info_window']['ajax_enabled'] ) ) {
		add_filter( 'gmw_form_get_info_window_content', '__return_false' );
	}
}
add_action( 'gmw_form_before_search_query', 'gmw_ps_disable_info_window_content', 50 );

/**
 * Modify the info-window elements
 *
 * @param  array  $args     array of arguments.
 *
 * @param  [type] $location [description].
 *
 * @param  array  $gmw      gmw form.
 *
 * @return [type]           [description]
 */
function gmw_ps_info_window_args( $args, $location, $gmw ) {

	if ( empty( $gmw['info_window'] ) ) {
		return $args;
	}

	$settings = $gmw['info_window'];

	$args['type']            = $settings['iw_type'];
	$args['address_fields']  = ! empty( $settings['address_fields'] ) ? $settings['address_fields'] : false;
	$args['distance']        = ( isset( $settings['distance'] ) && $settings['distance'] ) ? true : false;
	$args['directions_link'] = ( isset( $settings['directions_link'] ) && $settings['directions_link'] ) ? true : false;
	$args['location_meta']   = ! empty( $settings['location_meta'] ) ? $settings['location_meta'] : false;

	if ( empty( $settings['image']['enabled'] ) ) {
		$args['image_url'] = false;
		$args['image']     = false;
	}

	return $args;
}
add_filter( 'gmw_info_window_args', 'gmw_ps_info_window_args', 50, 3 );

/**
 * No results message and links
 *
 * @param  string $message original no results message.
 *
 * @param  array  $gmw     the form being displayed.
 *
 * @return string              new message
 */
function gmw_ps_no_results_message( $message, $gmw ) {

	if ( ! isset( $gmw['no_results'] ) ) {
		return $message;
	}

	$form_field = $gmw['no_results'];

	if ( empty( $form_field['message'] ) ) {
		return '';
	}

	$message = $form_field['message'];
	$url_px  = esc_attr( gmw_get_url_prefix() );
	$form_id = absint( $gmw['ID'] );
	$class   = ( 'ajax_forms' === $gmw['addon'] ) ? ' gmw-ajax-form-link' : '';

	// wider search link.
	if ( strpos( $message, '{wider_search_link}' ) !== false && ! empty( $form_field['wider_search']['link_text'] ) ) {

		$url = add_query_arg(
			array(
				$url_px . 'distance' => ! empty( $form_field['wider_search']['radius'] ) ? $form_field['wider_search']['radius'] : '',
			)
		);

		$link = '<a class="gmw-wider-search-link' . $class . '" data-id="' . $form_id . '" href="' . esc_url( $url ) . '" data-distance="' . $form_field['wider_search']['radius'] . '" onclick="document.gmw_form.submit();">' . esc_attr( stripslashes( $form_field['wider_search']['link_text'] ) ) . '</a> ';

		$message = str_replace( '{wider_search_link}', $link, $message );
	}

	// all results link.
	if ( strpos( $message, '{all_results_link}' ) !== false && ! empty( $form_field['all_results_link'] ) ) {

		/** If ( 1 === 1 ) {
         */
		$args_array = array(
			$url_px . 'address' => array(),
			$url_px . 'lat'     => '',
			$url_px . 'lng'     => '',
		);

		if ( isset( $gmw['form_values']['keywords'] ) ) {
			$args_array[ $url_px . 'keywords' ] = '';
		}

		if ( isset( $gmw['form_values']['cf'] ) ) {
			$args_array['cf'] = array();
		}

		if ( isset( $gmw['form_values']['tax'] ) ) {
			$args_array['tax'] = array();
		}

		if ( isset( $gmw['form_values']['xf'] ) ) {
			$args_array['xf'] = array();
		}

		$url = add_query_arg( $args_array );

		$link = '<a href="' . esc_url( $url ) . '" onclick="document.gmw_form.submit();" class="gmw-all-results-link' . $class . '" data-id="' . $form_id . '">' . esc_attr( stripslashes( $form_field['all_results_link'] ) ) . ' </a>';
		/**  } else {

			$link = '<a href="#"  onclick="">' . esc_attr( stripslashes( $form_field['all_results_link'] ) ) . ' </a>';
		}*/

		$message = str_replace( '{all_results_link}', $link, $message );
	}

	return $message;
}
add_filter( 'gmw_no_results_message', 'gmw_ps_no_results_message', 10, 2 );

/**
 * Modify some map features ( user location map icon, map control ) in search results
 *
 * @param  array $map_elements  the original map element.
 *
 * @param  array $gmw          the form being displayed.
 *
 * @return array               modifyed map element.
 */
function gmw_ps_modify_map_elements( $map_elements, $gmw ) {

	// make sure we are in GEO my WP search results.
	if ( empty( $gmw ) || ! is_array( $gmw ) || ! isset( $gmw['search_form'] ) ) {
		return $map_elements;
	}

	$icons_data = gmw_get_icons();

	// check if sub addon exists. If so, we will use that for the prefix.
	$prefix = gmw_get_addon_data( $gmw['component'] );
	$prefix = $prefix['prefix'];

	// Set location icon size.
	if ( 'google_maps' !== GMW()->maps_provider || ( 'google_maps' === GMW()->maps_provider && ! empty( $gmw['map_markers']['icon_width'] ) && ! empty( $gmw['map_markers']['icon_height'] ) ) ) {
		$icon_width  = ! empty( $gmw['map_markers']['icon_width'] ) ? $gmw['map_markers']['icon_width'] : GMW()->default_icons['location_icon_size'][0];
		$icon_height = ! empty( $gmw['map_markers']['icon_height'] ) ? $gmw['map_markers']['icon_height'] : GMW()->default_icons['location_icon_size'][1];

		$map_elements['settings']['icon_size'] = array( $icon_width, $icon_height );
	}

	// Set user marker icon url.
	if ( isset( $gmw['map_markers']['user_marker'] ) ) {
		$map_elements['user_location']['map_icon'] = $icons_data[ $prefix . '_map_icons' ]['url'] . $gmw['map_markers']['user_marker'];
	} else {
		$map_elements['user_location']['map_icon'] = GMW()->default_icons['user_location_icon_url'];
	}

	// user marker size.
	if ( 'google_maps' !== GMW()->maps_provider || ( 'google_maps' === GMW()->maps_provider && ! empty( $gmw['map_markers']['user_icon_width'] ) && ! empty( $gmw['map_markers']['user_icon_height'] ) ) ) {
		$icon_width  = ! empty( $gmw['map_markers']['user_icon_width'] ) ? $gmw['map_markers']['user_icon_width'] : GMW()->default_icons['user_location_icon_size'][0];
		$icon_height = ! empty( $gmw['map_markers']['user_icon_height'] ) ? $gmw['map_markers']['user_icon_height'] : GMW()->default_icons['user_location_icon_size'][1];

		$map_elements['user_location']['icon_size'] = array( $icon_width, $icon_height );
	}

	// disable the map control. We will enable each one based on the form settings.
	$map_elements['map_options'] = array_merge(
		$map_elements['map_options'],
		array(
			'zoomControl'        => false,
			'rotateControl'      => false,
			'mapTypeControl'     => false,
			'streetViewControl'  => false,
			'overviewMapControl' => false,
			'scrollwheel'        => false,
			'scaleControl'       => false,
			'resizeMapControl'   => false,
		)
	);

	// max zoom level.
	if ( ! empty( $gmw['results_map']['max_zoom_level'] ) ) {
		$map_elements['map_options']['maxZoom'] = $gmw['results_map']['max_zoom_level'];
	}

	// Map controls.
	if ( ! empty( $gmw['results_map']['map_controls'] ) ) {
		foreach ( $gmw['results_map']['map_controls'] as $value ) {
			if ( 'resizeMapControl' === $value ) {
				$map_elements['map_options']['resizeMapControl'] = 'gmw-resize-map-trigger-' . $gmw['ID'];
			} else {
				$map_elements['map_options'][ $value ] = true;
			}
		}
	}

	// map styles.
	if ( ! empty( $gmw['results_map']['styles'] ) ) {

		$map_elements['map_options']['styles'] = json_decode( $gmw['results_map']['styles'] );

	} elseif ( ! empty( $gmw['results_map']['snazzy_maps_styles'] ) ) {

		$styles = get_option( 'SnazzyMapStyles', null );

		foreach ( $styles as $style ) {

			if ( $style['id'] === $gmw['results_map']['snazzy_maps_styles'] ) {
				$map_elements['map_options']['styles'] = json_decode( $style['json'] );
			}
		}
	}

	return $map_elements;
}
add_filter( 'gmw_map_element', 'gmw_ps_modify_map_elements', 10, 2 );

/**
 * Set user location map icons.
 *
 * This happens during ajax results.
 *
 * The user location is usaully set using the get_map_args() function,
 *
 * but the function will happens on page load and not always during ajax.
 *
 * @param  [type] $json_data [description].
 *
 * @param  [type] $gmw       [description].
 *
 * @return [type]            [description].
 */
function gmw_ps_modify_user_location_map_icon( $json_data, $gmw ) {

	$icons_data = gmw_get_icons();
	$prefix     = GMW()->addons[ $gmw['component'] ]['prefix'];

	// Set user marker icon url.
	if ( isset( $gmw['map_markers']['user_marker'] ) ) {
		$json_data['user_location']['map_icon'] = $icons_data[ $prefix . '_map_icons' ]['url'] . $gmw['map_markers']['user_marker'];
	} else {
		$json_data['user_location']['map_icon'] = GMW()->default_icons['user_location_icon_url'];
	}

	// user marker size.
	if ( 'google_maps' !== GMW()->maps_provider || ( 'google_maps' === GMW()->maps_provider && ! empty( $gmw['map_markers']['user_icon_width'] ) && ! empty( $gmw['map_markers']['user_icon_height'] ) ) ) {
		$icon_width  = ! empty( $gmw['map_markers']['user_icon_width'] ) ? $gmw['map_markers']['user_icon_width'] : GMW()->default_icons['user_location_icon_size'][0];
		$icon_height = ! empty( $gmw['map_markers']['user_icon_height'] ) ? $gmw['map_markers']['user_icon_height'] : GMW()->default_icons['user_location_icon_size'][1];

		$json_data['user_location']['icon_size'] = array( $icon_width, $icon_height );
	}

	return $json_data;
}
add_filter( 'gmw_ajaxfms_ajax_form_json_data', 'gmw_ps_modify_user_location_map_icon', 20, 2 );

/**
 * Modify address fields in search results
 *
 * @param  [type] $address  [description].
 *
 * @param  [type] $location [description].
 *
 * @param  [type] $gmw      [description].
 *
 * @return [type]           [description]
 */
function gmw_ps_modify_results_address_fields( $address, $location, $gmw ) {

	// leave it the same if undefined.
	if ( empty( $gmw['search_results']['address_fields'] ) || ! is_array( $gmw['search_results']['address_fields'] ) ) {
		return $address;

		// hide address if disabled.
	} elseif ( in_array( 'disabled', $gmw['search_results']['address_fields'], true ) ) {

		return false;

		// full address.
	} elseif ( in_array( 'address', $gmw['search_results']['address_fields'], true ) ) {
		return ! empty( $location->formatted_address ) ? $location->formatted_address : $location->address;
	} else {

		$output = '';

		foreach ( $gmw['search_results']['address_fields'] as $field ) {
			if ( ! empty( $location->$field ) ) {
				$output .= $location->$field . ' ';
			}
		}

		return $output;
	}
}
add_filter( 'gmw_location_address', 'gmw_ps_modify_results_address_fields', 10, 3 );

/**
 * Modify results found message
 *
 * @param  [type] $args [description].
 *
 * @param  [type] $gmw  [description].
 *
 * @return [type]       [description]
 */
function gmw_ps_modify_results_found_message( $args, $gmw ) {

	if ( ! empty( $gmw['search_results']['results_found_message'] ) ) {

		$message = $gmw['search_results']['results_found_message'];

		$args['count_message']    = $message['count_message'];
		$args['location_message'] = isset( $message['location_message'] ) ? $message['location_message'] : '';
	}

	return $args;
}
add_filter( 'gmw_results_found_message', 'gmw_ps_modify_results_found_message', 50, 2 );

/**
 * Enqueue info-window stylesheets
 *
 * @param  [type] $map_args [description].
 *
 * @param  [type] $gmw      [description].
 *
 * @return [type]           [description]
 */
function gmw_ps_enqueue_iw_styles( $map_args, $gmw = false ) {

	// make sure we are in GEO my WP search results.
	if ( empty( $gmw ) || ! is_array( $gmw ) ) {
		return $map_args;
	}

	// We also don't need this for global maps
	// since it has built in info-windows.
	if ( isset( $gmw['addon'] ) && ( 'global_maps' === $gmw['addon'] || 'ajax_forms' === $gmw['addon'] ) ) {
		return $map_args;
	}

	if ( ! isset( $gmw['info_window'] ) ) {
		return $map_args;
	}

	if ( '' === $gmw['info_window']['ajax_enabled'] ) {
		return $map_args;
	}

	$iw_type  = $gmw['info_window']['iw_type'];
	$template = $gmw['info_window']['template'][ $iw_type ];

	// get info-window stylesheet.
	$template = gmw_get_info_window_template( $gmw['component'], $iw_type, $template, 'premium_settings' );

	if ( ! wp_style_is( $template['stylesheet_handle'], 'enqueued' ) ) {
		wp_enqueue_style( $template['stylesheet_handle'], $template['stylesheet_uri'] );
	}

	return $map_args;
}
add_action( 'gmw_map_element', 'gmw_ps_enqueue_iw_styles', 10, 2 );

/**
 * Get the orderby value on page load and form submission
 *
 * @param  [type] $form [description].
 *
 * @return [type]       [description]
 */
function gmw_ps_get_orderby_value( $form ) {

	$value = '';

	// return the orderby value from URL if exists.
	if ( isset( $form['form_values']['orderby'] ) ) {
		return $form['form_values']['orderby'];
	}

	// when in page load, get the orderby from the page load form settings.
	if ( $form['page_load_action'] ) {
		return isset( $form['page_load_results']['orderby'] ) ? $form['page_load_results']['orderby'] : '';
	}

	// if form submitted without an orderby value in URL
	// look for the value in form_submittion tab.
	if ( $form['submitted'] && isset( $form['form_submission']['orderby'] ) ) {
		$value = $form['form_submission']['orderby'];
	}

	return $value;
}

/**
 * Query Groups or Members Types
 *
 * @param  array  $gmw      gmw form.
 *
 * @param  string $object   member || group.
 *
 * @param  string $name_tag [description].
 *
 * @return [type]           [description]
 */
function gmw_ps_bp_get_object_types_query( $gmw, $object, $name_tag = '' ) {

	$output = array(
		'usage' => false,
		'types' => false,
	);

	if ( '' === $name_tag ) {
		$name_tag = 'bp_' . $object . '_types';
	}

	// filter type on page load.
	if ( $gmw['page_load_action'] && isset( $gmw['page_load_results'][ 'include_exclude_' . $object . '_types' ]['usage'] ) && 'disabled' !== $gmw['page_load_results'][ 'include_exclude_' . $object . '_types' ]['usage'] ) {

		$settings = $gmw['page_load_results'][ 'include_exclude_' . $object . '_types' ];

		if ( empty( $settings['usage'] ) || ! $settings[ $object . '_types' ] ) {

			return $output;

		} else {

			if ( 'include' === $settings['usage'] ) {

				$output['usage'] = $object . '_type__in';
				$output['types'] = $settings[ $object . '_types' ];

			} else {
				$output['usage'] = $object . '_type__not_in';
				$output['types'] = $settings[ $object . '_types' ];
			}
		}

		return $output;
	}

	// filter group on form submission.
	if ( ! $gmw['submitted'] || 'disabled' === $gmw['search_form'][ $object . '_types_filter' ]['usage'] ) {
		return $output;
	}

	$settings = $gmw['search_form'][ $object . '_types_filter' ];

	/**
	 * If set to pre-defined or no group types were selected in formm, then there are 2 scenarios:
	 *
	 * Either show only selected groups, when specifci groups
	 *
	 * are selected for the filter. Or show all groups types
	 *
	 * if nothing was selected.
	 */
	if ( 'pre_defined' === $settings['usage'] || empty( $gmw['form_values'][ $name_tag ] ) || ! array_filter( $gmw['form_values'][ $name_tag ] ) ) {

		$settings = $gmw['search_form'][ $object . '_types_filter' ];

		// if no types were selected in form editor we dont use the filter.
		if ( ! $settings[ $object . '_types' ] ) {

			return $output;

			// otherwise, filter all selected types.
		} else {

			$output['usage'] = $object . '_type__in';
			$output['types'] = $settings[ $object . '_types' ];
		}

		// otherwise, filter group types submitted in the form filter.
	} elseif ( 'pre_defined' !== $settings['usage'] ) {

		$output['usage'] = $object . '_type__in';
		$output['types'] = $gmw['form_values'][ $name_tag ];
	}

	return $output;
}

/**
Function gmw_ps_bp_object_types_query( $gmw, $object, $name_tag = '' ) {

	If ( '' == $name_tag ) {
		$name_tag = 'bp_' . $object . '_types';
	}

	// filter type on page load
	if ( $gmw['page_load_action'] && 'disabled' != $gmw['page_load_results'][ 'include_exclude_' . $object . '_types' ]['usage'] ) {

		$settings = $gmw['page_load_results'][ 'include_exclude_' . $object . '_types' ];

		if ( empty( $settings['usage'] ) || ! $settings[ $object . '_types' ] ) {

			return $gmw;

		} else {

			if ( 'include' == $settings['usage'] ) {

				$gmw['query_args'][ $object . '_type__in' ] = $settings[ $object . '_types' ];

			} else {

				$gmw['query_args'][ $object . '_type__not_in' ] = $settings[ $object . '_types' ];
			}
		}

		return $gmw;
	}

	// filter group on form submission.
	if ( ! $gmw['submitted'] || 'disabled' == $gmw['search_form'][ $object . '_types_filter' ]['usage'] ) {
		return $gmw;
	}

	$settings = $gmw['search_form'][ $object . '_types_filter' ];

	/**
	 * if set to pre-defined or no group types were selected in formm, then there are 2 scenarios:
	 *
	 * Either show only selected groups, when specifci groups
	 *
	 * are selected for the filter. Or show all groups types
	 *
	 * if nothing was selected.
	 *//*
	if ( 'pre_defined' == $settings['usage'] || empty( $gmw['form_values'][ $name_tag ] ) || ! array_filter( $gmw['form_values'][ $name_tag ] ) ) {

		$settings = $gmw['search_form'][ $object . '_types_filter' ];

		// if no types were selected in form editor we dont use the filter.
		if ( ! $settings[ $object . '_types' ] ) {

			return $gmw;

			// otherwise, filter all selected types.
		} else {

			$gmw['query_args'][ $object . '_type__in' ] = $settings[ $object . '_types' ];
		}

		// otherwise, filter group types submitted in the form filter.
	} elseif ( 'pre_defined' != $settings['usage'] ) {

		$gmw['query_args'][ $object . '_type__in' ] = $gmw['form_values'][ $name_tag ];
	}

	return $gmw;
}*/
