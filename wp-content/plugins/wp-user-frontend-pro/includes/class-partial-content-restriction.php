<?php if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class partial content restriction
 *
 * * @since 3.4.4
 */
class WPUF_Partial_Content_Restriction {

    /**
     * Constructor for partial content class
     */
    public function __construct() {
        add_shortcode( 'wpuf_partial_restriction', [ $this, 'add_shortcode' ] );
    }

    /**
     * Add shortcode for partial content restriction
     *
     * @param array $atts
     * @param string $content
     *
     * @return void
     */
    public function add_shortcode( $atts, $content ) {
        $defaults = [
            'roles'         => '',
            'subscriptions' => '',
        ];

        $atts = shortcode_atts( $defaults, $atts, 'wpuf_content_restrict' );

        $roles         = isset( $atts['roles'] ) ? explode( ',', $atts['roles'] ) : [];
        $subscriptions = isset( $atts['subscriptions'] ) ? explode( ',', $atts['subscriptions'] ) : [];
        $subscriptions = array_map( 'intval', $subscriptions );


        unset( $roles[0] );
        unset( $subscriptions[0] );
        $type = 'everyone';

        if ( ! empty( $roles ) ) {
            $type = 'loggedin';
        }

        if ( ! empty( $subscriptions ) ) {
            $type = 'subscription';
        }

        ob_start();

        $this->partial_content_restrict( do_shortcode( $content ), $roles, $subscriptions, $type );

        return ob_get_clean();
    }

    /**
     * Restrict partial content
     *
     *
     * @param string $content
     * @param array $roles
     * @param array $subscriptions
     *
     * @return void
     */
    public function partial_content_restrict( $content, $roles, $subscriptions, $type ) {
        if ( current_user_can( 'manage_options' ) || 'everyone' === $type ) {
            echo $content;
            return;
        }

        $errors = [];

        $current_pack = get_user_meta( get_current_user_id(), '_wpuf_subscription_pack', true );
        $pack_id = ! empty( $current_pack['pack_id'] ) ? $current_pack['pack_id'] : 0;

        if ( 'loggedin' === $type && ! is_user_logged_in() ) {
            /* translators: 1: Login Url */
            $errors[] = sprintf( __( 'You must be %s to view this content.', 'wpuf-pro' ), sprintf( '<a href="%s">%s</a>', wp_login_url( get_permalink( get_the_ID() ) ), __( 'logged in', 'wpuf-pro' ) ) );
        }

        if ( 'loggedin' === $type && is_user_logged_in() && ! wpuf_user_has_roles( $roles ) ) {
            $errors[] = __( 'This content is restricted for your user role', 'wpuf-pro' );
        }

        if ( 'subscription' === $type && ! is_user_logged_in() ) {
            /* translators: 1: Login Url */
            $errors[] = sprintf( __( 'You must be %s to view this content.', 'wpuf-pro' ), sprintf( '<a href="%s">%s</a>', wp_login_url( get_permalink( get_the_ID() ) ), __( 'logged in', 'wpuf-pro' ) ) );
        }

        if ( 'subscription' === $type && empty( $current_pack ) ) {
            /* translators: 1: Login Url */
            $errors[] = sprintf( __( 'You don\'t have a valid subscription package.', 'wpuf-pro' ), sprintf( '<a href="%s">%s</a>', wp_login_url( get_permalink( get_the_ID() ) ), __( 'logged in', 'wpuf-pro' ) ) );
        }

        if ( 'subscription' === $type && ! in_array( intval( $pack_id ), $subscriptions, true ) ) {
            $errors[] = __( 'Your subscription pack is not allowed to view this content', 'wpuf-pro' );
        }

        if ( $errors ) {
            /* translators: 1: Error Message */
            printf( '<div class="wpuf-info wpuf-restrict-message">%s</div>', $errors[0] );
        } else {
            echo $content;
        }
    }
}
