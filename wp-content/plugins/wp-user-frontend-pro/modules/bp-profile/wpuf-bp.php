<?php
/*
Plugin Name: BuddyPress Profile
Plugin URI: https://wedevs.com/docs/wp-user-frontend-pro/modules/buddypress-profile-integration/
Thumbnail Name: wpuf-buddypress.png
Description: Register and upgrade user profiles and sync data with BuddyPress
Version: 0.3
Author: Tareq Hasan
Author URI: http://tareq.wedevs.com/
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
 * WPUF_BP_Profile class
 *
 * @class WPUF_BP_Profile The class that holds the entire WPUF_BP_Profile plugin
 */
class WPUF_BP_Profile {

    /**
     * Constructor for the WPUF_BP_Profile class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
        add_action( 'wpuf_profile_form_tab', array( $this, 'bp_settings_tab_head' ) );
        add_action( 'wpuf_profile_form_tab_content', array( $this, 'bp_settings_content' ) );

        add_action( 'save_post', array( $this, 'save_form_settings' ), 1, 2 );

        add_action( 'wpuf_update_profile', array( $this, 'on_profile_update' ), 10, 2 );
        add_action( 'wpuf_after_register', array( $this, 'on_user_registration' ), 10, 2 );
    }

    /**
     * Initializes the WPUF_BP_Profile() class
     *
     * Checks for an existing WPUF_BP_Profile() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new WPUF_BP_Profile();
        }

        return $instance;
    }

    public function bp_settings_tab_head() {
        ?>
        <a href="#wpuf-metabox-buddypress" class="nav-tab" id="wpuf_buddypress-tab"><?php esc_attr_e( 'BuddyPress', 'wpuf-pro' ); ?></a>
        <?php
    }

    public function bp_settings_content() {
        global $post, $wpdb;

        $this->bp_fields = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}bp_xprofile_fields WHERE parent_id = 0" );

        $form_settings = wpuf_get_form_settings( $post->ID );
        $this->bp_settings = isset( $form_settings['_wpuf_bp_mapping'] ) ? $form_settings['_wpuf_bp_mapping'] : '';
        $wpuf_fields       = wpuf_get_form_fields( $post->ID );

        $excluded          = apply_filters( 'wpuf_bp_settings_content_excluded', array( 'user_login' ) );
        $allowed_type      = apply_filters( 'wpuf_bp_settings_content_allowed_types', array( 'text', 'numeric_text', 'email', 'textarea', 'date', 'radio', 'select', 'multiselect', 'checkbox', 'country_list', 'url', 'column_field' ) );

        $user_profile = is_multisite() ? admin_url( 'network/users.php?page=bp-profile-setup' ) : admin_url( 'users.php?page=bp-profile-setup' );
        ?>
        <div id="wpuf-metabox-buddypress" class="group">

            <?php if ( $wpuf_fields ) { ?>
                <table class="form-table">
                    <tbody>
                        <?php
                        foreach ( $wpuf_fields as $index => $field ) {
                            if ( $field['input_type'] === 'password' ) {
                                continue;
                            }

                            if ( isset( $field['name'] ) && in_array( $field['name'], $excluded, true ) ) {
                                continue;
                            }

                            if ( ! in_array( $field['input_type'], $allowed_type, true ) ) {
                                continue;
                            }

                            ?>
                            <?php
                            if ( $field['input_type'] === 'column_field' ) {
                                $i         = 1;
                                $max_count = count( $field['inner_fields'] );

                                for ( $i; $i <= $max_count; $i ++ ) {
                                    if ( empty( $field['inner_fields'][ 'column-' . $i ][0]['label'] ) ) {
                                        continue;
                                    }
                                    ?>
                                    <tr>
                                        <th><?php echo $field['inner_fields'][ 'column-' . $i ][0]['label']; ?></th>
                                        <td>
                                            <?php echo $this->bp_get_dropdown( $field['inner_fields'][ 'column-' . $i ][0]['name'] ); ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <th><?php echo $field['label']; ?></th>
                                    <td>
                                        <?php echo $this->bp_get_dropdown( $field['name'] ); ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
                <p><a class="button" href="<?php echo $user_profile; ?>" target="_blank"><?php esc_attr_e( 'BuddyPress profile fields &rarr;', 'wpuf-pro' ); ?></a></p>
            <?php } else { ?>

                <?php esc_attr_e( 'No fields found in the form. Add fields first and update the form.', 'wpuf-pro' ); ?>

            <?php } ?>
        </div>
        <?php
    }

    public function bp_get_dropdown( $index, $field_id = false ) {
        $dropdown = '<select name="wpuf_settings[_wpuf_bp_mapping][' . $index . ']">';
        $dropdown .= '<option value="-1">' . __( '-- Select --', 'wpuf-pro' ) . '</option>';

        if ( $this->bp_fields ) {
            foreach ( $this->bp_fields as $field ) {
                $selected = '';

                if ( isset( $this->bp_settings[ $index ] ) && $this->bp_settings[ $index ] === $field->id ) {
                    $selected = ' selected="selected"';
                }

                $dropdown .= '<option value="' . $field->id . '"' . $selected . '>' . $field->name . '</option>';
            }
        }

        $dropdown .= '</select>';

        return $dropdown;
    }

    public function save_form_settings( $post_id, $post ) {
        if ( ! isset( $_REQUEST['form_data'] ) ) {
            return;
        }

        $form_data = array();
        //phpcs:ignore
        parse_str( wp_unslash( $_REQUEST['form_data'] ), $form_data );

        if ( ! isset( $form_data['wpuf_settings']['_wpuf_bp_mapping'] ) ) {
            return;
        }

        // Is the user allowed to edit the post or page?
        if ( ! current_user_can( 'edit_post', $post->ID ) ) {
            return $post->ID;
        }

        update_post_meta( $post->ID, 'wpuf_bp_form', $form_data['wpuf_settings']['_wpuf_bp_mapping'] );
    }

    public function update_user_data( $user_id, $form_id ) {
        $fields = get_post_meta( $form_id, 'wpuf_bp_form', true );

        if ( $fields && is_array( $fields ) ) {
            foreach ( $fields as $input_name => $xprofile_field_id ) {
                if ( $xprofile_field_id !== '-1' && isset( $_REQUEST[ $input_name ] ) ) {
                    $field_type = BP_XProfile_Field::get_type( $xprofile_field_id );
                    //phpcs:ignore
                    $value  = isset( $_REQUEST[ $input_name ] ) ?  $_REQUEST[ $input_name ] : '';
                    if ( is_array( $value ) ) {
                        $value = array_map( 'sanitize_text_field', $value );
                    } else {
                        $value = sanitize_text_field( $value );
                    }

                    if ( 'datebox' === $field_type ) {
                        $date  = isset( $_REQUEST[ $input_name ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $input_name ] ) ) : gmdate( 'Y-m-d' );
                        $date  = str_replace( '/', '-', $date );
                        $value = gmdate( 'Y-m-d H:i:s', strtotime( $date ) );
                    }
                    xprofile_set_field_data( $xprofile_field_id, $user_id, $value );
                }
            }
        }
    }

    public function on_profile_update( $user_id, $form_id ) {
        $this->update_user_data( $user_id, $form_id );
    }

    public function on_user_registration( $user_id, $form_id ) {
        $this->update_user_data( $user_id, $form_id );

        if ( function_exists( 'bp_activity_add' ) ) {
            bp_activity_add(
                array(
                    'user_id'   => $user_id,
                    'component' => 'members',
                    'type'      => 'new_member',
                )
            );
        }
    }

}

function wpuf_bp_init() {

    //check dependency for wp_user_frontend
    if ( ! class_exists( 'WP_User_Frontend' ) ) {
        return;
    }

    //check dependency for buddypress
    if ( ! class_exists( 'BuddyPress' ) ) {
        return;
    }

    $wpuf_bp = WPUF_BP_Profile::init();
}

// WPUF_BP_Profile
wpuf_bp_init();
