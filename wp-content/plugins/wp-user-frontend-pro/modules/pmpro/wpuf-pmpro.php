<?php
/*
  Plugin Name: Paid Membership Pro Integration
  Plugin URI: https://wedevs.com/docs/wp-user-frontend-pro/modules/install-and-configure-pmpro-add-on-for-wpuf/
  Thumbnail Name: wpuf-pmpro.png
  Description: Membership Integration of WP User Frontend PRO with Paid Membership Pro
  Version: 0.2
  Author: Tareq Hasan
  Author URI: http://wedevs.com/
  License: GPL2
 */
/**
 * Copyright (c) 2013 Tareq Hasan (email: tareq@wedevs.com). All rights reserved.
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
 * WPUF_Pm_Pro class
 *
 * @class WPUF_Pm_Pro The class that holds the entire WPUF_Pm_Pro plugin
 */
class WPUF_Pm_Pro {

    const OPTION_ID = 'wpufpmpro';

    /**
     * Constructor for the WPUF_Pm_Pro class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses add_action()
     */
    public function __construct() {
        add_action( 'pmpro_membership_level_after_other_settings', array( $this, 'post_count_field_insert' ) );
        add_action( 'pmpro_save_membership_level', array( $this, 'post_count_field_save' ) );
        add_action( 'pmpro_after_change_membership_level', array( $this, 'set_user_membership' ), 10, 3 );
        // add_action( 'personal_options_update', array($this, 'profile_update_expiry'), 99 );
        // add_action( 'edit_user_profile_update', array($this, 'profile_update_expiry'), 99 );
    }

    /**
     * Initializes the WPUF_Pm_Pro() class
     *
     * Checks for an existing WPUF_Pm_Pro() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;
        if ( ! $instance ) {
            $instance = new WPUF_Pm_Pro();
        }
        return $instance;
    }

    /**
     * Helper function to retrieve the post count setting
     *
     * @param int $level_id
     * @return array|int
     */
    public function get_option( $level_id = false ) {
        $option = get_option( self::OPTION_ID, array() );
        if ( $level_id ) {
            return isset( $option[ $level_id ] ) ? $option[ $level_id ] : false;
        }
        return $option;
    }

    /**
     * Updates a post count level in options table
     *
     * @param int $level_id
     * @param int $post_count
     */
    public function update_level_count( $level_id, $post_count ) {
        $option            = $this->get_option();
        $option[ $level_id ] = $post_count;
        update_option( self::OPTION_ID, $option );
    }

    /**
     * Updates the post count when membership level form updates
     *
     * @uses `pmpro_save_membership_level` action hook
     * @param int $level_id
     */
    public function post_count_field_save( $level_id ) {
        $post_count = isset( $_REQUEST['wpuf_post_count'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpuf_post_count'] ) ) : 0;
        $pack_id    = isset( $_REQUEST['wpuf_pack_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpuf_pack_id'] ) ) : 0;
        $this->update_level_count( $level_id, $post_count );
        //remove meta from existing pack
        $old_pack = $this->wpuf_get_pack_id_by_lvl_id( $level_id );
        if ( $old_pack !== $pack_id ) {
            delete_post_meta( $old_pack, 'wpuf_pmpro_lvl_id' );
        }
        update_post_meta( $pack_id, 'wpuf_pmpro_lvl_id', $level_id );
    }

    /**
     * Shows the post count text field in the plugin membership level form
     *
     * @return void
     */
    public function post_count_field_insert() {
        $level_id   = isset( $_GET['edit'] ) ? sanitize_key( wp_unslash( $_GET['edit'] ) ) : 0;
        $level_id   = ( intval( $level_id ) < 0 ) ? 0 : intval( $level_id );
        $post_count = $level_id ? $this->get_option( $level_id ) : array();
        $sub        = WPUF_Subscription::init();
        $post_types = $sub->get_all_post_type();
        $all_subs   = $sub->get_subscriptions();
        $pack_id    = $this->wpuf_get_pack_id_by_lvl_id( $level_id );
        ?>
        <h3 class="topborder"><?php esc_html_e( 'WP User Frontend PRO', 'wpuf-pro' ); ?></h3>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row" valign="top"><label><?php esc_html_e( 'Select Sub Pack', 'wpuf-pro' ); ?>:</label></th>
                <td>
                    <select name="wpuf_pack_id" id="wpuf_pack">
                        <option value="" disabled
                        <?php
                        if ( $pack_id === 0 ) {
                            echo 'selected="selected"';}
                        ?>
                        ">Select Sub Pack</option>
                        <?php foreach ( $all_subs as $sub ) { ?>
                            <option value="<?php echo $sub->ID; ?>"
                                                      <?php
                                                        if ( $sub->ID === $pack_id ) {
                                                            echo 'selected="selected"';}
                                                        ?>
                            ><?php echo $sub->post_title; ?></option>
                        <?php } ?>
                    </select>
                    <span class="description">
                            <strong><?php esc_html_e( 'Must Select One Pack', 'wpuf-pro' ); ?></strong>
                    </span>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Set user membership when a membership level changes in
     * Paid Membership Pro plugin
     *
     * @param int $level_id
     * @param int $user_id
     */
    public function set_user_membership( $level_id, $user_id, $cancel_level ) {
        $user_level = pmpro_getMembershipLevelForUser( $user_id );
        if ( $user_level && ! $cancel_level ) {
            // Update expiry
            if ( ! empty( $user_level->expiration_number ) && ! empty( $user_level->expiration_number ) ) {
                $date_string = sprintf( '%s %s', $user_level->expiration_number, $user_level->expiration_period );
                $expire_date = gmdate( 'Y-m-d G:i:s', strtotime( $date_string ) );
            } else {
                $expire_date = 'unlimited';
            }

            $pack_id = $this->wpuf_get_pack_id_by_lvl_id( $level_id );
            // Update post count
            $membership = array(
                'pack_id'   => $pack_id,
                'posts'     => get_post_meta( $pack_id, '_post_type_name', true ),
                'status'    => 'completed',
                'expire'    => $expire_date,
                'recurring' => 'no',
            );
            update_user_meta( $user_id, '_wpuf_subscription_pack', $membership );
        } else {
            update_user_meta( $user_id, '_wpuf_subscription_pack', 'cancel' );
        }
    }

    /**
     * Update expiry time when user profile update from the admin panel
     *
     * @param int $user_id
     */
    public function profile_update_expiry( $user_id ) {
        $expires_year  = isset( $_REQUEST['expires_year'] ) ? intval( $_REQUEST['expires_year'] ) : 0;
        $expires_month = isset( $_REQUEST['expires_month'] ) ? intval( $_REQUEST['expires_month'] ) : 0;
        $expires_day   = isset( $_REQUEST['expires_day'] ) ? intval( $_REQUEST['expires_day'] ) : 0;

        if ( ! empty( $_REQUEST['expires'] ) ) {
            $expiration_date = $expires_year . '-' . $expires_month . '-' . $expires_day;
            $expiration_date = gmdate( 'Y-m-d G:i:s', strtotime( $expiration_date ) );
            update_user_meta( $user_id, 'wpuf_sub_validity', $expiration_date );
        }
    }

    /**
     * Get subs pack id using pmpro lvl id
     *
     * @since 3.4.7
     *
     * @param $level_id
     *
     * @return int
     */
    public function wpuf_get_pack_id_by_lvl_id( $level_id ) {
        $posts = get_posts(
            [
                'post_type'      => 'wpuf_subscription',
                'meta_query'     => [
                    [
                        'key' => 'wpuf_pmpro_lvl_id',
                        'value' => $level_id,
                        'compare' => '=',
                    ],
                ],
                'posts_per_page' => '1',
            ]
        );
        return $posts ? $posts[0]->ID : 0;
    }

}

// WPUF_Pm_Pro
$wpuf_pmpro = WPUF_Pm_Pro::init();
