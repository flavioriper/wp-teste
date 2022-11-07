<?php
/**
 * GMW Premium Settings - Posts Locator extensions functions.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// functions live in gmw-ps-pt-search-results-template-functions.php.
add_filter( 'gmw_ajaxfmspt_form_before_posts_query', 'gmw_disable_keywords_content_search', 10 );
add_filter( 'gmw_gmapspt_form_before_posts_query', 'gmw_disable_keywords_content_search', 10 );
add_filter( 'gmw_gmapspt_form_before_posts_query', 'gmw_ps_pt_query_custom_fields', 10 );
add_filter( 'gmw_ajaxfmspt_posts_query_clauses', 'gmw_ps_pt_set_map_icons_via_query', 10, 2 );
add_filter( 'gmw_gmapspt_posts_query_clauses', 'gmw_ps_pt_set_map_icons_via_query', 10, 2 );
add_filter( 'gmw_ajaxfmspt_form_before_posts_query', 'gmw_ps_pt_set_gmw_query_args', 10 );
add_filter( 'gmw_gmapspt_form_before_posts_query', 'gmw_ps_pt_set_gmw_query_args', 10 );
add_filter( 'gmw_ajaxfmspt_search_query_args', 'gmw_ps_query_pre_defined_taxonomies', 15, 2 );
add_filter( 'gmw_gmapspt_search_query_args', 'gmw_ps_query_pre_defined_taxonomies', 15, 2 );
add_filter( 'gmw_ajaxfmspt_loop_object_map_icon', 'gmw_ps_pt_get_map_icon_via_loop', 15, 3 );
add_filter( 'gmw_ajaxfmspt_form_before_posts_query', 'gmw_ps_pt_query_custom_fields', 10 );

/**
 * Generate custom map icons via posts loop for Posts Global Maps.
 *
 * For per cateroy and featured image icons.
 *
 * @param  object $query search query object.
 *
 * @param  array  $gmw   gmw form.
 *
 * @return [type]           [description]
 */
function gmw_ps_gmapspt_set_map_icons_via_loop( $query, $gmw ) {

	// abort if not set to featured image or categories.
	if ( 'per_category' !== $gmw['map_markers']['usage'] && 'image' !== $gmw['map_markers']['usage'] ) {
		return $query;
	}

	$temp  = array();
	$posts = $query->posts;

	foreach ( $posts as $post ) {

		$post->map_icon = gmw_ps_pt_get_map_icon_via_loop( '', $post, $gmw );

		$temp[] = $post;
	}

	$query->posts = $temp;

	return $query;
}
add_filter( 'gmw_gmapspt_cached_posts_query', 'gmw_ps_gmapspt_set_map_icons_via_loop', 10, 3 );
add_filter( 'gmw_gmapspt_posts_query', 'gmw_ps_gmapspt_set_map_icons_via_loop', 10, 3 );



