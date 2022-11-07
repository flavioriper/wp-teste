<?php

/**
 * Post status reject, approve email notification class
 *
 * @since 3.4.7
 *
 * @package WP User Frontend
 */
class WPUF_Post_Status_Notification {
    private $user;
    /**
     * WPUF_Post_Status_Change constructor.
     */
    public function __construct() {
        add_filter( 'wpuf_settings_fields', [ $this, 'add_global_settings' ], 10, 1 );
        add_action( 'transition_post_status', [ $this, 'handle_post_status' ], 10, 3 );
    }

    /**
     * Global settings
     *
     * @param $settings_fields
     *
     * @since 3.4.7
     *
     * @return mixed
     */
    public function add_global_settings( $settings_fields ) {
        $settings_fields['wpuf_mails'][] = array(
            'name'    => 'approved_post_email',
            'label'   => __( '<span class="dashicons dashicons-saved"></span> Approved Post Email', 'wpuf-pro' ),
            'type'    => 'html',
            'class'   => 'approved-post-email',
        );

        $settings_fields['wpuf_mails'][] = array(
            'name'     => 'enable_post_approval_notification',
            'class'    => 'approved-post-email-option',
            'label'    => __( 'Post Approval Notification', 'wpuf-pro' ),
            'desc'     => __( 'Enable Post Approval Notification .', 'wpuf-pro' ),
            'default'  => 'on',
            'type'     => 'checkbox',
        );

        $settings_fields['wpuf_mails'][] = array(
            'name'     => 'approved_post_email_subject',
            'label'    => __( 'Approved Email Subject for User', 'wpuf-pro' ),
            'desc'     => __( 'This sets the subject of the emails sent to approved post author.', 'wpuf-pro' ),
            'default'  => 'Post has been approved',
            'type'     => 'text',
            'class'    => 'approved-post-email-option',
        );

        $settings_fields['wpuf_mails'][] = array(
            'name'     => 'approved_post_email_body',
            'label'    => __( 'Approved Email Body for post author', 'wpuf-pro' ),
            //phpcs:ignore
            'desc'     => __( 'This sets the body of the emails sent to approved post author. <br><strong>You may use: </strong><code>%username%</code><code>%display_name%</code><code>%post_title%</code><code>%post_link%</code>', 'wpuf-pro' ),
            'default'  => 'Hi %username%,

            Your post has been approved by an administrator.

            Thanks',
            'type'     => 'wysiwyg',
            'class'    => 'approved-post-email-option',
        );

        return $settings_fields;
    }

    /**
     * Handle post status for email notification
     *
     * @param $new_status
     * @param $old_status
     * @param $post
     *
     * @since 3.4.7
     *
     * @return void
     */
    public function handle_post_status( $new_status, $old_status, $post ) {
        if ( 'on' !== wpuf_get_option( 'enable_post_approval_notification', 'wpuf_mails', 'on' ) ) {
            return;
        }

        if ( $new_status === 'private' ) {
            return;
        }

        $form_id = get_post_meta( $post->ID, '_wpuf_form_id', true );
        if ( ! $form_id ) {
            return;
        }

        $form_settings = wpuf_get_form_settings( $form_id );

        if ( ! $form_settings ) {
            return;
        }

        $post_form_status = ! empty( $form_settings['post_status'] ) ? $form_settings['post_status'] : '';

        if ( $post_form_status === 'publish' ) {
            return;
        }

        if ( $old_status === $post_form_status && ( $new_status === 'publish' || $new_status === 'trash' ) ) {
            $this->send_mail( $new_status, $post );
        }
    }

    /**
     * Send email for reject, publish post
     *
     * @param $status
     * @param $post
     *
     * @since 3.4.7
     *
     * @return void
     */
    public function send_mail( $status, $post ) {
        if ( $status === 'publish' ) {
            $subject = wpuf_get_option( 'approved_post_email_subject', 'wpuf_mails' );
            $body    = wpautop( wpuf_get_option( 'approved_post_email_body', 'wpuf_mails' ) );
        }

        $this->user = get_user_by( 'id', $post->post_author );
        $user_email = $this->user->user_email;

        $body = $this->prepare_mail_body( $post, $body );
        $body = get_formatted_mail_body( $body, $subject );
        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
        wp_mail( $user_email, $subject, $body, $headers );
    }

    /**
     * Placeholder for mail body
     *
     * @param $post
     * @param $mail_body
     *
     * @since 3.4.7
     *
     * @return string|string[]
     */
    public function prepare_mail_body( $post, $mail_body ) {
        $sub_field_search = [ '%username%', '%display_name%', '%post_title%', '%post_link%' ];

        $sub_field_replace = array(
            $this->user->user_login,
            $this->user->display_name,
            $post->post_title,
            get_post_permalink( $post->ID ),
        );

        return str_replace( $sub_field_search, $sub_field_replace, $mail_body );
    }

}
