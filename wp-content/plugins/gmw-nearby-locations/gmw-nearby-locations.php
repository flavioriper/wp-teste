<?php
/**
 * Plugin Name: GMW Add-on - Nearby Locations
 * Plugin URI: http://www.geomywp.com
 * Description: Display nearby locations using shortcode and widget.
 * Version: 1.4.3
 * Author URI: http://www.geomywp.com
 * Requires at least: 4.5
 * Tested up to: 4.9.5
 * GEO my WP: 3.0+
 * Text Domain: gmw-nearby-location
 * Domain Path: /languages/
 *
 * @package gmw-nearby-location
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
class GMW_Nearby_Locations_Addon extends GMW_Addon {

	/**
	 * Slug
	 *
	 * @var string
	 */
	public $slug = 'nearby_locations';

	/**
	 * Name
	 *
	 * @var string
	 */
	public $name = 'Nearby Locations';

	/**
	 * Prefix
	 *
	 * @var string
	 */
	public $prefix = 'nbl';

	/**
	 * Version
	 *
	 * @var string
	 */
	public $version = '1.4.3';

	/**
	 * License name
	 *
	 * @var string
	 */
	public $license_name = 'nearby_posts';

	/**
	 * Item name
	 *
	 * @var string
	 */
	public $item_name = 'Nearby Locations';

	/**
	 * Item ID
	 *
	 * @var integer
	 */
	public $item_id = 7991;

	/**
	 * Author
	 *
	 * @var string
	 */
	public $author = 'Eyal Fitoussi';

	/**
	 * Required GMW my WP version
	 *
	 * @var string
	 */
	public $gmw_min_version = '3.2';

	/**
	 * Text domain
	 *
	 * @var string
	 */
	public $textdomain = 'gmw-nearby-locations';

	/**
	 * Path
	 *
	 * @var [type]
	 */
	public $full_path = __FILE__;

	/**
	 * Description
	 *
	 * @var string
	 */
	public $description = 'Display nearby locations using shortcode and widget.';

	/**
	 * Extension's page
	 *
	 * @var string
	 */
	public $addon_page = 'https://geomywp.com/extensions/nearby-locations/';

	/**
	 * Support page
	 *
	 * @var string
	 */
	public $support_page = 'https://geomywp.com/support/#gmw-premium-support';

	/**
	 * Docs page
	 *
	 * @var string
	 */
	public $docs_page = 'https://docs.geomywp.com/category/29-nearby-locations';

	/**
	 * Tempaltes folder
	 *
	 * @var string
	 */
	public $templates_folder = 'nearby-locations';

	/**
	 * [$instance description]
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Creates a new instance of the GMW_Nearby_Locations_Addon
	 *
	 * Only creates a new instance if it does not already exist
	 *
	 * @static
	 *
	 * @return object The GMW_Nearby_Locations_Addon class object
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Pre init functions
	 */
	public function pre_init() {

		parent::pre_init();

		include 'includes/gmw-nearby-locations-functions.php';
		include 'includes/class-gmw-nearby-locations.php';
		include 'includes/gmw-nearby-locations-shortcode.php';

		// include Nearby posts child class.
		if ( gmw_is_addon_active( 'posts_locator' ) ) {
			include 'plugins/posts-locator/loader.php';
		}
	}
}
GMW_Addon::register( 'GMW_Nearby_Locations_Addon' );
