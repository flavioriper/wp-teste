<?php
/**
 * Nearby Posts shortcode.
 *
 * Filter and display nearby Posts Locator.
 *
 * @package gmw-nearby-locations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW Nearby Posts shortcode.
 *
 * @param array $atts shortcode attribubtes.
 *
 * @since 2.0
 *
 * @author Eyal Fitoussi
 */
function gmw_nearby_posts_shortcode( $atts = array() ) {

	if ( empty( $atts ) ) {
		$atts = array();
	}

	if ( ! class_exists( 'GMW_Nearby_Posts' ) ) {

		gmw_trigger_error( 'GMW_Nearby_Posts class is missing.' );

		return;
	}

	ob_start();

	$nearby_posts = new GMW_Nearby_Posts( $atts );

	$nearby_posts->display();

	$output_string = ob_get_contents();

	ob_end_clean();

	return $output_string;
}
add_shortcode( 'gmw_nearby_posts', 'gmw_nearby_posts_shortcode' );
