<?php
/**
 * GMW Premium Settings - Users Locator loader.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Users Locator Premium Features
 */
class GMW_PS_Users_Locator {

	/**
	 * [__construct description]
	 */
	public function __construct() {

		include_once 'includes/gmw-ps-users-locator-functions.php';

		if ( IS_ADMIN ) {
			include 'includes/admin/class-gmw-ps-ul-admin-settings.php';
			include 'includes/admin/class-gmw-ps-ul-form-settings.php';
		}

		// generate per user map icon tab if needed.
		if ( gmw_get_option( 'users_locator', 'per_user_map_icon', false ) !== false ) {
			add_filter( 'gmw_user_location_form_tabs', 'gmw_ps_location_form_map_icons_tab', 10 );
			add_filter( 'gmw_user_location_tabs_panels', 'gmw_ps_location_form_map_icons_panel', 10 );
		}

		add_action( 'gmw_ul_ajax_info_window_init', array( $this, 'info_window_init' ), 20, 2 );

		// if Global Maps extension enabled, loads its features.
		if ( gmw_is_addon_active( 'global_maps' ) || gmw_is_addon_active( 'ajax_forms' ) ) {
			$this->extensions_features();
		}
	}

	/**
	 * Load AJAX info window
	 *
	 * @param  object $location the location object.
	 *
	 * @param  array  $gmw      gmw form.
	 */
	public function info_window_init( $location, $gmw ) {
		include_once 'includes/gmw-ps-users-locator-info-window-functions.php';
	}

	/**
	 * Include Global Maps functions
	 */
	public function extensions_features() {
		// include global maps functions.
		include_once 'includes/gmw-ps-users-locator-extensions-functions.php';
	}
}
new GMW_PS_Users_Locator();
