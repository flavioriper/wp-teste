<?php
/**
 * Main class
 *
 * @author  YITH
 * @package YITH Infinite Scrolling
 * @version 1.0.0
 */

defined( 'YITH_INFS' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_INFS' ) ) {
	/**
	 * YITH Infinite Scrolling
	 *
	 * @since 1.0.0
	 */
	class YITH_INFS {

		/**
		 * Single instance of the class
		 *
		 * @since 1.0.0
		 * @var YITH_INFS
		 */
		protected static $instance;

		/**
		 * Plugin version
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $version = YITH_INFS_VERSION;

		/**
		 * Plugin object
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $obj = null;

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 * @return YITH_INFS
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 * @return mixed YITH_INFS_Admin | YITH_INFS_Frontend
		 */
		public function __construct() {
			// Class admin.
			if ( $this->is_admin() ) {
				require_once 'class.yith-infs-admin.php';

				add_action( 'after_setup_theme', array( $this, 'plugin_fw_loader' ), 1 );
				YITH_INFS_Admin();

			} elseif ( $this->load_frontend() ) {
				require_once 'class.yith-infs-frontend.php';
				YITH_INFS_Frontend();
			}
		}

		/**
		 * Check if is admin
		 *
		 * @since  1.0.6
		 * @author Francesco Licandro
		 * @return boolean
		 */
		public function is_admin() {
			$check_ajax    = defined( 'DOING_AJAX' ) && DOING_AJAX;
			$check_context = isset( $_REQUEST['context'] ) && 'frontend' === sanitize_text_field( wp_unslash( $_REQUEST['context'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			return is_admin() && ! ( $check_ajax && $check_context );
		}

		/**
		 * Check if load frontend class
		 *
		 * @since  1.0.6
		 * @author Francesco Licandro
		 * @return boolean
		 */
		public function load_frontend() {
			return 'yes' === yinfs_get_option( 'yith-infs-enable', 'yes' );
		}

		/**
		 * Load Plugin Framework
		 *
		 * @since  1.0.0
		 * @access public
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 */
		public function plugin_fw_loader() {
			if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
				global $plugin_fw_data;
				if ( ! empty( $plugin_fw_data ) ) {
					$plugin_fw_file = array_shift( $plugin_fw_data );
					require_once $plugin_fw_file;
				}
			}
		}
	}
}

/**
 * Unique access to instance of YITH_INFS class
 *
 * @since 1.0.0
 * @return YITH_INFS
 */
function YITH_INFS() { // phpcs:ignore
	return YITH_INFS::get_instance();
}
