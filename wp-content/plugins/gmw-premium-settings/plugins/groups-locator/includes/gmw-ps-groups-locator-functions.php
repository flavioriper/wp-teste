<?php
/**
 * GMW Premium Settings - Groups Locator functions.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Modify groups query.
 *
 * Filter keywords, Group types and orderby.
 *
 * @param  [type] $query_args [description].
 *
 * @param  [type] $gmw        [description].
 *
 * @return [type]             [description]
 */
function gmw_ps_filter_bp_groups_query( $query_args, $gmw ) {

	// Keywords.
	if ( ! empty( $gmw['form_values']['keywords'] ) ) {
		$query_args['search_terms'] = $gmw['form_values']['keywords'];
	}

	// Orderby.
	$orderby = gmw_ps_get_orderby_value( $gmw );

	// abort if no value found.
	if ( '' !== $orderby ) {
		$query_args['type'] = $orderby;
	}

	// Group Types.
	$output = gmw_ps_bp_get_object_types_query( $gmw, 'group', 'bpgt' );

	if ( ! empty( $output['usage'] ) ) {
		$query_args[ $output['usage'] ] = $output['types'];
	}

	return $query_args;
}
// remove original group types query made by the groups locator extension.
remove_filter( 'gmw_gl_search_query_args', 'gmw_gl_bp_group_types_query', 30, 2 );
// add premium settings group types filter and other filters.
add_filter( 'gmw_gl_search_query_args', 'gmw_ps_filter_bp_groups_query', 50, 2 );

/**
 * Modify groups map icon
 *
 * @param  [type] $args  [description].
 *
 * @param  [type] $group [description].
 *
 * @param  [type] $gmw   [description].
 *
 * @return [type]        [description]
 */
function gmw_ps_modify_group_map_icon( $args, $group, $gmw ) {

	$map_icon = '_default.png';

	// set default map icon usage if not exist.
	if ( ! isset( $gmw['map_markers']['usage'] ) ) {
		$usage                       = 'global';
		$gmw['map_markers']['usage'] = 'global';
	} else {
		$usage = $gmw['map_markers']['usage'];
	}

	$global_icon = isset( $gmw['map_markers']['default_marker'] ) ? $gmw['map_markers']['default_marker'] : '_default.png';

	// if same global map icon.
	if ( 'global' === $usage ) {

		$map_icon = $global_icon;

		// per group map icon.
	} elseif ( 'per_group' === $usage && isset( $group->map_icon ) ) {

		$map_icon = $group->map_icon;

		// avatar map icon.
	} elseif ( 'avatar' === $usage ) {

		$avatar = bp_core_fetch_avatar(
			array(
				'item_id' => $group->id,
				'object'  => 'group',
				'type'    => 'thumb',
				'width'   => 30,
				'height'  => 30,
				'html'    => false,
			)
		);

		$map_icon = array(
			'url'        => ( $avatar ) ? $avatar : GMW_PS_URL . '/friends/assets/ map-icons/_no_avatar.png',
			'scaledSize' => array(
				'height' => 30,
				'width'  => 30,
			),
		);

		// oterwise, default map icon.
	} else {
		$map_icon = '_default.png';
	}

	// generate the map icon.
	if ( 'avatar' !== $usage ) {

		if ( '_default.png' === $map_icon ) {
			$map_icon = 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=' . $group->location_count . '|FF776B|000000';
		} else {
			$icons    = gmw_get_icons();
			$map_icon = $icons['gl_map_icons']['url'] . $map_icon;
		}
	}

	$args['map_icon'] = $map_icon;

	return $args;
}
add_action( 'gmw_gl_form_map_location_args', 'gmw_ps_modify_group_map_icon', 20, 3 );

/**
 * Set additional arguments in gmw WP_Query cache.
 *
 * @param  object $query_args query args.
 *
 * @param  array  $gmw        gmw form.
 *
 * @return [type]      [description]
 *
 * @since 2.0
 */
function gmw_ps_gl_set_gmw_args( $query_args, $gmw ) {

	/**
	 * Set map icons settings in gmw query cache.
	 *
	 * The icons are being generated in the WP_Query,
	 *
	 * we need to make sure the query cache udpates when icons changes.
	 *
	 * @param  [type] $gmw [description]
	 * @return [type]      [description]
	 */
	$query_args['gmw_args']['map_icons']['usage']          = $gmw['map_markers']['usage'];
	$query_args['gmw_args']['map_icons']['default_marker'] = $gmw['map_markers']['default_marker'];

	return $query_args;
}

/**
 * Set custom group map icons via sarch query.
 *
 * This function set custom icons for "global" and "per group".
 *
 * everything is done during WP_Query.
 *
 * @param  [type] $gmw     [description].
 *
 * @return [type]          [description]
 *
 * @since 2.0
 */
function gmw_ps_gl_get_map_icons_via_query( $gmw ) {

	$usage = ! empty( $gmw['map_markers']['usage'] ) ? $gmw['map_markers']['usage'] : 'global';

	// abort if not the right usage.
	if ( ! in_array( $usage, array( 'global', 'per_group' ), true ) ) {
		return false;
	}

	global $wpdb;

	// get icons url.
	$icons     = gmw_get_icons();
	$icons_url = $icons['gl_map_icons']['url'];

	// get default marker. If no icon provided or using the _default.png,
	// we than pass blank value, to use Google's default red marker.
	if ( ! empty( $gmw['map_markers']['default_marker'] ) && '_default.png' !== $gmw['map_markers']['default_marker'] ) {
		$default_icon = $icons_url . $gmw['map_markers']['default_marker'];
	} else {
		$default_icon = GMW()->default_icons['location_icon_url'];
	}

	// if global icon.
	if ( 'global' === $usage ) {
		return $wpdb->prepare( '%s as map_icon', $default_icon );
	}

	// if per group, get the icon from locations table.
	if ( 'per_group' === $usage ) {

		return $wpdb->prepare( "IF ( gmw_locations.map_icon IS NOT NULL AND gmw_locations.map_icon != '_default.png', CONCAT( %s, gmw_locations.map_icon ), %s ) as map_icon", $icons_url, $default_icon );
	}

	return false;
}

/**
 * Generate custom map icons via groups loop.
 *
 * For group avatar map icons.
 *
 * @since 2.0
 *
 * @param  [type] $map_icon [description].
 *
 * @param  [type] $group    [description].
 *
 * @param  [type] $gmw      [description].
 *
 * @return [type]           [description]
 */
function gmw_ps_gl_get_map_icon_via_loop( $map_icon, $group, $gmw ) {

	$usage = $gmw['map_markers']['usage'];

	// abort if not set to featured image or categories.
	if ( 'avatar' === $gmw['map_markers']['usage'] ) {

		$group_id = isset( $group->id ) ? $group->id : $group->object_id;

		$map_icon = bp_core_fetch_avatar(
			array(
				'item_id' => $group_id,
				'object'  => 'group',
				'type'    => 'thumb',
				'width'   => 80,
				'height'  => 80,
				'html'    => false,
				'no_grav' => true,
			)
		);

		if ( empty( $map_icon ) ) {
			$map_icon = GMW_PS_URL . '/assets/map-icons/_no_image.png';
		}
	}

	return $map_icon;
}
