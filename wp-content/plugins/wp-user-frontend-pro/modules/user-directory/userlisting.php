<?php
/**
Plugin Name: User Directory
Plugin URI: https://wedevs.com/products/plugins/wp-user-frontend-pro/user-listing-profile/
Thumbnail Name: wpuf-ul.png
Description: Handle user listing and user profile in frontend
Version: 1.1.1
Author: weDevs
Author URI: https://wedevs.com
License: GPL2
 */

/**
 * User Listing class for WP User Frontend PRO
 *
 * @author weDevs <info@wedevs.com>
 */
class WPUF_User_Listing {

    private $shortcode_name = 'wpuf_user_listing';
    private $unique_meta;
    private $page_url;
    private $count_word = 10;
    private $avatar_size = 128;
    private $settings;
    private $total;

    /**
     * Instance of self
     *
     * @since 3.4.11
     *
     * @var WPUF_User_Listing
     */
    private static $instance = null;

    /**
     * Holds various class instances
     *
     * @since 3.4.11
     *
     * @var array
     */
    private $container = [];

    public function __construct() {

        // add_filter( 'wpuf_ud_nav_urls', array( $this, 'profile_nav_menus' ), 9 );

        // if ( is_admin() ) {
        //     require_once dirname( __FILE__ ) . '/userlisting-admin.php';
        //     new WPUF_Userlisting_Admin();
        // } else {
        //     add_shortcode( $this->shortcode_name, array( $this, 'wpuf_user_listing_init' ) );
        //     add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        // }

        $this->define_constants();
        $this->includes_files();
        $this->init_classes();

        add_action( 'admin_enqueue_scripts', [ $this, 'userlisting_enqueue_scripts' ] );
    }

    /**
     * Enqueue styles and scripts for user listing page
     *
     * @param string $page
     *
     * @return void
     */
    public function userlisting_enqueue_scripts( $page ) {
        if ( 'user-frontend_page_wpuf-settings' !== $page && 'user-frontend_page_wpuf_userlisting' !== $page ) {
            return;
        }

        wp_enqueue_script( 'jquery-ui-sortable' );
    }

    /**
     * Define constants
     *
     * @return void
     */
    public function define_constants() {
        define( 'WPUF_UD_FILE', __FILE__ );
        define( 'WPUF_UD_ROOT', dirname( __FILE__ ) );
        define( 'WPUF_UD_INCLUDES', WPUF_UD_ROOT . '/includes' );
        define( 'WPUF_UD_VIEWS', WPUF_UD_ROOT . '/includes/views' );
        define( 'WPUF_UD_ROOT_URI', plugins_url( '', __FILE__ ) );
        define( 'WPUF_UD_ASSET_URI', WPUF_UD_ROOT_URI . '/assets' );
        define( 'WPUF_UD_TEMPLATES', WPUF_UD_ROOT . '/templates' );
    }

    /**
     * Include all require files
     *
     * @return void
     */
    public function includes_files() {
        if ( is_admin() ) {
            require_once WPUF_UD_INCLUDES . '/Admin.php';
            require_once WPUF_UD_INCLUDES . '/Admin/Builder.php';
            require_once WPUF_UD_INCLUDES . '/Admin/Form.php';
        }

        require_once WPUF_UD_INCLUDES . '/Frontend.php';
        require_once WPUF_UD_INCLUDES . '/ShortCode.php';
        require_once WPUF_UD_INCLUDES . '/Assets.php';
    }

    /**
     * Init classes
     *
     * @return void
     */
    public function init_classes() {
        if ( is_admin() ) {
            $this->container['admin'] = new WPUF\UserDirectory\Admin();
        }

        $this->container['frontend']  = new WPUF\UserDirectory\Frontend();
        $this->container['shortcode'] = new WPUF\UserDirectory\ShortCode();
        $this->container['assets']    = new WPUF\UserDirectory\Assets();
    }

    /**
     * Magic getter to bypass referencing objects
     *
     * @since 3.4.11
     *
     * @param string $prop
     *
     * @return Class Instance
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }
    }

    /**
     * Returns a singleton WPUF_User_Listing class
     * Checks for an existing WPUF_User_Listing instance
     * and if it doesn't find one, creates it.
     *
     * @since 3.4.11
     *
     * @return WPUF_User_Listing
     */
    public static function init() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

/**
 * Return the WPUF_User_Listing instance
 *
 * @return WPUF_User_Listing
 */
function wpuf_user_listing() {
    return WPUF_User_Listing::init();
}

wpuf_user_listing();
