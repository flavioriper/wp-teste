<?php
/**
 * Loader file
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Posts Locator add-ons features
 */
class GMW_PS_Posts_Locator {

	/**
	 * [__construct description]
	 */
	public function __construct() {

		$gmw_pt_options = gmw_get_options_group( 'post_types_settings' );

		// load per post map icon if needed.
		if ( ! empty( $gmw_pt_options['per_post_icons'] ) ) {
			add_filter( 'gmw_post_location_form_tabs', 'gmw_ps_location_form_map_icons_tab', 10 );
			add_filter( 'gmw_post_location_tabs_panels', 'gmw_ps_location_form_map_icons_panel', 10 );
		}

		// do stuff in admin.
		if ( IS_ADMIN ) {

			// load category icons if needed.
			if ( ! empty( $gmw_pt_options['post_types'] ) && ! empty( $gmw_pt_options['per_category_icons']['enabled'] ) ) {
				include_once 'includes/admin/class-gmw-ps-category-icons.php';
			}

			// admin settings.
			include 'includes/admin/class-gmw-ps-pt-admin-settings.php';
			include 'includes/admin/class-gmw-ps-pt-form-settings.php';

			// do more cool stuff in front-end.
		} else {
			// load template functions when GEO my WP form loads.
			add_action( 'gmw_pt_shortcode_start', array( $this, 'include_template_functions' ), 10 );
		}

		// Load info-window functions.
		add_action( 'gmw_pt_ajax_info_window_init', array( $this, 'info_window_init' ), 20, 2 );

		// if Global Maps extension enabled, loads its features.
		if ( gmw_is_addon_active( 'global_maps' ) ) {
			$this->global_map_features();
		}

		// if Ajax Forms extension enabled, loads its features.
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
		include_once 'includes/gmw-ps-pt-search-form-template-functions.php';
		include_once 'includes/gmw-ps-pt-search-results-template-functions.php';
	}

	/**
	 * Posts locator info-window loader.
	 *
	 * @param  object $location location object.
	 *
	 * @param  array  $gmw      gmw form.
	 */
	public function info_window_init( $location, $gmw ) {
		include_once 'includes/gmw-ps-pt-info-window-functions.php';
	}

	/**
	 * Include Global Maps functions
	 */
	public function global_map_features() {

		// include template functions when global maps loads.
		add_action( 'gmw_gmapspt_global_map_init', array( $this, 'include_template_functions' ), 10 );

		// include global maps functions.
		include_once 'includes/gmw-ps-posts-locator-extensions-functions.php';
	}

	/**
	 * Include Ajax Forms functions
	 */
	public function ajax_forms_features() {

		// include template functions when Ajax form loads.
		add_action( 'gmw_ajaxfmspt_form_init', array( $this, 'include_template_functions' ), 10 );

		// include global maps functions.
		include_once 'includes/gmw-ps-posts-locator-extensions-functions.php';
	}
}
new GMW_PS_Posts_Locator();
