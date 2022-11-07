<?php

class WPUF_Admin_Posting_Profile extends WPUF_Admin_Posting {

    function __construct() {

        add_action( 'personal_options_update', array($this, 'save_fields') );
        add_action( 'edit_user_profile_update', array($this, 'save_fields') );
        add_action( 'wpuf_pro_frontend_form_update_user_meta', array( $this, 'update_vendor_profile_meta' ), 10, 2 );

        add_action( 'show_user_profile', array($this, 'render_form') );
        add_action( 'edit_user_profile', array($this, 'render_form') );

        add_action( 'personal_options_update', array($this, 'post_lock_update') );
        add_action( 'edit_user_profile_update', array($this, 'post_lock_update') );

        add_action( 'show_user_profile', array($this, 'post_lock_form') );
        add_action( 'edit_user_profile', array($this, 'post_lock_form') );

        add_action( 'wp_ajax_wpuf_delete_avatar', array($this, 'delete_avatar_ajax') );
        add_action( 'wp_ajax_nopriv_wpuf_delete_avatar', array($this, 'delete_avatar_ajax') );

        add_action( 'admin_enqueue_scripts', array($this, 'user_profile_scripts') );
    }

    /**
     * User profile edit related scripts
     *
     * @param  string $hook
     *
     * @return void
     */
    function user_profile_scripts( $hook ) {
        if ( ! in_array( $hook, array( 'profile.php', 'user-edit.php' ) ) ) {
            return;
        }

        wp_enqueue_script( 'jquery-ui-autocomplete' );

        $api_key = wpuf_get_option( 'gmap_api_key', 'wpuf_general' );

        if ( ! empty( $api_key ) ) {
            $scheme = is_ssl() ? 'https' : 'http';
            wp_enqueue_script( 'google-maps', $scheme . '://maps.google.com/maps/api/js?libraries=places&key=' . $api_key, [], null );
        }
    }

    function delete_avatar_ajax() {
        check_ajax_referer( 'wpuf_nonce' );

        $post_data = wp_unslash( $_POST );

        if ( isset( $post_data['user_id'] ) && !empty( $post_data['user_id'] ) ) {
            $user_id = $post_data['user_id'];
        } else {
            $user_id = get_current_user_id();
        }

        $avatar = get_user_meta( $user_id, 'user_avatar', true );

        if ( $avatar ) {
            if ( absint( $avatar ) > 0 ) {
                wp_delete_attachment( $avatar, true );
            } else {
                $upload_dir = wp_upload_dir();

                $full_url = str_replace( $upload_dir['baseurl'],  $upload_dir['basedir'], $avatar );

                if ( file_exists( $full_url ) ) {
                    unlink( $full_url );
                }
            }

            delete_user_meta( $user_id, 'user_avatar' );
        }

        die();
    }

    function get_role_name( $userdata ) {
        return reset( $userdata->roles );
    }

    function render_form( $userdata, $post_id = NULL, $preview = false ) {
        $option = get_option( 'wpuf_profile', array() );

        if ( !isset( $option['roles'][$this->get_role_name( $userdata )] ) || empty( $option['roles'][$this->get_role_name( $userdata )] ) ) {
            return;
        }

        $form_id = $option['roles'][$this->get_role_name( $userdata )];
        list($post_fields, $taxonomy_fields, $custom_fields) = $this->get_input_fields( $form_id );
        if ( !$custom_fields ) {
            return;
        }
        ?>

        <input type="hidden" name="wpuf_cf_update" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
        <input type="hidden" name="wpuf_cf_form_id" value="<?php echo $form_id; ?>" />

        <table class="form-table wpuf-cf-table">
            <tbody>

                <script type="text/javascript">
                    if ( typeof wpuf_conditional_items === 'undefined' ) {
                        wpuf_conditional_items = [];
                    }

                    if ( typeof wpuf_plupload_items === 'undefined' ) {
                        wpuf_plupload_items = [];
                    }

                    if ( typeof wpuf_map_items === 'undefined' ) {
                        wpuf_map_items = [];
                    }
                </script>

                <?php
                    $atts = array();
                    wpuf()->fields->render_fields( $custom_fields, $form_id, $atts,$type = 'user', $userdata->ID );
                ?>
            </tbody>
        </table>
        <?php
        $this->scripts_styles();
    }

    function save_fields( $user_id ) {
        global $post;
        !is_object( $post ) ? $post = new stdClass():'';
        !isset ( $post->ID ) ? $post->ID = '' : '';

        if ( !isset( $_POST['wpuf_cf_update'] ) ) {
            return $post->ID;
        }

        if ( !wp_verify_nonce( $_POST['wpuf_cf_update'], plugin_basename( __FILE__ ) ) ) {
            return $post->ID;
        }

        list( $post_fields, $taxonomy_fields, $custom_fields ) = self::get_input_fields( $_POST['wpuf_cf_form_id'] );
        WPUF_Frontend_Form_Profile::update_user_meta( $custom_fields, $user_id );
    }

    /**
     * Adds the postlock form in users profile
     *
     * @param object $profileuser
     */
    function post_lock_form( $profileuser ) {

        $current_user      = new WPUF_User( $profileuser );
        $wpuf_subscription = $current_user->subscription();
        $post_locked       = $current_user->post_locked();
        $lock_reason       = $current_user->lock_reason();
        $edit_post_locked  = $current_user->edit_post_locked();
        $edit_lock_reason  = $current_user->edit_post_lock_reason();

        if ( is_admin() && current_user_can( 'edit_users' ) ) {
            $select = ( $post_locked == true ) ? 'yes' : 'no';
            $edit_post_select = ( $edit_post_locked == true ) ? 'yes' : 'no';
            ?>

            <h3><?php _e( 'WPUF Post Lock', 'wpuf-pro' ); ?></h3>
            <table class="form-table">
                <tr>
                    <th><label for="wpuf-post-lock"><?php _e( 'Lock Post:', 'wpuf-pro' ); ?> </label></th>
                    <td>
                        <select name="wpuf_postlock" id="wpuf-post-lock">
                            <option value="no"<?php selected( $select, 'no' ); ?>>No</option>
                            <option value="yes"<?php selected( $select, 'yes' ); ?>>Yes</option>
                        </select>
                        <span class="description"><?php _e( 'Lock user from creating new post.', 'wpuf-pro' ); ?></span></em>
                    </td>
                </tr>
                <tr>
                    <th><label for="wpuf_lock_cause"><?php _e( 'Lock Reason:', 'wpuf-pro' ); ?> </label></th>
                    <td>
                        <input type="text" name="wpuf_lock_cause" id="wpuf_lock_cause" class="regular-text" value="<?php echo esc_attr( $lock_reason ); ?>" />
                    </td>
                </tr>

                <tr>
                    <th><label for="post-lock"><?php _e( 'Lock Edit Post:', 'wpuf-pro' ); ?> </label></th>
                    <td>
                        <select name="wpuf_edit_postlock" id="edit-post-lock">
                            <option value="no"<?php selected( $edit_post_select, 'no' ); ?>>No</option>
                            <option value="yes"<?php selected( $edit_post_select, 'yes' ); ?>>Yes</option>
                        </select>
                        <span class="description"><?php _e( 'Lock user from editing post.', 'wpuf-pro' ); ?></span></em>
                    </td>
                </tr>
                <tr>
                    <th><label for="post-lock"><?php _e( 'Edit Post Lock Reason:', 'wpuf-pro' ); ?> </label></th>
                    <td>
                        <input type="text" name="wpuf_edit_post_lock_cause" id="wpuf_edit_post_lock_cause" class="regular-text" value="<?php echo esc_attr( $edit_lock_reason ); ?>" />
                    </td>
                </tr>
            </table>
            <?php
        }
    }

    /**
     * Update user profile lock
     *
     * @param int $user_id
     */
    function post_lock_update( $user_id ) {
        if ( is_admin() && current_user_can( 'edit_users' ) ) {
            update_user_meta( $user_id, 'wpuf_postlock', $_POST['wpuf_postlock'] );
            update_user_meta( $user_id, 'wpuf_lock_cause', $_POST['wpuf_lock_cause'] );
            update_user_meta( $user_id, 'wpuf_edit_postlock', $_POST['wpuf_edit_postlock'] );
            update_user_meta( $user_id, 'wpuf_edit_post_lock_cause', $_POST['wpuf_edit_post_lock_cause'] );
        }
    }

    /**
     * Update vendor user meta in admin user edit page
     *
     * If we show any profile/registration form in admin user edit page,
     * then we may need to update certain meta data in a customized way.
     * For example, shopurl for Dokan Vendor Registration form need to handle
     * separately, since shopurl meta doesn't actually works as the vendor shop
     * url. We need to set it as user_nicename.
     *
     * @since 3.3.0
     *
     * @param int   $user_id
     * @param array $postdata
     *
     * @return void
     */
    public function update_vendor_profile_meta( $user_id, $postdata ) {
        global $pagenow;

        if ( 'user-edit.php' !== $pagenow || ! current_user_can( 'edit_users' ) ) {
            return;
        }

        /**
         * For store url, we don't need to update the nicename here. Let Dokan handle
         * that part. Otherwise, we need to remove the dokan action that hooked in
         * edit_user_profile_update action.
         *
         * When WPUF form displays in user edit page, we have duplicate inputs for shop url,
         * one from WPUF and one from Dokan. User may change either the WPUF input or the Dokan
         * input. We need to handle both cases. The condition here gives priority to WPUF input,
         * so that even user tries to set different name in both WPUF and Dokan inputs in same submit,
         * only WPUF input works, not Dokan input.
         */
        if ( isset( $postdata['shopurl'] ) && user_can( $user_id, 'seller' ) ) {
            $userdata          = get_userdata( $user_id );
            $current_store_url = $userdata->user_nicename;
            $shop_url          = sanitize_text_field( $postdata['shopurl'] );
            $dokan_store_url   = sanitize_text_field( $postdata['dokan_store_url'] );

            if ( $shop_url !== $current_store_url ) {
                $existing_user = get_user_by( 'slug', $shop_url );

                if ( $existing_user instanceof WP_User ) {
                    update_user_meta( $user_id, 'shopurl', $dokan_store_url );
                } else {
                    $_POST['dokan_store_url'] = $shop_url;
                }
            } else if ( $dokan_store_url !== $current_store_url ) {
                update_user_meta( $user_id, 'shopurl', $dokan_store_url );
            }
        }
    }
}
