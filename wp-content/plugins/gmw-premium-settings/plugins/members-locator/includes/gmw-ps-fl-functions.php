<?php
/**
 * GMW Premium Settings - Members Locator functions.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Location tab options
 *
 * @param array  $args location form arguments.
 *
 * @param string $object_type object type.
 *
 * @return array       modified args.
 */
function gmw_ps_fl_location_form_args( $args, $object_type ) {

	if ( ! empty( $args['slug'] ) && 'members_locator' === $args['slug'] ) {
		$args['exclude_fields_groups'] = gmw_get_option( 'members_locator', 'location_form_exclude_fields_groups', array() );
		$args['exclude_fields']        = gmw_get_option( 'members_locator', 'location_form_exclude_fields', array() );
		$args['form_template']         = gmw_get_option( 'members_locator', 'location_form_template', 'location-form-tabs-top' );
	}

	return $args;
}
// add_filter( 'gmw_member_location_form_args', 'gmw_ps_fl_location_form_args', 20 );
add_filter( 'gmw_location_form_args', 'gmw_ps_fl_location_form_args', 20, 3 );

/**
 * Modify members query
 *
 * @param  array $query_args search query args.
 * @param  array $gmw        gmw form.
 *
 * @return array             modified query args.
 */
function gmw_fl_filter_members_query( $query_args, $gmw ) {

	// keywords.
	if ( isset( $gmw['form_values']['keywords'] ) && '' !== $gmw['form_values']['keywords'] ) {
		$query_args['search_terms'] = $gmw['form_values']['keywords'];
	}

	// Order by.
	$value = gmw_ps_get_orderby_value( $gmw );

	// abort if no value found.
	if ( '' !== $value ) {
		$query_args['type'] = str_replace( ' ', '', $value );
	}

	return $query_args;
}
add_filter( 'gmw_fl_search_query_args', 'gmw_fl_filter_members_query', 20, 2 );

/**
 * Filter Members query based on BP Groups.
 *
 * @param  array $query_args search query args.
 *
 * @param  array $form        gmw form.
 *
 * @return new query args.
 */
function gmw_fl_filter_bp_groups_member_query( $query_args, $form ) {

	/**
	 * If groups passed via URL
	 */
	if ( isset( $form['form_values']['bp_groups'] ) && array_filter( $form['form_values']['bp_groups'] ) ) {

		$groups = $form['form_values']['bp_groups'];

		/**
		 * When no groups exist in URL, then the options are either
		 *
		 * Showing all groups or the groups selected in the form settings.
		 *
		 * When this is the case we get the groups ID from the form settings.
		 */
	} elseif ( isset( $form['search_form']['bp_groups']['groups'] ) && array_filter( $form['search_form']['bp_groups']['groups'] ) ) {

		$groups = $form['search_form']['bp_groups']['groups'];

		// otherwise, no filtering needs to be done.
	} else {
		return $query_args;
	}

	// sanitize groups ID.
	$groups    = array_map( 'absint', $groups );
	$groups_id = implode( ',', $groups );

	global $wpdb;

	// get users ID belog to the selected groups from database.
	$users_id = $wpdb->get_col(
		"
        SELECT DISTINCT user_id 
        FROM {$wpdb->prefix}bp_groups_members
        WHERE group_id IN ( {$groups_id} )
        AND `is_confirmed` = 1"
	); // WPCS: unprepared sql ok, db call ok, cache ok.

	/**
	 * If no users found in groups, generate no results query.
	 *
	 * Otherwise, check if include argument already contains users id.
	 *
	 * If so, we need to merge it with users ID belong to the groups
	 *
	 * and keep only matching users.
	 */
	if ( empty( $users_id ) ) {

		$query_args['include'] = -0;

	} elseif ( ! empty( $query_args['include'] ) ) {

		$query_args['include'] = array_intersect( $query_args['include'], $users_id );

		if ( empty( $query_args['include'] ) ) {
			$query_args['include'] = -0;
		}
	} else {
		$query_args['include'] = $users_id;
	}

	return $query_args;
}
add_filter( 'gmw_fl_search_query_args', 'gmw_fl_filter_bp_groups_member_query', 55, 2 );

/**
 * Set additional settings in gmw WP_Query cache.
 *
 * @param  array $query_args search query args.
 *
 * @param  array $gmw        gmw form.
 *
 * @return new query args.
 */
function gmw_ps_fl_set_gmw_args( $query_args, $gmw ) {

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
 * Modify members map icon
 *
 * @param  array  $args    map icon args.
 * @param  object $member  member object.
 * @param  array  $gmw     gmw form.
 *
 * @return array           new args.
 */
function gmw_ps_modify_member_map_icon( $args, $member, $gmw ) {

	$map_icon = '_default.png';

	// set default map icon usage if not exist.
	if ( ! isset( $gmw['map_markers']['usage'] ) ) {
		$gmw['map_markers']['usage'] = 'global';
	}

	$usage       = isset( $gmw['map_markers']['usage'] ) ? $gmw['map_markers']['usage'] : 'global';
	$global_icon = isset( $gmw['map_markers']['default_marker'] ) ? $gmw['map_markers']['default_marker'] : '_default.png';

	// if same global map icon.
	if ( 'global' === $usage ) {

		$map_icon = $global_icon;

		// per member map icon.
	} elseif ( 'per_member' === $usage && isset( $member->map_icon ) ) {

		$map_icon = $member->map_icon;

		// avatar map icon.
	} elseif ( 'avatar' === $usage ) {

		$avatar = bp_core_fetch_avatar(
			array(
				'item_id' => $member->ID,
				'type'    => 'thumb',
				'width'   => 50,
				'height'  => 50,
				'html'    => false,
			)
		);

		$map_icon = ( $avatar ) ? $avatar : GMW_PS_URL . '/friends/assets/map-icons/_no_avatar.png';

		// oterwise, default map icon.
	} else {
		$map_icon = '_default.png';
	}

	// generate the map icon.
	if ( 'avatar' !== $usage ) {

		if ( '_default.png' === $map_icon ) {

			$map_icon = 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=' . $member->location_count . '|FF776B|000000';

		} else {

			$icons    = gmw_get_icons();
			$map_icon = $icons['fl_map_icons']['url'] . $map_icon;
		}
	}

	$args['map_icon'] = $map_icon;

	return $args;
}
add_action( 'gmw_fl_form_map_location_args', 'gmw_ps_modify_member_map_icon', 20, 3 );

/**
 * Get custom map icons via the search query.
 *
 * This function set icons for "global" and "per member".
 *
 * everything is done during WP_Query.
 *
 * @param  array $gmw     gmw form.
 *
 * @return SQL query || false.
 */
function gmw_ps_fl_get_map_icons_via_query( $gmw ) {

	$usage = ! empty( $gmw['map_markers']['usage'] ) ? $gmw['map_markers']['usage'] : 'global';

	// abort if not the right usage.
	if ( ! in_array( $usage, array( 'global', 'per_member' ), true ) ) {
		return false;
	}

	global $wpdb;

	// get icons url.
	$icons     = gmw_get_icons();
	$icons_url = $icons['fl_map_icons']['url'];

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

	// if per post, get the icon from locations table.
	if ( 'per_member' === $usage ) {

		return $wpdb->prepare( "IF ( gmw_locations.map_icon IS NOT NULL AND gmw_locations.map_icon != '_default.png', CONCAT( %s, gmw_locations.map_icon ), %s ) as map_icon", $icons_url, $default_icon );

	}

	return false;
}

/**
 * Generate custom map icons via members loop.
 *
 * So far it supports avtar as map icon.
 *
 * @param  string $map_icon URL to map icon.
 * @param  object $member   member object.
 * @param  array  $gmw       gmw form.
 *
 * @return new map icon URL.
 */
function gmw_ps_fl_get_map_icon_via_loop( $map_icon, $member, $gmw ) {

	$usage = $gmw['map_markers']['usage'];

	// abort if not set to featured image or categories.
	if ( 'avatar' === $gmw['map_markers']['usage'] ) {

		$member_id = isset( $member->id ) ? $member->id : $member->object_id;

		$map_icon = bp_core_fetch_avatar(
			array(
				'item_id' => $member_id,
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

/**
 * Disable Location tab for displayed users
 *
 * @param  array $args location tab args.
 *
 * @return new args.
 */
function gmw_ps_disable_displayed_user_location_tab( $args ) {

	$elements = gmw_get_option( 'members_locator', 'displayed_member_location_tab_elements', array() );

	if ( empty( $elements ) ) {

		if ( ! bp_is_my_profile() ) {
			$args = array();
		}
	} else {
		// run the filter only when tab is active.
		add_filter( 'gmw_fl_user_location_tab_content', 'gmw_ps_displayed_user_location_tab', 10, 3 );
	}

	return $args;
}
add_filter( 'gmw_fl_setup_nav', 'gmw_ps_disable_displayed_user_location_tab', 20 );

/**
 * Modify the elements and address field of the Location tab for the displayed member
 *
 * @since 3.0
 *
 * @param  shortcode $content tab content.
 *
 * @return modified shortcode.
 */
function gmw_ps_displayed_user_location_tab( $content ) {

	// make sure Single Location add-on is enabled.
	if ( ! gmw_is_addon_active( 'single_location' ) ) {
		return $content;
	}

	$elements = gmw_get_option( 'members_locator', 'displayed_member_location_tab_elements', false );

	// Abort if no element selected for the tab, which means tab is enabled.
	if ( empty( $elements ) ) {
		return;
	}

	$elements       = implode( ',', $elements );
	$address_fields = gmw_get_option( 'members_locator', 'displayed_member_location_tab_address_fields', array( 'address' ) );
	$address_fields = ( in_array( 'address', $address_fields, true ) || 6 === count( $address_fields ) ) ? 'formatted_address' : implode( ',', $address_fields );

	// modify the shortcode.
	$content = '[gmw_bp_member_location elements="' . esc_attr( $elements ) . '" address_fields="' . esc_attr( $address_fields ) . '" map_height="300px" map_width="100%" user_map_icon="0" item_info_window="0"]';

	return $content;
}

/**
 * Modify the address of the activity address
 *
 * @param array   $activity_address activity address fields.
 *
 * @param array   $user_location    users location.
 *
 * @param integer $user_id          user ID.
 *
 * @return new address fields.
 */
function gmw_ps_member_activity_address_fields( $activity_address, $user_location, $user_id ) {

	// get address fields from options.
	$address_fields = gmw_get_option( 'members_locator', 'activity_update_address_fields' );

	// if address field is empty return no address.
	if ( ! is_array( $address_fields ) || empty( $address_fields ) ) {

		return false;

		// if address field "address" show formatted address.
	} elseif ( in_array( 'address', $address_fields, true ) ) {

		return ! empty( $user_location['formatted_address'] ) ? $user_location['formatted_address'] : $user_location['address'];

		// otherwise, return the different address fields.
	} else {

		$output = array();

		foreach ( $address_fields as $field ) {
			if ( ! empty( $user_location[ $field ] ) ) {
				$output[] = $user_location[ $field ];
			}
		}
		return implode( ' ', $output );
	}
}
add_filter( 'gmw_fl_activity_address_fields', 'gmw_ps_member_activity_address_fields', 10, 3 );
// add_filter( 'gmw_xf_address_activity_update','gmw_fl_activity_address_fields', 10, 2 );
