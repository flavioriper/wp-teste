<?php
/**
 * GMW Premium Settings - Members Locator search form template functions.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add the bp member types filter before the xprofile fields filter in the search form.
 *
 * @param  array $gmw gmw form.
 */
function gmw_ps_fl_enable_bp_member_types_search_form_filter( $gmw ) {

	if ( 'members_locator' === $gmw['component'] ) {
		gmw_search_form_bp_member_types( $gmw );
	}
}
add_action( 'gmw_search_form_filters', 'gmw_ps_fl_enable_bp_member_types_search_form_filter', 20 );

/**
 * Append the bp groups filter before the xprofile fields filter in the search form.
 *
 * @param  array $gmw gmw form.
 */
function gmw_ps_fl_enable_bp_groups_search_form_filter( $gmw ) {

	if ( 'members_locator' === $gmw['slug'] || 'members_locator_ajax' === $gmw['slug'] ) {
		gmw_search_form_bp_groups_filter( $gmw );
	}
}
add_action( 'gmw_search_form_filters', 'gmw_ps_fl_enable_bp_groups_search_form_filter', 20 );
