<?php
/**
 * GMW Premium Settings - Groups Locator loader.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Groups Locator premium features
 */
class GMW_PS_BP_Groups_Locator {

	/**
	 * [__construct description]
	 */
	public function __construct() {

		include_once 'includes/gmw-ps-groups-locator-functions.php';

		if ( IS_ADMIN ) {
			include 'includes/admin/class-gmw-ps-gl-admin-settings.php';
			include 'includes/admin/class-gmw-ps-gl-form-settings.php';
		} else {

			// load per group map icon if needed.
			if ( gmw_get_option( 'bp_groups_locator', 'per_group_map_icon', false ) !== false ) {
				add_filter( 'gmw_bp_group_location_form_tabs', 'gmw_ps_location_form_map_icons_tab', 10 );
				add_filter( 'gmw_bp_group_location_tabs_panels', 'gmw_ps_location_form_map_icons_panel', 10 );
			}
		}

		add_action( 'gmw_gl_ajax_info_window_init', array( $this, 'get_info_window_data' ), 20, 2 );

		// if Global Maps extension enabled, loads its features.
		if ( gmw_is_addon_active( 'global_maps' ) || gmw_is_addon_active( 'ajax_forms' ) ) {
			$this->extensions_features();
		}
	}

	/**
	 * Load AJAX info window
	 *
	 * @param  object $location location object.
	 *
	 * @param  array  $gmw      gmw form.
	 */
	public function get_info_window_data( $location, $gmw ) {
		include_once 'includes/gmw-ps-groups-locator-info-window-functions.php';
	}

	/**
	 * Include extensions functions
	 */
	public function extensions_features() {
		// include global maps functions.
		include_once 'includes/gmw-ps-groups-locator-extensions-functions.php';
	}
}
new GMW_PS_BP_Groups_Locator();
