<?php
/*
Plugin Name: Social Login & Registration
Plugin URI: https://wedevs.com/docs/wp-user-frontend-pro/modules/social-login-registration/
Thumbnail Name: Social-Media-Login.png
Description: Add Social Login and registration feature in WP User Frontend
Version: 1.1
Author: weDevs
Author URI: http://wedevs.com/
License: GPL2
*/

/**
 * Copyright (c) 2017 weDevs ( email: info@wedevs.com ). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WPUF_Social_Login
 *
 * @class WPUF_Social_Login The class that holds the entire WPUF_Social_Login plugin
 */

use Hybridauth\Exception\Exception;
use Hybridauth\Hybridauth;
use Hybridauth\HttpClient;
use Hybridauth\Storage\Session;

class WPUF_Social_Login {

    private $account_page_url = null;
    private $access_token;
    private $form_id;
    private $provider;
    private $config;

    /**
     * Load automatically when class instantiated
     *
     * @since 2.4
     *
     * @uses actions|filter hooks
     */
    public function __construct() {
        if ( ! class_exists( 'Hybridauth' ) ) {
            if ( class_exists( 'WeDevs_Dokan' ) && class_exists( 'Dokan_Pro' ) ) {
                if ( version_compare( DOKAN_PRO_PLUGIN_VERSION, '3.0.0', '<' ) ) {
                    require_once DOKAN_PRO_INC . '/lib/hybridauth/autoload.php';
                }
            } else {
                require_once dirname( __FILE__ ) . '/lib/hybridauth/autoload.php';
            }
        }

        add_action( 'setup_theme', array( $this, 'set_account_page_url' ) );
        add_action( 'admin_notices', array( $this, 'show_admin_notice' ) );
        add_filter( 'wpuf_settings_sections', array( $this, 'wpuf_social_settings_tab' ) );
        add_filter( 'wpuf_settings_fields', array( $this, 'wpuf_pro_social_api_fields' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 9 );

        //Hybrid auth action
        add_action( 'template_redirect', array( $this, 'monitor_authentication_requests' ) );

        // add social buttons on registration form
        add_action( 'wpuf_login_form_bottom', array( $this, 'render_social_logins' ) );
        add_action( 'wpuf_reg_form_bottom', array( $this, 'render_social_logins' ) );
        add_action( 'wpuf_add_profile_form_bottom', array( $this, 'render_social_logins' ) );
    }

    /**
     * Instantiate the class
     *
     * @return object
     * @since 2.6
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new WPUF_Social_Login();
        }

        return $instance;
    }

    /**
     * Check if current page is WPUF Account page
     *
     * @since 3.2.0
     *
     * @return bool
     */
    public function is_account_page() {
        global $post;

        $account_page_id = wpuf_get_option( 'account_page', 'wpuf_my_account', 0 );

        return ! empty( $post->ID ) && absint( $account_page_id ) === $post->ID;
    }

    /**
     * Set account page URL
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function set_account_page_url() {
        $account_page_id = wpuf_get_option( 'account_page', 'wpuf_my_account', 0 );

        if ( ! empty( $account_page_id ) ) {
            $this->account_page_url = get_permalink( $account_page_id );
            $this->config           = $this->get_providers_config();
        }
    }

    /**
     * Show admin notice if account page is not set in settings
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function show_admin_notice() {
        if ( empty( $this->account_page_url ) ) {
            ?>
                <div class="error">
                    <p><?php esc_html_e( 'To use WPUF Social Login module, please set your account page in My Account > Account Page settings first.', 'wpuf-pro' ); ?></p>
                </div>
            <?php
        }
    }

    /**
     * Register all scripts
     *
     * @return void
     **/
    public function enqueue_scripts() {
        // register styles
        wp_enqueue_style( 'wpuf-social-style', WPUF_PRO_ASSET_URI . '/css/jssocials.css', '', WPUF_VERSION );
        // enqueue scripts
        wp_enqueue_script( 'wpuf-social-script', WPUF_PRO_ASSET_URI . '/js/jssocials.min.js', array( 'jquery' ), WPUF_VERSION, true );
    }

    /**
     * Get configuration values for HybridAuth
     *
     * @return array
     */
    private function get_providers_config() {
        $config = [
            'callback'  => $this->account_page_url,
            'providers' => [
                'Google'    => [
                    'enabled' => true,
                    'keys'    => [
                        'id' => '',
                        'secret' => '',
                    ],
                ],
                'Facebook'  => [
                    'enabled'        => true,
                    'keys'           => [
                        'id' => '',
                        'secret' => '',
                    ],
                    'trustForwarded' => false,
                    'scope'          => 'email, public_profile',
                ],
                'Twitter'   => [
                    'enabled'      => true,
                    'keys'         => [
                        'key' => '',
                        'secret' => '',
                    ],
                    'includeEmail' => true,
                ],
                'LinkedIn'  => [
                    'enabled' => true,
                    'keys'    => [
                        'id' => '',
                        'secret' => '',
                    ],
                ],
                'Instagram' => [
                    'enabled' => true,
                    'keys'    => [
                        'id' => '',
                        'secret' => '',
                    ],
                ],
            ],
        ];

        //facebook config from admin
        $fb_id     = wpuf_get_option( 'fb_app_id', 'wpuf_social_api' );
        $fb_secret = wpuf_get_option( 'fb_app_secret', 'wpuf_social_api' );

        if ( $fb_id !== '' && $fb_secret !== '' ) {
            $config['providers']['Facebook']['keys']['id']     = $fb_id;
            $config['providers']['Facebook']['keys']['secret'] = $fb_secret;
        }

        //google config from admin
        $g_id     = wpuf_get_option( 'google_app_id', 'wpuf_social_api' );
        $g_secret = wpuf_get_option( 'google_app_secret', 'wpuf_social_api' );

        if ( $g_id !== '' && $g_secret !== '' ) {
            $config['providers']['Google']['keys']['id']     = $g_id;
            $config['providers']['Google']['keys']['secret'] = $g_secret;
        }
        //linkedin config from admin
        $l_id     = wpuf_get_option( 'linkedin_app_id', 'wpuf_social_api' );
        $l_secret = wpuf_get_option( 'linkedin_app_secret', 'wpuf_social_api' );

        if ( $l_id !== '' && $l_secret !== '' ) {
            $config['providers']['LinkedIn']['keys']['id']     = $l_id;
            $config['providers']['LinkedIn']['keys']['secret'] = $l_secret;
        }

        //Twitter config from admin
        $twitter_id     = wpuf_get_option( 'twitter_app_id', 'wpuf_social_api' );
        $twitter_secret = wpuf_get_option( 'twitter_app_secret', 'wpuf_social_api' );

        if ( $twitter_id !== '' && $twitter_secret !== '' ) {
            $config['providers']['Twitter']['keys']['key']    = $twitter_id;
            $config['providers']['Twitter']['keys']['secret'] = $twitter_secret;
        }

        //Instagram config from admin
        $instagram_id     = wpuf_get_option( 'instagram_app_id', 'wpuf_social_api' );
        $instagram_secret = wpuf_get_option( 'instagram_app_secret', 'wpuf_social_api' );

        if ( $instagram_id !== '' && $instagram_secret !== '' ) {
            $config['providers']['Instagram']['keys']['key']    = $instagram_id;
            $config['providers']['Instagram']['keys']['secret'] = $instagram_secret;
        }

        /**
         * Filter the Config array of Hybridauth
         *
         * @param array $config
         *
         * @since 1.0.0
         */
        $config = apply_filters( 'wpuf_social_providers_config', $config );

        return $config;
    }

    /**
     * Monitors Url for Hauth Request and process Hybridauth for authentication
     *
     * @return void
     */
    public function monitor_authentication_requests() {
        if ( ! $this->is_account_page() ) {
            return;
        }

        try {
            /**
             * Feed the config array to Hybridauth
             *
             * @var Hybridauth
             */
            $hybridauth = new Hybridauth( $this->config );

            /**
             * Initialize session storage.
             *
             * @var Session
             */
            $storage = new Session();

            /**
             * Hold information about provider when user clicks on Sign In.
             */
            $provider = ! empty( $_GET['wpuf_reg'] ) ? sanitize_text_field( wp_unslash( $_GET['wpuf_reg'] ) ) : '';
            $form_id  = ! empty( $_GET['form_id'] ) ? sanitize_text_field( wp_unslash( $_GET['form_id'] ) ) : '';

            if ( $provider ) {
                $storage->set( 'provider', $provider );
                $storage->set( 'form_id', $form_id );
            }

            $provider = $storage->get( 'provider' );

            if ( $provider ) {
                $adapter            = $hybridauth->authenticate( $provider );
                $access_token       = $adapter->getAccessToken();
                $this->provider     = $provider;
                $this->access_token = $access_token && ! empty( $access_token['access_token'] ) ? $access_token['access_token'] : '';
                $this->form_id      = $storage->get( 'form_id' );
                $storage->clear();
            }

            if ( ! isset( $adapter ) ) {
                return;
            }

            $user_profile = $adapter->getUserProfile();
            $from_obj     = get_object_vars( $user_profile );
            $user_profile = new stdClass();

            array_map(
                function ( $key, $val ) use ( &$user_profile ) {
                    $user_profile->{$this->camel_to_snake_case( $key )} = $val;
                }, array_keys( $from_obj ), $from_obj
            );

            if ( ! $user_profile ) {
                wp_redirect( $this->account_page_url );
                exit;
            }

            if ( empty( $user_profile->email ) ) {
                wp_redirect( $this->account_page_url );
                exit;
            }

            $wp_user = get_user_by( 'email', $user_profile->email );

            if ( ! $wp_user ) {
                $this->register_new_user( $user_profile );
            } else {
                $this->login_user( $wp_user );
            }
        } catch ( Exception $e ) {
            wp_die( $e->getMessage() );
        }
    }

    /**
     * Filter admin menu settings section
     *
     * @param array $settings
     *
     * @return array
     */
    public function wpuf_social_settings_tab( $settings ) {
        $s_settings = array(
            array(
                'id'    => 'wpuf_social_api',
                'title' => __( 'Social Login', 'wpuf-pro' ),
                'icon'  => 'dashicons-share',
            ),
        );

        return array_merge( $settings, $s_settings );
    }

    /**
     * Render settings fields for admin settings section
     *
     * @param array $settings_fields
     *
     * @return array
     **/

    public function wpuf_pro_social_api_fields( $settings_fields ) {
        $social_settings_fields = array(
            'wpuf_social_api' => array(
                'enabled'              => array(
                    'name'  => 'enabled',
                    'label' => __( 'Enable Social Login', 'wpuf-pro' ),
                    'type'  => 'checkbox',
                    'desc'  => __( 'Enabling this will add Social Icons under registration form to allow users to login or register using Social Profiles', 'wpuf-pro' ),
                ),
                'facebook_app_label'   => array(
                    'name'  => 'fb_app_label',
                    'label' => __( 'Facebook App Settings', 'wpuf-pro' ),
                    'type'  => 'html',
                    'desc'  => '<a target="_blank" href="https://developers.facebook.com/apps/">' . __( 'Create an App', 'wpuf-pro' ) . '</a>' . __( 'if you don\'t have one and fill App ID and App Secret below. ', 'wpuf-pro' ),
                ),
                'facebook_app_url'     => array(
                    'name'  => 'fb_app_url',
                    'label' => __( 'Redirect URI', 'wpuf-pro' ),
                    'type'  => 'html',
                    'desc'  => "<input class='regular-text' type='text' disabled value='{$this->account_page_url}'>",
                ),
                'facebook_app_id'      => array(
                    'name'  => 'fb_app_id',
                    'label' => __( 'App Id', 'wpuf-pro' ),
                    'type'  => 'text',
                ),
                'facebook_app_secret'  => array(
                    'name'  => 'fb_app_secret',
                    'label' => __( 'App Secret', 'wpuf-pro' ),
                    'type'  => 'text',
                ),
                'twitter_app_label'    => array(
                    'name'  => 'twitter_app_label',
                    'label' => __( 'Twitter App Settings', 'wpuf-pro' ),
                    'type'  => 'html',
                    'desc'  => '<a target="_blank" href="https://apps.twitter.com/">' . __( 'Create an App', 'wpuf-pro' ) . '</a>' . __( 'if you don\'t have one and fill Consumer key and Consumer Secret below.', 'wpuf-pro' ),
                ),
                'twitter_app_url'      => array(
                    'name'  => 'twitter_app_url',
                    'label' => __( 'Callback URL', 'wpuf-pro' ),
                    'type'  => 'html',
                    'desc'  => "<input class='regular-text' type='text' disabled value='{$this->account_page_url}'>",
                ),
                'twitter_app_id'       => array(
                    'name'  => 'twitter_app_id',
                    'label' => __( 'Consumer Key', 'wpuf-pro' ),
                    'type'  => 'text',
                ),
                'twitter_app_secret'   => array(
                    'name'  => 'twitter_app_secret',
                    'label' => __( 'Consumer Secret', 'wpuf-pro' ),
                    'type'  => 'text',
                ),
                'google_app_label'     => array(
                    'name'  => 'google_app_label',
                    'label' => __( 'Google App Settings', 'wpuf-pro' ),
                    'type'  => 'html',
                    'desc'  => '<a target="_blank" href="https://console.developers.google.com/project">' . __( 'Create an App', 'wpuf-pro' ) . '</a>' . __( ' if you don\'t have one and fill Client ID and Client Secret below.', 'wpuf-pro' ),
                ),
                'google_app_url'       => array(
                    'name'  => 'google_app_url',
                    'label' => __( 'Redirect URI', 'wpuf-pro' ),
                    'type'  => 'html',
                    'desc'  => "<input class='regular-text' type='text' disabled value='{$this->account_page_url}'>",
                ),
                'google_app_id'        => array(
                    'name'  => 'google_app_id',
                    'label' => __( 'Client ID', 'wpuf-pro' ),
                    'type'  => 'text',
                ),
                'google_app_secret'    => array(
                    'name'  => 'google_app_secret',
                    'label' => __( 'Client secret', 'wpuf-pro' ),
                    'type'  => 'text',
                ),
                'linkedin_app_label'   => array(
                    'name'  => 'linkedin_app_label',
                    'label' => __( 'Linkedin App Settings', 'wpuf-pro' ),
                    'type'  => 'html',
                    'desc'  => '<a target="_blank" href="https://www.linkedin.com/developer/apps">' . __( 'Create an App', 'wpuf-pro' ) . '</a>' . __( ' if you don\'t have one and fill Client ID and Client Secret below.', 'wpuf-pro' ),
                ),
                'linkedin_app_url'     => array(
                    'name'  => 'linkedin_app_url',
                    'label' => __( 'Redirect URL', 'wpuf-pro' ),
                    'type'  => 'html',
                    'desc'  => "<input class='regular-text' type='text' disabled value='{$this->account_page_url}'>",
                ),
                'linkedin_app_id'      => array(
                    'name'  => 'linkedin_app_id',
                    'label' => __( 'Client ID', 'wpuf-pro' ),
                    'type'  => 'text',
                ),
                'linkedin_app_secret'  => array(
                    'name'  => 'linkedin_app_secret',
                    'label' => __( 'Client Secret', 'wpuf-pro' ),
                    'type'  => 'text',
                ),
                'instagram_app_label'  => array(
                    'name'  => 'instagram_app_label',
                    'label' => __( 'Instagram App Settings', 'wpuf-pro' ),
                    'type'  => 'html',
                    'desc'  => '<a target="_blank" href="https://www.instagram.com/developer/">' . __( 'Create an App', 'wpuf-pro' ) . '</a>' . __( ' if you don\'t have one and fill Client ID and Client Secret below.', 'wpuf-pro' ),
                ),
                'instagram_app_url'    => array(
                    'name'  => 'instagram_app_url',
                    'label' => __( 'Redirect URI', 'wpuf-pro' ),
                    'type'  => 'html',
                    'desc'  => "<input class='regular-text' type='text' disabled value='{$this->account_page_url}'>",
                ),
                'instagram_app_id'     => array(
                    'name'  => 'instagram_app_id',
                    'label' => __( 'Client ID', 'wpuf-pro' ),
                    'type'  => 'text',
                ),
                'instagram_app_secret' => array(
                    'name'  => 'instagram_app_secret',
                    'label' => __( 'Client Secret', 'wpuf-pro' ),
                    'type'  => 'text',
                ),
            ),
        );

        return array_merge( $settings_fields, $social_settings_fields );
    }

    /**
     * Render social login icons
     *
     * @return void
     */
    public function render_social_logins() {
        $is_enabled = wpuf_get_option( 'enabled', 'wpuf_social_api', 'off' );

        if ( ! wpuf_validate_boolean( $is_enabled ) ) {
            return;
        }

        $configured_providers = [];

        //facebook config from admin
        $fb_id     = wpuf_get_option( 'fb_app_id', 'wpuf_social_api' );
        $fb_secret = wpuf_get_option( 'fb_app_secret', 'wpuf_social_api' );

        if ( $fb_id !== '' && $fb_secret !== '' ) {
            $configured_providers [] = 'facebook';
        }
        //google config from admin
        $g_id     = wpuf_get_option( 'google_app_id', 'wpuf_social_api' );
        $g_secret = wpuf_get_option( 'google_app_secret', 'wpuf_social_api' );

        if ( $g_id !== '' && $g_secret !== '' ) {
            $configured_providers [] = 'google';
        }
        //linkedin config from admin
        $l_id     = wpuf_get_option( 'linkedin_app_id', 'wpuf_social_api' );
        $l_secret = wpuf_get_option( 'linkedin_app_secret', 'wpuf_social_api' );

        if ( $l_id !== '' && $l_secret !== '' ) {
            $configured_providers [] = 'linkedin';
        }

        //Twitter config from admin
        $twitter_id     = wpuf_get_option( 'twitter_app_id', 'wpuf_social_api' );
        $twitter_secret = wpuf_get_option( 'twitter_app_secret', 'wpuf_social_api' );

        if ( $twitter_id !== '' && $twitter_secret !== '' ) {
            $configured_providers [] = 'twitter';
        }

        //Instagram config from admin
        $instagram_id     = wpuf_get_option( 'instagram_app_id', 'wpuf_social_api' );
        $instagram_secret = wpuf_get_option( 'instagram_app_secret', 'wpuf_social_api' );

        if ( $instagram_id !== '' && $instagram_secret !== '' ) {
            $configured_providers [] = 'instagram';
        }

        /**
         * Filter the list of Providers connect links to display
         *
         * @param array $providers
         *
         * @since 1.0.0
         */
        $providers = apply_filters( 'wpuf_social_provider_list', $configured_providers );

        $redirect_uri = preg_replace( '/^http:/i', 'https:', $this->account_page_url );

            $base_url  = $this->account_page_url;
            $providers = $providers;
            $pro       = true;

        if ( empty( $this->account_page_url ) ) {
            ?>
                <div class="error">
                    <p><?php esc_html_e( 'Account Page URL is not set in WPUF Admin Settings.', 'wpuf-pro' ); ?></p>
                </div>
            <?php
            return;
        }

        if ( ! is_user_logged_in() && ! empty( $configured_providers ) ) {
            ?>
            <script>
                var wpuf_reg_form_id = document.querySelector('input[name=form_id]').value;
                document.cookie = 'wpuf_reg_form_id' + "=" + wpuf_reg_form_id + ';path=/';
                window.addEventListener('DOMContentLoaded', (e) => {
                    document.querySelectorAll('#wpuf_social_link a').forEach(function(link){link.href=link.href+'&form_id='+wpuf_reg_form_id})
                });
            </script>
            <hr>
            <div class="wpuf-social-login-text"
                 style="text-align:center; font-weight: bold;"><?php __( 'You may also connect with', 'wpuf-pro' ); ?></div>
            <br>
            <ul class="jssocials-shares" id="wpuf_social_link">
                <?php foreach ( $providers as $provider ) : ?>
                    <li class="jssocials-share jssocials-share-<?php echo $provider; ?>">
                        <a href="<?php echo add_query_arg( array( 'wpuf_reg' => $provider ), $this->account_page_url ); ?>"
                           class="jssocials-share-link">
                            <img src="<?php echo WPUF_PRO_ASSET_URI . '/images/social-icons/' . $provider . '.png'; ?>"
                                 class="jssocials-share-logo" alt=""> <?php echo ucfirst( $provider ); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php
        }
    }

    /**
     * Recursive function to generate a unique username.
     *
     * If the username already exists, will add a numerical suffix which will increase until a unique username is found.
     *
     * @param string $username
     *
     * @return string The unique username.
     */
    public function generate_unique_username( $username ) {
        static $i;
        if ( null === $i ) {
            $i = 1;
        } else {
            $i ++;
        }
        $username = preg_replace( '/([^@]*).*/', '$1', $username );
        if ( ! username_exists( $username ) ) {
            return $username;
        }
        $new_username = sprintf( '%s_%s', $username, $i );
        if ( ! username_exists( $new_username ) ) {
            return $new_username;
        } else {
            return call_user_func( array( $this, 'generate_unique_username' ), $username );
        }
    }

    /**
     * Register a new user
     *
     * @param object $data
     *
     * @param string $provider
     *
     * @return void
     */
    private function register_new_user( $data ) {
        $form_settings = array();
        $form_id       = $this->form_id;
        $form_settings = wpuf_get_form_settings( $form_id );

        if ( wpuf_is_vendor_reg( $form_id ) ) {
            $this->account_page_url = wpuf_get_dokan_redirect_url();
        }

        $user_role = isset( $form_settings['role'] ) ? $form_settings['role'] : 'subscriber';
        $uname     = $data->email;

        if ( empty( $uname ) ) {
            $uname = $data->display_name;
        }

        $userdata = array(
            'user_login' => $this->generate_unique_username( $uname ),
            'user_email' => $data->email,
            'user_pass'  => wp_generate_password(),
            'first_name' => $data->first_name,
            'last_name'  => $data->last_name,
            'role'       => $user_role,
        );

        $user_id = wp_insert_user( $userdata );
        if ( ! is_wp_error( $user_id ) ) {
            $this->store_avatar( $user_id, $data );
            $this->login_user( get_userdata( $user_id ) );
            wp_redirect( $this->account_page_url );
            exit;
        }
    }

    /**
     * Log in existing users
     *
     * @param WP_User $wp_user
     *
     * return void
     */
    private function login_user( $wp_user ) {
        clean_user_cache( $wp_user->ID );
        wp_clear_auth_cookie();
        wp_set_current_user( $wp_user->ID );
        wp_set_auth_cookie( $wp_user->ID, true, false );
        update_user_caches( $wp_user );
    }

    /**
     * Store user avatar from facebook
     *
     * @since 3.4.7
     *
     * @param $user_id
     * @param $data
     *
     * @return void
     */
    private function store_avatar( $user_id, $data ) {
        if ( is_null( $this->access_token ) || $this->provider !== 'facebook' ) {
            return;
        }

        $image_url     = $data->photo_url . '&access_token=' . $this->access_token;
        $profile_image = file_get_contents( $image_url );
        $headers       = [];

        array_map(
            function ( $key_value ) use ( &$headers ) {
                $arr_split           = preg_split( '/:/', $key_value, 2 );
                $headers[ $arr_split[0] ] = $arr_split && ! empty( $arr_split[1] ) ? $arr_split[1] : '';
            }, $http_response_header
        );

        $file_name      = pathinfo( $headers['Location'], PATHINFO_FILENAME );
        $file_extension = explode( '/', $headers['Content-Type'] )[1];
        $hash           = wp_hash( time() );
        $hash           = substr( $hash, 0, 8 );

        $file_name = $data->identifier . '-' . $hash . '.' . $file_extension;
        $file_path = wp_upload_dir()['path'] . '/' . $file_name;

        file_put_contents( $file_path, $profile_image );

        $attachment = [
            'post_author'    => $user_id,
            'post_mime_type' => $headers['Content-Type'],
            'post_title'     => $file_name,
            'post_content'   => '',
            'post_status'    => 'inherit',
            'comment_status' => 'closed',
        ];

        $attach_id    = wp_insert_attachment( $attachment, $file_path );
        $imagenew     = get_post( $attach_id );
        $fullsizepath = get_attached_file( $imagenew->ID );

        if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $attach_data = wp_generate_attachment_metadata( $attach_id, $fullsizepath );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        update_user_meta( $user_id, 'user_avatar', $attach_id );
    }

    /**
     * Convert Pascel to snake case
     *
     * @since 3.4.7
     *
     * @param $input
     *
     * @return string
     */
    public function camel_to_snake_case( $input ) {
        preg_match_all( '!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches );
        $ret = $matches[0];
        foreach ( $ret as &$match ) {
            $match = $match === strtoupper( $match ) ? strtolower( $match ) : lcfirst( $match );
        }
        return implode( '_', $ret );
    }
}

$wpuf_social_login = WPUF_Social_Login::init();
