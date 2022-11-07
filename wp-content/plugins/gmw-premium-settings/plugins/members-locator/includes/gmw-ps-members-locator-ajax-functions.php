<?php
/**
 * GMW Premium Settings - Members Locator AJAX Forms functions.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Trigger premium functions found in themplate functions files.
add_filter( 'gmw_ajaxfmsfl_search_query_args', 'gmw_fl_filter_members_query', 20, 2 );
add_filter( 'gmw_ajaxfmsfl_search_query_args', 'gmw_fl_filter_bp_groups_member_query', 55, 2 );
add_filter( 'gmw_ajaxfmsfl_search_query_args', 'gmw_ps_fl_bp_member_types_query', 15, 2 );
add_filter( 'gmw_ajaxfmsfl_loop_object_map_icon', 'gmw_ps_fl_get_map_icon_via_loop', 15, 3 );
add_filter( 'gmw_ajaxfmsfl_search_query_args', 'gmw_ps_fl_set_gmw_args', 15, 2 );

/**
 * Set custom map icon via search query.
 *
 * @param  array $args arguments.
 *
 * @param  array $gmw  gmw form.
 *
 * @return [type]       [description]
 */
function gmw_ps_ajaxfmsfl_get_map_icons_via_query( $args, $gmw ) {

	$icon_query = gmw_ps_fl_get_map_icons_via_query( $gmw );

	if ( ! empty( $icon_query ) ) {
		$args['db_fields'] .= ', ' . $icon_query;
	}

	return $args;
}
add_filter( 'gmw_ajaxfmsfl_get_locations_query_args', 'gmw_ps_ajaxfmsfl_get_map_icons_via_query', 20, 2 );
