<?php
/**
 * Premium Settings - Users Locator AJAX Info Window Generator.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Get the user data + location data from database.
$user = gmw_get_user_location_data( $location->location_id, true );

// Make sure that ID key is also user ID.
$user->ID          = $location->object_id;
$user->location_id = $location->location_id;
$user->distance    = $location->distance;
$user->units       = $location->units;

// Get location meta if needed and append it to the user.
if ( ! empty( $gmw['info_window']['location_meta'] ) ) {
	$user->location_meta = gmw_get_location_meta( $user->ID, $gmw['info_window']['location_meta'] );
}

// Modify form and member.
$user = apply_filters( 'gmw_ps_user_before_info_window', $user, $gmw );

$iw_type  = ! empty( $gmw['info_window']['iw_type'] ) ? $gmw['info_window']['iw_type'] : 'infobubble';
$template = $gmw['info_window']['template'][ $iw_type ];

// Include template.
$template_data = gmw_get_info_window_template( 'users_locator', $iw_type, $template, 'premium_settings' );

require $template_data['content_path'];

do_action( 'gmw_ps_after_user_info_window', $user, $gmw );
