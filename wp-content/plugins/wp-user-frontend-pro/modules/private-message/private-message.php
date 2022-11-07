<?php
/*
Plugin Name: Private Message
Plugin URI: https://wedevs.com/docs/wp-user-frontend-pro/modules/private-messaging/
Thumbnail Name: message.gif
Description: User to user message from Frontend
Version: 1.0
Author: weDevs
Author URI: http://wedevs.com/
License: GPL2
*/

/**
 * Copyright (c) 2014 weDevs ( email: info@wedevs.com ). All rights reserved.
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
if ( !defined( 'ABSPATH' ) ) exit;

define( 'WPUF_PM_DIR', plugins_url('/', __FILE__) );

/**
 * WPUF_Private_Message class
 *
 * @class WPUF_Private_Message The class that holds the entire WPUF_Private_Message plugin
 */
class WPUF_Private_Message {

    /**
     * Constructor for the WPUF_Private_Message class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses add_filter()
     * @uses add_action()
     */
    public function __construct() {
        $this->includes();
        $this->init_classes();

        add_filter( 'wpuf_account_sections', array( $this, 'user_message_menu' ), 99 );
        add_action( 'wpuf_account_content_message', array( $this, 'user_message_section' ), 99, 2 );
    }

    /**
     * Includes all required classes
     *
     * @return void
     */
    public function includes() {
        require_once dirname( __FILE__ ) . '/class-conversation.php';
        require_once dirname( __FILE__ ) . '/class-private-message-ajax.php';
    }

    /**
     * Instantiate required classes
     *
     * @return void
     */
    public function init_classes() {
        new WPUF_Private_Message_Ajax();
        new WPUF_Conversation();
    }

    /**
     * Initializes the WPUF_Private_Message() class
     *
     * Checks for an existing WPUF_Private_Message() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new WPUF_Private_Message();
        }

        return $instance;
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public static function activate() {
        global $wpdb;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            if ( ! empty($wpdb->charset ) ) {
                $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
            }

            if ( ! empty($wpdb->collate ) ) {
                $collate .= " COLLATE $wpdb->collate";
            }
        }

        $table_schema = array(
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpuf_message` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `from` int(11) NOT NULL,
                `to` int(11) NOT NULL,
                `message` longtext,
                `status` BIT NOT NULL DEFAULT 0,
                `from_del` BIT NOT NULL DEFAULT 0,
                `to_del` BIT NOT NULL DEFAULT 0,
                `created` datetime NOT NULL,
                PRIMARY KEY (`id`),
                key `from` (`from`),
                key `to` (`to`)
            ) $collate;",
        );

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        foreach ( $table_schema as $table ) {
            dbDelta( $table );
        }
    }

    /**
     * Check user Analytics info exist or not with post
     *
     * @param array  $sections
     *
     * @return array
     */
    function user_message_menu( $sections ) {
        $sections = array_merge( $sections , [ 'message' => __( 'Message', 'wpuf-pro' ) ] );
        return $sections;
    }

    /**
     * Check user Analytics info exist or not with post
     *
     * @param array  $sections
     * @param string  $current_section
     *
     * @return boolean
     */
    function user_message_section( $sections, $current_section ) {
        $this->enqueue_scripts();

        add_action( 'wp_footer',  array( $this, 'render_user_list' ) );

        require_once dirname( __FILE__ ) . '/templates/message.php';
    }

    private function enqueue_scripts() {
        // @todo: NEED OPTIMIZATION
        $prefix = '';
        wp_register_script( 'wpuf-vue', WPUF_ASSET_URI . '/vendor/vue/vue' . $prefix . '.js', array(), WPUF_VERSION, true );
        wp_register_script( 'wpuf-vuex', WPUF_ASSET_URI . '/vendor/vuex/vuex' . $prefix . '.js', array( 'wpuf-vue' ), WPUF_VERSION, true );
        wp_register_script( 'wpuf-vue-router', WPUF_ASSET_URI . '/vendor/vue-router/vue-router' . $prefix . '.js', array( 'wpuf-vue' ), WPUF_VERSION, true );

        wp_enqueue_style( 'wpuf-private-message', WPUF_PM_DIR . '/assets/css/frontend.css', [], false );
        wp_enqueue_script( 'wpuf-private-message', WPUF_PM_DIR . '/assets/js/frontend.js', ['jquery', 'wpuf-vue', 'wpuf-vuex', 'wpuf-vue-router'], false, true );
        wp_enqueue_script( 'wpuf-private-message-script', WPUF_PM_DIR . '/assets/js/script.js', ['jquery'], false, true );

        wp_localize_script( 'wpuf-private-message', 'wpufPM', [
            'ajaxurl' => admin_url( 'admin-ajax.php' )
        ] );
    }

    /**
     * Render user list in the modal
     *
     * @return void
     */
    public function render_user_list() {
        include dirname( __FILE__ ) . '/templates/modal.php';
    }

} // WPUF_Private_Message

$wpuf_ua = WPUF_Private_Message::init();
wpuf_register_activation_hook( __FILE__, array( 'WPUF_Private_Message', 'activate' ) );
