<?php
/**
 * GMW Premium Settings - Members Locator loader.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Members Locator add-ons features
 */
class GMW_PS_Members_Locator {

	/**
	 * [__construct description]
	 */
	public function __construct() {

		include 'includes/gmw-ps-fl-functions.php';

		if ( IS_ADMIN ) {

			include_once 'includes/admin/class-gmw-ps-fl-admin-settings.php';
			include_once 'includes/admin/class-gmw-ps-fl-form-settings.php';

		} else {

			// load per member map icon if needed.
			if ( gmw_get_option( 'members_locator', 'per_member_map_icon', false ) !== false ) {
				add_filter( 'gmw_member_location_form_tabs', 'gmw_ps_location_form_map_icons_tab', 10 );
				add_filter( 'gmw_member_location_tabs_panels', 'gmw_ps_location_form_map_icons_panel', 10 );
			}

			add_action( 'gmw_fl_shortcode_start', array( $this, 'include_template_functions' ), 10 );
		}

		add_action( 'gmw_fl_ajax_info_window_init', array( $this, 'get_info_window_data' ), 20, 2 );

		// if Global Maps extension enabled, loads its features.
		if ( gmw_is_addon_active( 'global_maps' ) ) {
			$this->global_map_features();
		}

		// if Global Maps extension enabled, loads its features.
		if ( gmw_is_addon_active( 'ajax_forms' ) ) {
			$this->ajax_forms_features();
		}
	}

	/**
	 * Include posts classes
	 *
	 * @param array $gmw gmw form.
	 */
	public function include_template_functions( $gmw ) {
		include_once 'includes/gmw-ps-fl-search-form-template-functions.php';
		include_once 'includes/gmw-ps-fl-search-results-template-functions.php';
	}

	/**
	 * Generate info-window content.
	 *
	 * @param  object $location location object.
	 *
	 * @param  array  $gmw      gmw form.
	 */
	public function get_info_window_data( $location, $gmw ) {
		include_once 'includes/gmw-ps-fl-info-window-functions.php';
	}

	/**
	 * Include Global Maps functions
	 */
	public function global_map_features() {

		// include template functions when global maps loads.
		add_action( 'gmw_gmapsfl_global_map_init', array( $this, 'include_template_functions' ), 10 );

		// include global maps functions.
		include_once 'includes/gmw-ps-members-global-maps-functions.php';
	}

	/**
	 * Include Global Maps functions
	 */
	public function ajax_forms_features() {

		// include template functions when global maps loads.
		add_action( 'gmw_ajaxfmsfl_form_init', array( $this, 'include_template_functions' ), 10 );

		// include global maps functions.
		include_once 'includes/gmw-ps-members-locator-ajax-functions.php';
	}
}
new GMW_PS_Members_Locator();
