<?php
/**
 * GMW Premium Settings - Members Locator global maps functions.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Trigger premium functions found in themplate functions files.
add_filter( 'gmw_gmapsfl_search_query_args', 'gmw_ps_fl_set_gmw_args', 15, 2 );

/**
 * Query Members Types for global maps.
 *
 * @param  array $gmw gmw form.
 *
 * @return [type]      [description]
 */
function gmw_ps_gmapsfl_bp_member_types_query( $gmw ) {

	$output = gmw_ps_bp_get_object_types_query( $gmw, 'member', 'bpmt' );

	if ( ! empty( $output['usage'] ) ) {

		// need to enable advanced query.
		$gmw['query_args']['gmw_args']['bp_user_query_enabled'] = true;
		$gmw['query_args'][ $output['usage'] ]                  = $output['types'];
	}

	return $gmw;
}
add_filter( 'gmw_gmapsfl_form_before_members_query', 'gmw_ps_gmapsfl_bp_member_types_query', 15 );

/**
 * Query members keywords
 *
 * @param  array $clauses query clauses.
 * @param  array $gmw     gmw form.
 *
 * @return [type]          [description]
 */
function gmw_ps_gmapsfl_filter_members_keywords( $clauses, $gmw ) {

	// verify that keywords exists.
	if ( empty( $gmw['form_values']['keywords'] ) ) {
		return $clauses;
	}

	global $wpdb;
	// get keywords value from URL.
	$keywords = $gmw['form_values']['keywords'];

	// support for WordPress lower then V4.0.
	$like = method_exists( $wpdb, 'esc_like' ) ? $wpdb->esc_like( trim( $keywords ) ) : like_escape( trim( $keywords ) );
	$like = esc_sql( $like );
	$like = '%' . $like . '%';

	// search title.
	$clauses->query_where .= " AND ( {$wpdb->users}.user_login LIKE '{$like}' OR {$wpdb->users}.user_nicename LIKE '{$like}' OR {$wpdb->users}.display_name LIKE '{$like}' ) ";

	return $clauses;
}
add_filter( 'gmw_gmapsfl_members_query_clauses', 'gmw_ps_gmapsfl_filter_members_keywords', 50, 2 );

/**
 * Set custom map icon via search query.
 *
 * @param  array $clauses query clauses.
 *
 * @param  array $gmw     gmw form.
 *
 * @return [type]          [description]
 */
function gmw_ps_gmapsfl_get_map_icons_via_query( $clauses, $gmw ) {

	$icon_query = gmw_ps_fl_get_map_icons_via_query( $gmw );

	if ( ! empty( $icon_query ) ) {
		$clauses->query_fields .= ', ' . $icon_query;
	}

	return $clauses;
}
add_filter( 'gmw_gmapsfl_members_query_clauses', 'gmw_ps_gmapsfl_get_map_icons_via_query', 20, 2 );

/**
 * Generate custom map icons via members loop.
 *
 * For member avatar map icons.
 *
 * @param  object $query main query.
 *
 * @param  array  $gmw   gmw form.
 *
 * @return [type]        [description]
 */
function gmw_ps_fl_set_map_icons_via_loop( $query, $gmw ) {

	// abort if not set to featured image or categories.
	if ( 'avatar' !== $gmw['map_markers']['usage'] ) {
		return $query;
	}

	$temp    = array();
	$members = $query->results;

	foreach ( $members as $member ) {

		$member->map_icon = gmw_ps_fl_get_map_icon_via_loop( '', $member, $gmw );

		$temp[] = $member;
	}

	$query->results = $temp;

	return $query;
}
add_filter( 'gmw_gmapsfl_cached_members_query', 'gmw_ps_fl_set_map_icons_via_loop', 10, 2 );
add_filter( 'gmw_gmapsfl_members_query', 'gmw_ps_fl_set_map_icons_via_loop', 10, 2 );
