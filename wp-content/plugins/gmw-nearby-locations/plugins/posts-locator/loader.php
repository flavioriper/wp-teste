<?php
/**
 * GEO my WP Nearby Posts Locator.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once 'includes/class-gmw-nearby-posts.php';
require_once 'includes/gmw-nearby-posts-shortcode.php';

/**
 * Nearby Posts Widget init.
 */
function gmw_nearby_posts_widget_init() {
	include_once 'includes/class-gmw-nearby-posts-widget.php';
}
add_action( 'widgets_init', 'gmw_nearby_posts_widget_init' );
