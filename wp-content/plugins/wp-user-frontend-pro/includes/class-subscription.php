<?php

/**
 * Pro Subscription Class
 *
 * @package WPUF\Pro
 */
class WPUF_Pro_Subscription {
    const FORMAT = 'Y-m-d h:i:s';

    public function __construct() {
        add_action( 'wpuf_admin_subscription_detail', array( $this, 'recurring_payment' ), 10, 4 );
        add_action( 'wpuf_admin_subscription_post_restriction', array( $this, 'post_rollback' ), 10 );
        add_action( 'wpuf_update_subscription_pack', array( $this, 'update_subcription_data' ), 10, 2 );
        add_filter( 'wpuf_get_subscription_meta', array( $this, 'get_subscription_metadata' ), 10, 2 );
        add_filter( 'wpuf_new_subscription', array( $this, 'set_subscription_meta_to_user' ), 10, 4 );

        //Rollback counter for CPT, less priority needed due to unstick on trash
        add_action( 'wp_trash_post', [ $this, 'restore_post_numbers' ], 1 );

        //subscription notification mail hooks
        add_action( 'wpuf_remove_expired_post_hook', array( $this, 'wpuf_send_subs_notification' ) );
        add_filter( 'wpuf_mail_options', array( $this, 'subs_notification_mail_options' ) );
    }

    public function recurring_payment( $sub_meta, $hidden_recurring_class, $hidden_trial_class, $obj ) {
        ?>

        <tr valign="top">
            <th><label><?php esc_attr_e( 'Recurring', 'wpuf-pro' ); ?></label></th>
            <td>
                <label for="wpuf-recuring-pay">
                    <input type="checkbox" <?php checked( $sub_meta['recurring_pay'], 'yes' ); ?> size="20" style="" id="wpuf-recuring-pay" value="yes" name="recurring_pay" />
                    <?php esc_attr_e( 'Enable Recurring Payment', 'wpuf-pro' ); ?>
                </label>
            </td>
        </tr>

        <tr valign="top" class="wpuf-recurring-child" style="display: <?php echo $hidden_recurring_class; ?>;">
            <th><label for="wpuf-billing-cycle-number"><?php esc_attr_e( 'Billing cycle:', 'wpuf-pro' ); ?></label></th>
            <td>
                <select id="wpuf-billing-cycle-number" name="billing_cycle_number">
                    <?php echo $obj->lenght_type_option( $sub_meta['billing_cycle_number'] ); ?>
                </select>

                <select id="cycle_period" name="cycle_period">
                    <?php echo $obj->option_field( $sub_meta['cycle_period'] ); ?>
                </select>
                <div><span class="description"></span></div>
            </td>
        </tr>

        <tr valign="top" class="wpuf-recurring-child" style="display: <?php echo $hidden_recurring_class; ?>;">
            <th><label for="wpuf-billing-limit"><?php esc_attr_e( 'Billing cycle stop', 'wpuf-pro' ); ?></label></td>
                <td>
                    <select id="wpuf-billing-limit" name="billing_limit">
                        <option value=""><?php esc_attr_e( 'Never', 'wpuf-pro' ); ?></option>
                        <?php echo $obj->lenght_type_option( $sub_meta['billing_limit'] ); ?>
                    </select>
                    <div><span class="description"><?php esc_attr_e( 'After how many cycles should billing stop?', 'wpuf-pro' ); ?></span></div>
                </td>
            </th>
        </tr>

        <tr valign="top" class="wpuf-recurring-child" style="display: <?php echo $hidden_recurring_class; ?>;">
            <th><label for="wpuf-trial-status"><?php esc_attr_e( 'Trial', 'wpuf-pro' ); ?></label></th>
            <td>
                <label for="wpuf-trial-status">
                    <input type="checkbox" size="20" style="" id="wpuf-trial-status" <?php checked( $sub_meta['trial_status'], 'yes' ); ?> value="yes" name="trial_status" />
                    <?php esc_attr_e( 'Enable trial period', 'wpuf-pro' ); ?>
                </label>
            </td>
        </tr>

        <tr class="wpuf-trial-child" style="display: <?php echo $hidden_trial_class; ?>;">
            <th><label for="wpuf-trial-duration"><?php esc_attr_e( 'Trial period', 'wpuf-pro' ); ?></label></th>
            <td>
                <select id="wpuf-trial-duration" name="trial_duration">
                    <?php echo $obj->lenght_type_option( $sub_meta['trial_duration'] ); ?>
                </select>
                <select id="trial-duration-type" name="trial_duration_type">
                    <?php echo $obj->option_field( $sub_meta['trial_duration_type'] ); ?>
                </select>
                <span class="description"><?php esc_attr_e( 'Define the trial period', 'wpuf-pro' ); ?></span>
            </td>
        </tr>
        <?php
    }

    public function post_rollback( $sub_meta ) {
        ?>
        <tr valign="top">
            <th><label><?php esc_attr_e( 'Post Number Rollback', 'wpuf-pro' ); ?></label></th>
            <td>
                <label>
                    <input type="checkbox" size="20" style="" id="wpuf-postnum-rollback" <?php checked( $sub_meta['postnum_rollback_on_delete'], 'yes' ); ?> value="yes" name="postnum_rollback_on_delete" />
                    <?php esc_attr_e( 'If enabled, number of posts will be restored if the post is deleted.', 'wpuf-pro' ); ?>
                </label>
            </td>
        </tr>
        <?php
    }

    /**
     * Update the meta data of subscription pack
     */
    public function update_subcription_data( $subscription_id, $post ) {
        update_post_meta( $subscription_id, 'postnum_rollback_on_delete', ( isset( $post['postnum_rollback_on_delete'] ) ? $post['postnum_rollback_on_delete'] : '' ) );
    }

    /**
     * Get subscription meta data
     */
    public function get_subscription_metadata( $meta, $subscription_id ) {
        $meta['postnum_rollback_on_delete'] = get_post_meta( $subscription_id, 'postnum_rollback_on_delete', true );

        return $meta;
    }


    /**
     * Restore number of posts allowed to post when the post is deleted
     */
    public function restore_post_numbers( $post_id ) {
        global $current_user;

        $post_to_delete = get_post( $post_id );

        if ( 'draft' === $post_to_delete->post_status || 'pending' === $post_to_delete->post_status ) {
            return;
        }

        $post_type      = $post_to_delete->post_type;
        $stickies       = get_option( 'sticky_posts' );
        $is_featured    = in_array( intval( $post_id ), $stickies, true );

        if ( in_array( 'administrator', $current_user->roles, true ) || (int) get_post_field( 'post_author', $post_id ) === $current_user->ID ) {
            $user_subpack_data = get_user_meta( $post_to_delete->post_author, '_wpuf_subscription_pack', true );

            if ( isset( $user_subpack_data['postnum_rollback_on_delete'] ) && $user_subpack_data['postnum_rollback_on_delete'] === 'yes' ) {
                $main_subpack_data = WPUF_Subscription::get_subscription( $user_subpack_data['pack_id'] );
                if ( isset( $main_subpack_data->meta_value['post_type_name'][ $post_type ] )
                    && isset( $user_subpack_data['posts'][ $post_type ] )
                    && $user_subpack_data['posts'][ $post_type ] < $main_subpack_data->meta_value['post_type_name'][ $post_type ] ) {
                    $user_subpack_data['posts'][ $post_type ]++;
                }

                if ( $is_featured && isset( $main_subpack_data->meta_value['_total_feature_item'] )
                    && isset( $user_subpack_data['total_feature_item'] )
                    && $user_subpack_data['total_feature_item'] < $main_subpack_data->meta_value['_total_feature_item'] ) {
                    intval( $user_subpack_data['total_feature_item']++ );
                }

                update_user_meta( $post_to_delete->post_author, '_wpuf_subscription_pack', $user_subpack_data );
            }
        }
    }


    /**
     * Update meta of user from the data of pack he has been assigned to
     *
     * @param $user_meta
     * @param $user_id
     * @param $pack_id
     * @param $recurring
     *
     * @return mixed
     */
    public static function set_subscription_meta_to_user( $user_meta, $user_id, $pack_id, $recurring ) {
        $subscription = WPUF_Subscription::get_subscription( $pack_id );
        $user_meta['postnum_rollback_on_delete'] = isset( $subscription->meta_value['postnum_rollback_on_delete'] ) ? $subscription->meta_value['postnum_rollback_on_delete'] : '';

        return $user_meta;
    }

    /**
     * Send subscription notification
     */
    public function wpuf_send_subs_notification() {
        $users = get_users(
            array(
                'meta_key' => '_wpuf_subscription_pack',
            )
        );

        $date_before      = wpuf_get_option( 'pre_sub_notification_date', 'wpuf_mails', 7 );
        $date_after       = wpuf_get_option( 'post_sub_notification_date', 'wpuf_mails', 3 );
        $enabled_sub_noti = wpuf_get_option( 'enable_subs_notification', 'wpuf_mails', 'off' );

        foreach ( $users as $user ) {
            $sub = get_user_meta( $user->ID, '_wpuf_subscription_pack', true );

            if ( $enabled_sub_noti === 'on' ) {
                $pack_id       = $this->get_transaction_var( $user->ID, 'pack_id' );
                $sub_meta      = get_post_meta( $pack_id );
                $duration      = ! empty( $sub_meta['_expiration_number'] ) && ! empty( $sub_meta['_expiration_number'][0] ) ? $sub_meta['_expiration_number'][0] : 0;
                $duration_type = ! empty( $sub_meta['_expiration_period'] ) && ! empty( $sub_meta['_expiration_period'][0] ) ? $sub_meta['_expiration_period'][0] : 'day';

                $start_date      = $this->get_transaction_var( $user->ID, 'created' );
                $map_to_interval = [
                    'day'   => 'D',
                    'week'  => 'W',
                    'month' => 'M',
                    'year'  => 'Y',
                ];

                $interval     = new DateInterval( "P${duration}${map_to_interval[$duration_type]}" );
                $end_date     = new DateTimeImmutable( $start_date );
                $end_date     = $end_date->add( $interval );

                $current_time = new DateTimeImmutable();
                $curr_time    = $current_time->format( self::FORMAT );
                $time_diff    = $current_time->diff( $end_date );

                if ( ! empty( $sub ) && ( $sub !== 'Cancel' || $sub !== 'cancel' ) && ( $curr_time < $end_date->format( self::FORMAT ) ) && ( $time_diff->days <= $date_before ) && get_user_meta( $user->ID, 'wpuf_pre_sub_exp', true ) !== 'sent' ) {
                    if ( empty( $sub['expire'] ) || $sub['expire'] === 'unlimited' ) {
                        continue;
                    }

                    $this->wpuf_pre_sub_exp_notification( $user, $sub );
                    update_user_meta( $user->ID, 'wpuf_pre_sub_exp', 'sent' );
                }

                if ( ( ! empty( $sub ) && ( $sub === 'Cancel' || $sub === 'cancel' ) && $curr_time > $end_date->format( self::FORMAT ) ) && ( $time_diff->days >= $date_after ) && get_user_meta( $user->ID, 'wpuf_post_sub_exp', true ) !== 'sent' ) {
                    $this->wpuf_post_sub_exp_notification( $user, $sub );
                    update_user_meta( $user->ID, 'wpuf_post_sub_exp', 'sent' );
                }
            }
        }
    }

    /**
     * Pre-subscription expiration notification mail
     */
    public function wpuf_pre_sub_exp_notification( $user, $sub ) {
        $to = $user->user_email;

        $subj       = wpuf_get_option( 'pre_sub_exp_subject', 'wpuf_mails' );
        $text_body  = wpautop( wpuf_get_option( 'pre_sub_exp_body', 'wpuf_mails' ) );
        $text_body  = $this->prepare_mail_body( $user, $sub, $text_body );
        $text_body  = get_formatted_mail_body( $text_body, $subj );

        $headers = 'Content-Type: text/html; charset=UTF-8';

        wp_mail( $to, $subj, $text_body, $headers );
    }

    /**
     * Post-subscription expiration notification mail
     *
     * @param $user
     * @param $sub
     */
    public function wpuf_post_sub_exp_notification( $user, $sub ) {
        $to = $user->user_email;

        $subj       = wpuf_get_option( 'post_sub_exp_subject', 'wpuf_mails' );
        $text_body  = wpautop( wpuf_get_option( 'post_sub_exp_body', 'wpuf_mails' ) );
        $text_body  = $this->prepare_mail_body( $user, $sub, $text_body );
        $text_body  = get_formatted_mail_body( $text_body, $subj );

        $headers = 'Content-Type: text/html; charset=UTF-8';

        wp_mail( $to, $subj, $text_body, $headers );
    }

    public function subs_notification_mail_options( $mail_options ) {
        $new_options = array(
            array(
                'name'    => 'subscription_setting',
                'label'   => __( '<span class="dashicons dashicons-money"></span> Subscription', 'wpuf-pro' ),
                'type'    => 'html',
                'class'   => 'subscription-setting',
            ),
            array(
                'name'     => 'enable_subs_notification',
                'class'    => 'wpuf-sub-notification-enabled subscription-setting-option',
                'label'    => __( 'Subscription Notification', 'wpuf-pro' ),
                'desc'     => __( 'Enable Subscription Notification.', 'wpuf-pro' ),
                'default'  => 'off',
                'type'     => 'checkbox',
            ),
            array(
                'name'     => 'pre_sub_notification_date',
                'class'    => 'pre-sub-exp-notify-date subscription-setting-option',
                'label'    => __( 'Send Notification Before', 'wpuf-pro' ),
                'desc'     => __( 'Send Pre-subscription expiration notice before days', 'wpuf-pro' ),
                'default'  => 7,
                'type'     => 'number',
            ),
            array(
                'name'     => 'post_sub_notification_date',
                'class'    => 'post-sub-exp-notify-date subscription-setting-option',
                'label'    => __( 'Send Notification After', 'wpuf-pro' ),
                'desc'     => __( 'Send Post-subscription expiration notice after days', 'wpuf-pro' ),
                'default'  => 3,
                'type'     => 'number',
            ),
            array(
                'name'     => 'pre_sub_exp_subject',
                'class'    => 'pre-sub-exp-sub subscription-setting-option',
                'label'    => __( 'Subscription pre-expiration mail subject', 'wpuf-pro' ),
                'desc'     => __( 'This sets the subject of the emails sent to users before the subscription pack is expired.', 'wpuf-pro' ),
                'default'  => __( 'Your Subscription Pack is expiring!', 'wpuf-pro' ),
                'type'     => 'text',
            ),
            array(
                'name'     => 'pre_sub_exp_body',
                'class'    => 'pre-sub-exp-body subscription-setting-option',
                'label'    => __( 'Subscription pre-expiration mail body', 'wpuf-pro' ),
                //phpcs:ignore
                'desc'     => __( "This sets the body of the emails sent to users before the subscription pack is expired. <br><strong>You may use: </strong><code>%username%</code><code>%sub_pack_name%</code><code>%sub_expiration_date%</code><br><code>%sub_start_date%</code><code>%sub_end_date%</code><code>%sub_pack_price%</code><br>", 'wpuf-pro' ),
                'default'  => __( "Dear Subscriber, \r\n\r\nYour Subscription Pack is expiring! Please buy a new subscription pack.", 'wpuf-pro' ),
                'type'     => 'wysiwyg',
            ),
            array(
                'name'     => 'post_sub_exp_subject',
                'class'    => 'post-sub-exp-sub subscription-setting-option',
                'label'    => __( 'Subscription post-expiration mail subject', 'wpuf-pro' ),
                'desc'     => __( 'This sets the subject of the emails sent to users after the subscription pack is expired.', 'wpuf-pro' ),
                'default'  => __( 'Your Subscription Pack is expired!', 'wpuf-pro' ),
                'type'     => 'text',
            ),
            array(
                'name'     => 'post_sub_exp_body',
                'class'    => 'post-sub-exp-body subscription-setting-option',
                'label'    => __( 'Subscription post-expiration mail body', 'wpuf-pro' ),
                //phpcs:ignore
                'desc'     => __( "This sets the body of the emails sent to users after the subscription pack is expired. <br><strong>You may use: </strong><code>%username%</code><code>%sub_pack_name%</code><code>%sub_expiration_date%</code><br><code>%sub_start_date%</code><code>%sub_end_date%</code><code>%sub_pack_price%</code><br>", 'wpuf-pro' ),
                'default'  => __( "Dear Subscriber, \r\n\r\nYour Subscription Pack is expired! Please buy a new subscription pack.", 'wpuf-pro' ),
                'type'     => 'wysiwyg',
            ),
        );

        return array_merge( $mail_options, $new_options );
    }

    /**
     * Prepare mail-body using plcaeholder replace
     *
     * @since 3.4.7
     *
     * @param $user
     * @param $sub
     * @param $mail_body
     *
     * @return string|string[]
     */
    public function prepare_mail_body( $user, $sub_pack, $mail_body ) {
        global $wpdb;

        $sub_field_search = [ '%username%', '%sub_pack_name%', '%sub_expiration_date%', '%sub_start_date%', '%sub_end_date%', '%sub_pack_price%' ];

        $pack_id       = (int) $sub_pack['pack_id'];
        $sub           = get_post( $pack_id );
        $sub_meta      = get_post_meta( $pack_id );
        $pack_title    = $sub->post_title;

        $duration      = ! empty( $sub_meta['_expiration_number'] ) && ! empty( $sub_meta['_expiration_number'][0] ) ? $sub_meta['_expiration_number'][0] : 0;
        $duration_type = ! empty( $sub_meta['_expiration_period'] ) && ! empty( $sub_meta['_expiration_period'][0] ) ? $sub_meta['_expiration_period'][0] : 'days';
        $start_date    = $this->get_transaction_var( $user->ID, 'created' );

        if ( $start_date ) {
            $start_date = gmdate( 'l\,d F Y', strtotime( $start_date ) );
            $end_date   = gmdate( 'l\,d F Y', strtotime( $start_date . " + {$duration} {$duration_type}" ) );
        }

        $sub_field_replace = array(
            $user->user_login,
            $pack_title,
            $end_date, //expire
            $start_date, //expire - cycle
            $end_date, //expire -- from which days
            (float) $sub_meta['_billing_amount'],
        );

        return str_replace( $sub_field_search, $sub_field_replace, $mail_body );
    }

    /**
     * Get var from transaction table
     *
     * @param $user_id
     * @param $column
     *
     * @return string|void|null
     */
    public function get_transaction_var( $user_id, $column ) {
        if ( ! $user_id || ! $column ) {
            return;
        }

        global $wpdb;

        return $wpdb->get_var( $wpdb->prepare( "SELECT {$column} FROM {$wpdb->prefix}wpuf_transaction WHERE pack_id !=0 AND user_id=%s ORDER BY id DESC LIMIT 1 ", $user_id ) );//phpcs:ignore
    }
}
