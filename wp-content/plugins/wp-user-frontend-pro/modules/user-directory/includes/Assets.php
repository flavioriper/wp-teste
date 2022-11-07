<?php
namespace WPUF\UserDirectory;

/**
 * Class assets
 */
class Assets {
    /**
     * Constructor for the Assets class
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Init hooks
     *
     * @return void
     */
    public function init_hooks() {
        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', [ $this, 'register' ], 5 );
        } else {
            add_action( 'wp_enqueue_scripts', [ $this, 'register' ], 5 );
        }
    }

    /**
     * Register styles and scripsts
     *
     * @return void
     */
    public function register() {
        $this->register_scripts( $this->get_scripts() );
        $this->register_styles( $this->get_styles() );
    }

    /**
     * Register scripts
     *
     * @param array $scripts
     *
     * @return void
     */
    public function register_scripts( $scripts ) {
        foreach ( $scripts as $handle => $script ) {
            $deps      = isset( $script['deps'] ) ? $script['deps'] : false;
            $in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
            $version   = isset( $script['version'] ) ? $script['version'] : WPUF_PRO_VERSION;

            wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
        }
    }

    /**
     * Register styles
     *
     * @param array $styles
     *
     * @return void
     */
    public function register_styles( $styles ) {
        foreach ( $styles as $handle => $style ) {
            $src   = $style['src'];
            $deps  = isset( $style['deps'] ) ? $style['deps'] : [];
            $ver   = isset( $style['ver'] ) ? $style['ver'] : WPUF_PRO_VERSION;
            $media = isset( $style['media'] ) ? $style['media'] : 'all';

            wp_register_style( $handle, $src, $deps, $ver, $media );
        }
    }

    /**
     * Get all registered scripts
     *
     * @return array
     */
    public function get_scripts() {
        return [
            'wpuf-user-directory-admin-script' => [
                'src'       => WPUF_UD_ASSET_URI . '/js/userlisting.js',
                'deps'      => [ 'jquery', 'underscore' ],
                'version'   => filemtime( WPUF_UD_ROOT . '/assets/js/userlisting.js' ),
                'in_footer' => true,
            ],
            'wpuf-user-directory-frontend-script' => [
                'src'       => WPUF_UD_ASSET_URI . '/js/wpuf-ud-frontend.js',
                'deps'      => [ 'jquery' ],
                'version'   => WPUF_PRO_VERSION,
                'in_footer' => true,
            ],
        ];
    }

    /**
     * Get all registered styles
     *
     * @return array
     */
    public function get_styles() {
        return [
            'wpuf-user-directory-admin-style'   => [
                'src' => WPUF_UD_ASSET_URI . '/css/admin.css',
            ],
            'wpuf-user-directory-frontend-style' => [
                'src' => WPUF_UD_ASSET_URI . '/css/profile-listing.css',
            ],
            'wpuf-ud-layout-one' => [
                'src' => WPUF_UD_ASSET_URI . '/css/ud-layout-one.css',
            ],
            'wpuf-ud-layout-two' => [
                'src' => WPUF_UD_ASSET_URI . '/css/ud-layout-two.css',
            ],
            'wpuf-ud-layout-three' => [
                'src' => WPUF_UD_ASSET_URI . '/css/ud-layout-three.css',
            ],
            'wpuf-listing-layout-two' => [
                'src' => WPUF_UD_ASSET_URI . '/css/listing-layout-two.css',
            ],
            'wpuf-listing-layout-three' => [
                'src' => WPUF_UD_ASSET_URI . '/css/listing-layout-three.css',
            ],
            'wpuf-listing-layout-four' => [
                'src' => WPUF_UD_ASSET_URI . '/css/listing-layout-four.css',
            ],
            'wpuf-listing-layout-five' => [
                'src' => WPUF_UD_ASSET_URI . '/css/listing-layout-five.css',
            ],
            'wpuf-listing-layout-six' => [
                'src' => WPUF_UD_ASSET_URI . '/css/listing-layout-six.css',
            ],
        ];
    }
}

