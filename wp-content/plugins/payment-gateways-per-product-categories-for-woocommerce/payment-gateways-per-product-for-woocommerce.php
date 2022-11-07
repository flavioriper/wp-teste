<?php
/*
Plugin Name: Payment Gateways per Products for WooCommerce
Plugin URI: https://wpfactory.com/item/payment-gateways-per-product-for-woocommerce/
Description: Show WooCommerce gateway only if there is selected product, product category or product tag in cart.
Version: 1.7.1
Author: WPWhale
Author URI: https://wpwhale.com
Text Domain: payment-gateways-per-product-categories-for-woocommerce
Domain Path: /langs
Copyright: © 2022 WPWhale
WC tested up to: 6.5
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Check if WooCommerce is active
$plugin = 'woocommerce/woocommerce.php';
if (
	! in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) &&
	! ( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
) {
	return;
}

if ( 'payment-gateways-per-product-for-woocommerce.php' === basename( __FILE__ ) ) {
	// Check if Pro is active, if so then return
	$plugin = 'payment-gateways-per-product-for-woocommerce-pro/payment-gateways-per-product-for-woocommerce-pro.php';
	if (
		in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) ||
		( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
	) {
		return;
	}
}

if ( ! class_exists( 'Alg_WC_PGPP' ) ) :

/**
 * Main Alg_WC_PGPP Class
 *
 * @class   Alg_WC_PGPP
 * @version 1.2.1
 * @since   1.0.0
 */
final class Alg_WC_PGPP {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public $version = '1.2.1';

	/**
	 * @var   Alg_WC_PGPP The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_WC_PGPP Instance
	 *
	 * Ensures only one instance of Alg_WC_PGPP is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @static
	 * @return  Alg_WC_PGPP - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_WC_PGPP Constructor.
	 *
	 * @version 1.2.1
	 * @since   1.0.0
	 * @access  public
	 */
	function __construct() {

		// Set up localisation
		load_plugin_textdomain( 'payment-gateways-per-product-categories-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

		// Pro
		if ( 'payment-gateways-per-product-for-woocommerce-pro.php' === basename( __FILE__ ) ) {
			require_once( 'includes/pro/class-alg-wc-pgpp-pro.php' );
		}

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 */
	function includes() {
		// Core
		$this->core = require_once( 'includes/class-alg-wc-pgpp-core.php' );
	}

	/**
	 * admin.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	function admin() {
		// Action links
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
		// Settings
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
		require_once( 'includes/settings/class-alg-wc-pgpp-settings-section.php' );
		$this->settings = array();
		$this->settings['general']    = require_once( 'includes/settings/class-alg-wc-pgpp-settings-general.php' );
		$this->settings['cats']       = require_once( 'includes/settings/class-alg-wc-pgpp-settings-cats.php' );
		$this->settings['tags']       = require_once( 'includes/settings/class-alg-wc-pgpp-settings-tags.php' );
		$this->settings['products']   = require_once( 'includes/settings/class-alg-wc-pgpp-settings-products.php' );
		// Version updated
		if ( get_option( 'alg_wc_pgpp_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_updated' ) );
		}
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();
		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_pgpp' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
		if ( 'payment-gateways-per-product-for-woocommerce.php' === basename( __FILE__ ) ) {
			$custom_links[] = '<a href="https://wpfactory.com/item/payment-gateways-per-product-for-woocommerce/">' .
				__( 'Unlock All', 'payment-gateways-per-product-categories-for-woocommerce' ) . '</a>';
		}
		return array_merge( $custom_links, $links );
	}

	/**
	 * Add Payment Gateways per Products settings tab to WooCommerce settings.
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = require_once( 'includes/settings/class-alg-wc-settings-pgpp.php' );
		return $settings;
	}

	/**
	 * version_updated.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	function version_updated() {
		update_option( 'alg_wc_pgpp_version', $this->version );
	}

	/**
	 * Get the plugin url.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

}

endif;

if ( ! function_exists( 'alg_wc_pgpp' ) ) {
	/**
	 * Returns the main instance of Alg_WC_PGPP to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  Alg_WC_PGPP
	 */
	function alg_wc_pgpp() {
		return Alg_WC_PGPP::instance();
	}
}

alg_wc_pgpp();

function alg_wc_pgpp_custom_plugin_scripts($hook) {
	if ( 'woocommerce_page_wc-settings' != $hook ) {
        return;
    }
    wp_enqueue_script( 'select2' );
}
add_action( 'admin_enqueue_scripts', 'alg_wc_pgpp_custom_plugin_scripts', PHP_INT_MAX );

function alg_wc_pgpp_custom_admin_js_add_order() {
	?>
	<script>
	jQuery(document).ready(function(){
		is_checkedalg_wc_pqpp();
		
		if (jQuery.isFunction(jQuery('.products_select_pgpp').select2)){
			jQuery('.products_select_pgpp').select2({
				ajax: {
						url: ajaxurl, 
						dataType: 'json',
						delay: 250, 
						data: function (params) {
							return {
								q: params.term, 
								action: 'alg_wc_pgpp_get_products' 
							};
						},
						processResults: function( data ) {
						var options = [];
						if ( data ) {
							jQuery.each( data, function( index, text ) {
								options.push( { id: text[0], text: text[1]  } );
							});
						
						}
						return {
							results: options
						};
					},
					cache: true
				},
				minimumInputLength: 3
			});
		}

	});
	jQuery("#alg_wc_pgpp_advanced_fallback_gateway_enabled").on("click", function(){
        is_checkedalg_wc_pqpp();
    });
	function is_checkedalg_wc_pqpp(){
		if(jQuery("#alg_wc_pgpp_advanced_fallback_gateway_enabled").length > 0){
			var check = jQuery("#alg_wc_pgpp_advanced_fallback_gateway_enabled").prop("checked");
			if(check) {
				 jQuery('#alg_wc_pgpp_advanced_fallback_gateway').removeAttr('disabled');
			} else {
				 jQuery('#alg_wc_pgpp_advanced_fallback_gateway').attr('disabled','disabled');
				  if (jQuery.isFunction(jQuery('#alg_wc_pgpp_advanced_fallback_gateway').select2)){
					jQuery( '#alg_wc_pgpp_advanced_fallback_gateway' ).select2();
				  }
			}
		}
	}
	</script>
	<?php
}
add_action('admin_footer', 'alg_wc_pgpp_custom_admin_js_add_order');

add_action( 'wp_ajax_noprev_alg_wc_pgpp_get_products', 'alg_wc_pgpp_get_products_ajax_callback' );
add_action( 'wp_ajax_alg_wc_pgpp_get_products', 'alg_wc_pgpp_get_products_ajax_callback' );
function alg_wc_pgpp_get_products_ajax_callback(){

	// we will pass post IDs and titles to this array
	$return = array();
	$add_variations = false;
	if('yes' === get_option( 'alg_wc_pgpp_products_add_variations', 'no' )){
		$add_variations = true;		
	}
	// you can use WP_Query, query_posts() or get_posts() here - it doesn't matter
	$loop = new WP_Query( array( 
		's'=> $_GET['q'], // the search query
		'post_status' => 'publish',
		'posts_per_page' => 50,
		'post_type' => array('product'),
		'orderby'        => 'title',
		'order'          => 'ASC',
		'fields'         => 'ids',
	) );
	foreach ( $loop->posts as $post_id ) {
		$maintitle = get_the_title( $post_id ) . ' (#' . $post_id . ')';
		$title = ( mb_strlen( $maintitle ) > 100 ) ? mb_substr( $maintitle, 0, 99 ) . '...' : $maintitle;
		$return[ $post_id ] = array( $post_id, $title );
		if ( $add_variations ) {
			$_product = wc_get_product( $post_id );
			if ( $_product->is_type( 'variable' ) ) {
				unset( $return[ $post_id ] );
				foreach ( $_product->get_children() as $child_id ) {
					$childmaintitle = get_the_title( $child_id ) . ' (#' . $child_id . ')';
					$chtitle = ( mb_strlen( $childmaintitle ) > 100 ) ? mb_substr( $childmaintitle, 0, 99 ) . '...' : $childmaintitle;
					$return[ $child_id ] = array( $child_id, $chtitle );
				}
			}
		}
	}
	echo json_encode( $return );
	die;
}
