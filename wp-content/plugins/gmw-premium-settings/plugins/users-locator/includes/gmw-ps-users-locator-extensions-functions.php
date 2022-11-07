<?php
/**
 * GMW Premium Settings - Users Locator extensions functions.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Set additional settings in gmw WP_Query cache.
add_filter( 'gmw_gmapsul_search_query_args', 'gmw_ps_ul_set_gmw_args', 10, 2 );
add_filter( 'gmw_ajaxfmsul_search_query_args', 'gmw_ps_ul_set_gmw_args', 10, 2 );

// Set custom map icons via loop.
add_filter( 'gmw_ajaxfmsul_loop_object_map_icon', 'gmw_ps_ul_get_map_icon_via_loop', 15, 3 );

/**
 * Query users keywords.
 *
 * @param  array $clauses query clauses.
 *
 * @param  array $gmw     gmw form.
 *
 * @return [type]          [description]
 *
 * @since 2.0
 */
function gmw_ps_gmapsul_filter_keywords( $clauses, $gmw ) {

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
add_filter( 'gmw_gmapsul_users_query_clauses', 'gmw_ps_gmapsul_filter_keywords', 50, 2 );
add_filter( 'gmw_ajaxfmsul_users_query_clauses', 'gmw_ps_gmapsul_filter_keywords', 50, 2 );

// Set custom map icons via search query.
// The function lives in gmw-ps-users-locator-functions.php.
add_filter( 'gmw_gmapsul_users_query_clauses', 'gmw_ps_ul_set_map_icons_via_query', 12, 2 );
add_filter( 'gmw_ajaxfmsul_users_query_clauses', 'gmw_ps_ul_set_map_icons_via_query', 12, 2 );

/**
 * Generate custom map icons via user loop.
 *
 * For member avatar map icons.
 *
 * @since 2.0
 *
 * @param  object $query main search query object.
 *
 * @param  array  $gmw   gmw form.
 *
 * @return [type]           [description]
 */
function gmw_ps_gmapsul_set_map_icons_via_loop( $query, $gmw ) {

	// abort if not set to featured image or categories.
	if ( ( ! empty( $gmw['map_markers']['usage'] ) && 'avatar' !== $gmw['map_markers']['usage'] ) || empty( $query->results ) ) {
		return $query;
	}

	$temp  = array();
	$users = $query->results;

	foreach ( $users as $user ) {

		$user->map_icon = gmw_ps_ul_get_map_icon_via_loop( '', $user, $gmw );

		$temp[] = $user;
	}

	$query->results = $temp;

	return $query;
}
add_filter( 'gmw_gmapsul_cached_users_query', 'gmw_ps_gmapsul_set_map_icons_via_loop', 10, 3 );
add_filter( 'gmw_gmapsul_users_query', 'gmw_ps_gmapsul_set_map_icons_via_loop', 10, 2 );
