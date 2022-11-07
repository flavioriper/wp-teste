<?php
/**
 * GMW Premium Settings - Users Locator functions.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Modify the users query.
 *
 * Filter keywords and order by value.
 *
 * @param  array $query_args search query args.
 *
 * @param  array $gmw        gmw form.
 *
 * @return [type]             [description]
 */
function gmw_ps_filter_users_query( $query_args, $gmw ) {

	// keywords.
	if ( isset( $gmw['form_values']['keywords'] ) && '' !== $gmw['form_values']['keywords'] ) {

		// adding * to performe wild search.
		$query_args['search']         = '*' . $gmw['form_values']['keywords'] . '*';
		$query_args['search_columns'] = array( 'user_login', 'user_nicename', 'display_name' );
	}

	// orderby.
	$orderby = gmw_ps_get_orderby_value( $gmw );

	// abort if no value found.
	if ( '' !== $orderby && 'distance' !== $orderby ) {
		$query_args['orderby'] = $orderby;
	}

	return $query_args;
}
add_filter( 'gmw_ul_search_query_args', 'gmw_ps_filter_users_query', 50, 2 );

/**
 * Set additional arguments in gmw WP_Query cache.
 *
 * @param  array $query_args search query args.
 *
 * @param  array $gmw        gmw form.
 *
 * @return [type]      [description]
 *
 * @since 2.0
 */
function gmw_ps_ul_set_gmw_args( $query_args, $gmw ) {

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
	$query_args['gmw_args']['map_icons']['usage']          = ! empty( $gmw['map_markers']['usage'] ) ? $gmw['map_markers']['usage'] : 'global';
	$query_args['gmw_args']['map_icons']['default_marker'] = ! empty( $gmw['map_markers']['default_marker'] ) ? $gmw['map_markers']['default_marker'] : '_default.png';

	return $query_args;
}
add_filter( 'gmw_ul_search_query_args', 'gmw_ps_ul_set_gmw_args', 10, 2 );

/**
 * Use user avatar as map icon.
 *
 * @param  array  $args arguments.
 *
 * @param  object $user the user object.
 *
 * @param  array  $gmw  gmw form.
 *
 * @return [type]                   [description]
 */
function gmw_ps_modify_user_map_icon( $args, $user, $gmw ) {

	$args['map_icon'] = gmw_ps_ul_get_map_icon_via_loop( $args['map_icon'], $user, $gmw );

	return $args;
}
add_action( 'gmw_ul_form_map_location_args', 'gmw_ps_modify_user_map_icon', 20, 3 );


/**
 * Set custom users map icons.
 *
 * This function set icons for "global" and "per user" usage.
 *
 * Note that this function run using a filter inside the internal cache of GEO my WP.
 *
 * We use the gmw_ps_ul_set_gmw_args() function above to clear the cache when the
 *
 * default marker and usage change in the form settings.
 *
 * everything is done during WP_Query.
 *
 * @param  array $clauses query clauses.
 *
 * @param  array $gmw     gmw form.
 *
 * @return [type]          [description]
 *
 * @since 2.0
 */
function gmw_ps_ul_set_map_icons_via_query( $clauses, $gmw ) {

	$usage = ! empty( $gmw['map_markers']['usage'] ) ? $gmw['map_markers']['usage'] : 'global';

	// abort if not the right usage.
	if ( ! in_array( $usage, array( 'global', 'per_user' ), true ) ) {
		return $clauses;
	}

	global $wpdb;

	// get icons url.
	$icons     = gmw_get_icons();
	$icons_url = $icons['ul_map_icons']['url'];

	// get default marker. If no icon provided or using the _default.png,
	// we than pass blank value, to use Google's default red marker.
	if ( ! empty( $gmw['map_markers']['default_marker'] ) && '_default.png' !== $gmw['map_markers']['default_marker'] ) {
		$default_icon = $icons_url . $gmw['map_markers']['default_marker'];
	} else {
		$default_icon = GMW()->default_icons['location_icon_url'];
	}

	// if global icon.
	if ( 'global' === $usage ) {

		$clauses->query_fields .= $wpdb->prepare( ', %s as map_icon', $default_icon );

		return $clauses;
	}

	// if per post, get the icon from locations table.
	if ( 'per_user' === $usage ) {

		$clauses->query_fields .= $wpdb->prepare( ", IF ( gmw_locations.map_icon IS NOT NULL AND gmw_locations.map_icon != '_default.png', CONCAT( %s, gmw_locations.map_icon ), %s ) as map_icon", $icons_url, $default_icon );

		return $clauses;
	}

	return $clauses;
}
add_filter( 'gmw_ul_users_query_clauses', 'gmw_ps_ul_set_map_icons_via_query', 12, 2 );

/**
 * Generate custom map icons via users loop.
 *
 * For user avatar map icon.
 *
 * @since 2.0
 *
 * @param  string $map_icon map icon.
 *
 * @param  object $user     the user object.
 *
 * @param  array  $gmw      gmw form.
 *
 * @return [type]           [description]
 */
function gmw_ps_ul_get_map_icon_via_loop( $map_icon, $user, $gmw ) {

	// abort if not set to user avatar.
	if ( ! empty( $gmw['map_markers']['usage'] ) && 'avatar' === $gmw['map_markers']['usage'] ) {

		$user_id = isset( $user->ID ) ? $user->ID : $user->object_id;

		$map_icon = get_avatar_url(
			$user_id,
			array(
				'height' => 30,
				'width'  => 30,
			)
		);

		if ( empty( $map_icon ) ) {
			$map_icon = GMW_PS_URL . '/assets/map-icons/_no_image.png';
		}
	}

	return $map_icon;
}
