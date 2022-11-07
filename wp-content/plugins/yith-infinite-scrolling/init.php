<?php
/**
 * Plugin Name: YITH Infinite Scrolling
 * Plugin URI: https://yithemes.com/themes/plugins/yith-infinite-scrolling/
 * Description: The <code><strong>YITH Infinite Scrolling</strong></code> plugin lets you easily add infinite scroll on your pages. <a href="https://yithemes.com/" target="_blank">Get more plugins for your e-commerce shop on <strong>YITH</strong></a>.
 * Version: 1.7.0
 * Author: YITH
 * Author URI: https://yithemes.com/
 * Text Domain: yith-infinite-scrolling
 * Domain Path: /languages/
 *
 * @author  YITH
 * @package YITH Infinite Scrolling
 * @version 1.7.0
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

/**
 * Add admin notice on installation error
 *
 * @since 1.0.0
 * @author Francesco Licandro
 * @return void
 */
function yith_infs_install_free_admin_notice() {
	?>
	<div class="error">
		<p><?php esc_html_e( 'You can\'t activate the free version of YITH Infinite Scrolling while you are using the premium one.', 'yith-infinite-scrolling' ); ?></p>
	</div>
	<?php
}

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );


if ( ! defined( 'YITH_INFS_VERSION' ) ) {
	define( 'YITH_INFS_VERSION', '1.7.0' );
}

if ( ! defined( 'YITH_INFS_FREE_INIT' ) ) {
	define( 'YITH_INFS_FREE_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_INFS_INIT' ) ) {
	define( 'YITH_INFS_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_INFS' ) ) {
	define( 'YITH_INFS', true );
}

if ( ! defined( 'YITH_INFS_FILE' ) ) {
	define( 'YITH_INFS_FILE', __FILE__ );
}

if ( ! defined( 'YITH_INFS_URL' ) ) {
	define( 'YITH_INFS_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'YITH_INFS_DIR' ) ) {
	define( 'YITH_INFS_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'YITH_INFS_TEMPLATE_PATH' ) ) {
	define( 'YITH_INFS_TEMPLATE_PATH', YITH_INFS_DIR . 'templates' );
}

if ( ! defined( 'YITH_INFS_SLUG' ) ) {
	define( 'YITH_INFS_SLUG', 'yith-infinite-scrolling' );
}

if ( ! defined( 'YITH_INFS_ASSETS_URL' ) ) {
	define( 'YITH_INFS_ASSETS_URL', YITH_INFS_URL . 'assets' );
}

if ( ! defined( 'YITH_INFS_OPTION_NAME' ) ) {
	define( 'YITH_INFS_OPTION_NAME', 'yit_infs_options' );
}

/* Plugin Framework Version Check */
if ( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YITH_INFS_DIR . 'plugin-fw/init.php' ) ) {
	require_once YITH_INFS_DIR . 'plugin-fw/init.php';
}
yit_maybe_plugin_fw_loader( YITH_INFS_DIR );

/**
 * Plugin init
 *
 * @since 1.0.0
 * @author Francesco Licandro
 * @return void
 */
function yith_infs_init() {

	load_plugin_textdomain( 'yith-infinite-scrolling', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	// Load required classes and functions.
	require_once 'includes/functions.yith-infs.php';
	require_once 'includes/class.yith-infs.php';

	// Let's start the game!
	YITH_INFS();
}

add_action( 'yith_infs_init', 'yith_infs_init' );


/**
 * Plugin install
 *
 * @since 1.0.0
 * @author Francesco Licandro
 * @return void
 */
function yith_infs_install() {

	if ( defined( 'YITH_INFS_PREMIUM' ) ) {
		add_action( 'admin_notices', 'yith_infs_install_free_admin_notice' );
		deactivate_plugins( plugin_basename( __FILE__ ) );
	} else {
		do_action( 'yith_infs_init' );
	}
}

add_action( 'plugins_loaded', 'yith_infs_install', 11 );
