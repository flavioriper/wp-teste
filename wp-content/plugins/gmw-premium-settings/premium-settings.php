<?php
/**
 * Plugin Name: GMW Add-on - Premium Settings
 * Plugin URI: http://www.geomywp.com
 * Description: Extend GEO my WP forms and other components with premium features.
 * Version: 2.4.4
 * Author: Eyal Fitoussi
 * Author URI: http://www.geomywp.com
 * Requires at least: 4.5
 * Tested up to: 5.0.3
 * GEO my WP: 3.0+
 * Text Domain: gmw-premium-settings
 * Domain Path: /languages/
 *
 * @package gmw-premium-settings
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// look for GMW add-on registration class.
if ( ! class_exists( 'GMW_Addon' ) ) {
	return;
}

/**
 * GMW_Premium_Settings class.
 */
class GMW_Premium_Settings_Addon extends GMW_Addon {

	/**
	 * Slug
	 *
	 * @var string
	 */
	public $slug = 'premium_settings';

	/**
	 * Name
	 *
	 * @var string
	 */
	public $name = 'Premium Settings';

	/**
	 * Prefix.
	 *
	 * @var string
	 */
	public $prefix = 'ps';

	/**
	 * Version
	 *
	 * @var string
	 */
	public $version = '2.4.4';

	/**
	 * License name
	 *
	 * @var string
	 */
	public $license_name = 'premium_settings';

	/**
	 * Item Name.
	 *
	 * @var string
	 */
	public $item_name = 'Premium Settings';

	/**
	 * Item ID.
	 *
	 * @var string
	 */
	public $item_id = 668;

	/**
	 * Author.
	 *
	 * @var string
	 */
	public $author = 'Eyal Fitoussi';

	/**
	 * Version.
	 *
	 * @var string
	 */
	public $gmw_min_version = '3.6.4';

	/**
	 * Textdomain.
	 *
	 * @var string
	 */
	public $textdomain = 'gmw-premium-settings';

	/**
	 * Full Path.
	 *
	 * @var string
	 */
	public $full_path = __FILE__;

	/**
	 * Description.
	 *
	 * @var string
	 */
	public $description = 'Extend GEO my WP forms and other components with premium features.';

	/**
	 * Add-on's page.
	 *
	 * @var string
	 */
	public $addon_page = 'https://geomywp.com/extensions/premium-settings/';

	/**
	 * Support Page.
	 *
	 * @var string
	 */
	public $support_page = 'https://geomywp.com/support/#gmw-premium-support';

	/**
	 * Docs page.
	 *
	 * @var string
	 */
	public $docs_page = 'http://docs.geomywp.com';

	/**
	 * Settings groups
	 *
	 * @return [type] [description]
	 */
	public function admin_settings_groups() {

		// Generate Members Locator settings tab if extension enabled.
		if ( gmw_is_addon_active( 'members_locator' ) ) {

			return array(
				'slug'     => 'members_locator',
				'label'    => __( 'Members Locator', 'gmw-premium-settings' ),
				'icon'     => 'buddypress',
				'priority' => 10,
			);
		}

		return false;
	}

	/**
	 * Form settings groups
	 *
	 * @return [type] [description]
	 */
	public function form_settings_groups() {

		return array(

			array(
				'slug'     => 'no_results',
				'label'    => __( 'No Results', 'gmw-premium-settings' ),
				'fields'   => array(),
				'priority' => 43,
			),
			array(
				'slug'     => 'map_markers',
				'label'    => __( 'Map Markers', 'gmw-premium-settings' ),
				'fields'   => array(),
				'priority' => 53,
			),
			array(
				'slug'     => 'info_window',
				'label'    => __( 'Marker Info-Window', 'gmw-premium-settings' ),
				'fields'   => array(),
				'priority' => 55,
			),
		);
	}

	/**
	 * Instance of Premium Settings
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Create new instance
	 *
	 * @return [type] [description]
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * [__construct description]
	 */
	public function __construct() {

		// load icons into GMW global.
		GMW()->icons = get_option( 'gmw_icons' );

		parent::__construct();
	}

	/**
	 * Pre init functions
	 */
	public function pre_init() {

		parent::pre_init();

		// include functions.
		include 'includes/gmw-ps-functions.php';

		// include in admin only.
		if ( IS_ADMIN ) {

			include GMW_PS_PATH . '/includes/admin/gmw-ps-admin-functions.php';
			include GMW_PS_PATH . '/includes/admin/class-gmw-ps-admin-settings.php';
			include GMW_PS_PATH . '/includes/admin/class-gmw-ps-form-settings-helper.php';
			include GMW_PS_PATH . '/includes/admin/class-gmw-ps-form-settings.php';

			// map script.
		} else {
			add_action( 'gmw_enqueue_map_scripts', array( $this, 'enqueue_map_scripts' ) );
		}

		// include template functions files only when needed.
		add_action( 'gmw_shortcode_start', array( $this, 'include_template_functions' ), 10 );

		// enable Global Maps features when enabled.
		if ( gmw_is_addon_active( 'global_maps' ) ) {
			$this->global_maps_features();
		}

		// Enable Ajax Forms features when enabled.
		if ( gmw_is_addon_active( 'ajax_forms' ) ) {
			$this->ajax_forms_features();
		}

		// load posts locator features.
		if ( gmw_is_addon_active( 'posts_locator' ) ) {
			include GMW_PS_PATH . '/plugins/posts-locator/loader.php';
		}

		// load members locator features.
		if ( gmw_is_addon_active( 'users_locator' ) ) {
			include GMW_PS_PATH . '/plugins/users-locator/loader.php';
		}

		// load members locator features.
		if ( gmw_is_addon_active( 'members_locator' ) ) {
			include GMW_PS_PATH . '/plugins/members-locator/loader.php';
		}

		// load members locator features.
		if ( gmw_is_addon_active( 'bp_groups_locator' ) ) {
			include GMW_PS_PATH . '/plugins/groups-locator/loader.php';
		}

		// load members locator features.
		//if ( gmw_is_addon_active( 'bp_members_directory_geolocation' ) ) {
			// include( GMW_PS_PATH . '/plugins/members-directory-geolocation/loader.php' );
		//}
	}

	/**
	 * Enqueue map script
	 *
	 * @param array $scripts map scripts.
	 *
	 * @return array new map scripts.
	 */
	public function enqueue_map_scripts( $scripts ) {

		$scripts['gmw_ps_map'] = array(
			'handle'    => 'gmw-ps-map',
			'src'       => GMW_PS_URL . '/assets/js/gmw.ps.map.min.js',
			'deps'      => array( 'gmw-map' ),
			'ver'       => GMW_PS_VERSION,
			'in_footer' => true,
		);

		return $scripts;
	}

	/**
	 * Include template functions files
	 */
	public function include_template_functions() {
		include_once 'includes/class-gmw-ps-template-functions-helper.php';
		include_once 'includes/gmw-ps-search-form-template-functions.php';
		include_once 'includes/gmw-ps-search-results-template-functions.php';
	}

	/**
	 * Global Maps premium features
	 */
	public function global_maps_features() {

		// include template functions files only when global maps loads.
		add_action( 'gmw_global_map_init', array( $this, 'include_template_functions' ), 10 );
	}

	/**
	 * Ajax Forms premium features
	 */
	public function ajax_forms_features() {

		// include template functions files only when global maps loads.
		add_action( 'gmw_ajaxfms_form_init', array( $this, 'include_template_functions' ), 10 );
	}

	/**
	 * Enqueue/register scripts
	 */
	public function enqueue_scripts() {

		wp_register_style( 'datetime-picker-classic', GMW_URL . '/assets/lib/pickadate/datetime.picker.classic.min.css', array(), '3.6.4' );
		wp_register_style( 'datetime-picker-default', GMW_URL . '/assets/lib/pickadate/datetime.picker.default.min.css', array(), '3.6.4' );
		wp_register_script( 'datetime-picker', GMW_URL . '/assets/lib/pickadate/datetime.picker.min.js', array( 'jquery' ), '3.6.4', true );

		if ( IS_ADMIN ) {

			wp_enqueue_script( 'gmw-ps-admin', $this->plugin_url . '/assets/js/gmw.ps.admin.min.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_style( 'gmw-ps-style', $this->plugin_url . '/assets/css/gmw.ps.admin.min.css', array(), $this->version );

		} else {

			$smartbox_library = gmw_get_option( 'general_settings', 'smartbox_library', 'chosen' );

			// register chosen scripts/style.
			if ( 'chosen' === $smartbox_library ) {

				if ( ! wp_style_is( 'chosen', 'registered' ) ) {
					wp_register_style( 'chosen', GMW_URL . '/assets/lib/chosen/chosen.min.css', array(), '1.8.7' );
				}
				if ( ! wp_script_is( 'chosen', 'registered' ) ) {
					wp_register_script( 'chosen', GMW_URL . '/assets/lib/chosen/chosen.jquery.min.js', array( 'jquery' ), '1.8.7', true );
				}

				// register select2 scripts/style.
			} elseif ( 'select2' === $smartbox_library ) {

				if ( ! wp_style_is( 'select2', 'registered' ) ) {
					wp_register_style( 'select2', GMW_URL . '/assets/lib/select2/css/select2.min.css', array(), '4.0.13' );
				}

				if ( ! wp_script_is( 'select2', 'registered' ) ) {
					wp_register_script( 'select2', GMW_URL . '/assets/lib/select2/js/select2.full.min.js', array( 'jquery' ), '4.0.13', true );
				}
			}
		}
	}
}
GMW_Addon::register( 'GMW_Premium_Settings_Addon' );
