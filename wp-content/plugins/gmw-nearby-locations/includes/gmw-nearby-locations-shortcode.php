<?php
/**
 * Nearby Locations shortcodes.
 *
 * @package gmw-nearby-locations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW Nearby Locations shortcode.
 *
 * @param array $atts shortcode attributes.
 *
 * @version 1.0
 *
 * @author Eyal Fitoussi
 */
function gmw_nearby_locations_shortcode( $atts = array() ) {

	if ( empty( $atts ) ) {
		$atts = array( 'object' => 'post' );
		// make sure we have object type. If not, we check in item type
		// to support previous version of the plugin.
	} elseif ( ! isset( $atts['object'] ) ) {

		if ( isset( $atts['item_type'] ) ) {

			gmw_trigger_error( '[gmw_nearby_locations] shortcode attribute item_type is deprecated since version 2.0. Use "object" instead.' );

			$atts['object'] = $atts['item_type'];

			// otherwise, set the object type as post by default.
		} elseif ( isset( $atts['object_type'] ) ) {

			$atts['object'] = $atts['object_type'];

			// otherwise, set the object type as post by default.
		} else {
			$atts['object'] = 'post';
		}
	}

	// remove s from the end of the object in case plural was provided.
	$atts['object'] = rtrim( $atts['object'], 's' );

	// make sure the class of the item exists
	// we check 2 scenarios where user might entered object type
	// as plural rather than singular.
	if ( class_exists( "GMW_Nearby_{$atts['object']}" ) ) {

		// if enterd object type as plural we need to make it singualr by removing
		// the 's' at the end.
		$atts['object']    = substr( $atts['object'], 0, -1 );
		$atts['item_type'] = $atts['object'];
		$class_name        = "GMW_Nearby_{$atts['object']}s";

	} elseif ( class_exists( "GMW_Nearby_{$atts['object']}s" ) ) {

		$class_name = "GMW_Nearby_{$atts['object']}s";

		// otherwise, can use the filter for custom class.
	} elseif ( ! class_exists( $class_name = apply_filters( 'gmw_nearby_locations_custom_class_name', '', $atts['object'], $atts ) ) ) {
		return;
	}

	$nearby_locations = new $class_name( $atts );

	ob_start();

	$nearby_locations->display();

	$output_string = ob_get_contents();

	ob_end_clean();

	return $output_string;
}
add_shortcode( 'gmw_nearby_locations', 'gmw_nearby_locations_shortcode' );
