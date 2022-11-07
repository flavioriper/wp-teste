<?php
/**
 * GMW Premium Settings - Groups Locator functions.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// remove original group types query made by the groups locator extension.
remove_filter( 'gmw_ajaxfmsgl_search_query_args', 'gmw_gl_bp_group_types_query', 15, 2 );
// add group types query and keywords filters of premium settings.
add_filter( 'gmw_ajaxfmsgl_search_query_args', 'gmw_ps_filter_bp_groups_query', 15, 2 );

// remove original group types query from Global Maps.
remove_filter( 'gmw_gmapsgl_search_query_args', 'gmw_gl_bp_group_types_query', 15, 2 );
// add group types query and keywords filters of premium settings.
add_filter( 'gmw_gmapsgl_search_query_args', 'gmw_ps_filter_bp_groups_query', 15, 2 );

// Modify map icons via loop.
add_filter( 'gmw_ajaxfmsgl_loop_object_map_icon', 'gmw_ps_gl_get_map_icon_via_loop', 12, 3 );

// Set additional arguments in gmw WP_Query cache.
add_filter( 'gmw_gmapsgl_search_query_args', 'gmw_ps_gl_set_gmw_args', 10, 2 );
add_filter( 'gmw_ajaxfmsgl_search_query_args', 'gmw_ps_gl_set_gmw_args', 10, 2 );

/**
 * Set Ajax form custom map icon via search query.
 *
 * @param  [type] $args [description].
 *
 * @param  [type] $gmw  [description].
 *
 * @return [type]       [description]
 */
function gmw_ps_ajaxfmsgl_get_map_icons_via_query( $args, $gmw ) {

	$icon_query = gmw_ps_gl_get_map_icons_via_query( $gmw );

	if ( ! empty( $icon_query ) ) {
		$args['db_fields'] .= ', ' . $icon_query;
	}

	return $args;
}
add_filter( 'gmw_ajaxfmsgl_get_locations_query_args', 'gmw_ps_ajaxfmsgl_get_map_icons_via_query', 20, 2 );

/**
 * Set Global Maps custom map icon via search query.
 *
 * @param  [type] $clauses [description].
 *
 * @param  [type] $gmw     [description].
 *
 * @return [type]          [description]
 */
function gmw_ps_gmapsgl_get_map_icons_via_query( $clauses, $gmw ) {

	$icon_query = gmw_ps_gl_get_map_icons_via_query( $gmw );

	if ( ! empty( $icon_query ) ) {
		$clauses['fields'] .= ', ' . $icon_query;
	}

	return $clauses;
}
add_filter( 'gmw_gmapsgl_groups_query_clauses', 'gmw_ps_gmapsgl_get_map_icons_via_query', 20, 2 );

/**
 * Set Global Maps icons via loop.
 *
 * @param  array  $groups [description].
 *
 * @param  [type] $gmw    [description].
 *
 * @return [type]         [description]
 */
function gmw_ps_gmapsgl_set_map_icons_via_loop( $groups = array(), $gmw ) {

	// abort if not set to featured image or categories.
	if ( 'avatar' !== $gmw['map_markers']['usage'] ) {
		return $groups;
	}

	$temp = array();

	foreach ( $groups as $group ) {

		$group->map_icon = gmw_ps_gl_get_map_icon_via_loop( '', $group, $gmw );

		$temp[] = $group;
	}

	$groups = $temp;

	return $groups;
}
add_filter( 'gmw_gmapsgl_cached_groups_locations', 'gmw_ps_gmapsgl_set_map_icons_via_loop', 10, 2 );
add_filter( 'gmw_gmapsgl_groups_locations', 'gmw_ps_gmapsgl_set_map_icons_via_loop', 10, 2 );
