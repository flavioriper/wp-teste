<?php
/**
 * GMW Premium Settings - Posts Locator info-window functions.
 *
 * @package gmw-premium-settings.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ajax info window loader.
 */
global $post;

// get the post object.
$post = get_post( $location->object_id ); // WPCS: override global ok.

// get additional post location data.
$location_data = gmw_get_post_location( $location->location_id, true );

$fields = array(
	'lat',
	'lng',
	'latitude',
	'longitude',
	'street',
	'premise',
	'city',
	'region_name',
	'postcode',
	'country_code',
	'address',
	'formatted_address',
	'location_name',
	'featured_location',
);

$fields = apply_filters( 'gmw_ps_db_fields_pre_info_window', $fields, $post, $location, $gmw );

// append location to the post object.
foreach ( $fields as $field ) {

	if ( isset( $location_data->$field ) ) {
		$post->$field = $location_data->$field;
	}
}

// get location meta if needed and append it to the post.
if ( ! empty( $gmw['info_window']['location_meta'] ) ) {
	$post->location_meta = gmw_get_location_meta( $location->location_id, $gmw['info_window']['location_meta'] );
}

// append distance + units to the post.
$post->distance = $location->distance;
$post->units    = $location->units;

// filter post object.
$post = apply_filters( 'gmw_ps_post_before_info_window', $post, $gmw, $location ); // WPCS: override global ok.

$iw_type  = ! empty( $gmw['info_window']['iw_type'] ) ? $gmw['info_window']['iw_type'] : 'infobubble';
$template = $gmw['info_window']['template'][ $iw_type ];

// include template.
$template_data = gmw_get_info_window_template( 'posts_locator', $iw_type, $template, 'premium_settings' );

require $template_data['content_path'];

do_action( 'gmw_ps_after_post_info_window', $gmw, $post );
