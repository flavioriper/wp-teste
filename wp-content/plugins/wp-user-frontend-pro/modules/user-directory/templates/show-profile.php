<?php
$profile_header_template = wpuf_get_option( 'profile_header_template', 'user_directory', 'layout' );
$avatar_size  = wpuf_get_option( 'avatar_size', 'user_directory', 120 );

$profile_fields = WPUF_User_Listing()->shortcode->get_options();
// get the saved profile tabs from userlisting builder setting
$profile_tabs = ! empty( $profile_fields['profile_tabs'] ) ? $profile_fields['profile_tabs'] : [];

$saved_tabs = [];

// if profile tabs are set from userlisting builder
if ( count( $profile_tabs ) ) {
    foreach ( $profile_tabs as $key => $value ) {
        if ( ! empty( $value['show_tab'] ) ) {
            $saved_tabs[ $key ] = [
                'label' => $value['label'],
                'id'    => $value['id'],
            ];
        }
    }
}

$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'posts'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$all_data['profile_header_template'] = $profile_header_template;
$all_data['avatar_size']    = $avatar_size;
$all_data['profile_fields'] = $profile_fields;
$all_data['profile_tabs']   = $profile_tabs;
$all_data['saved_tabs']     = $saved_tabs;
$all_data['user']           = $user;
$all_data['current_tab']    = $current_tab;

switch ( $profile_header_template ) {
    case 'layout':
        wp_enqueue_style( 'wpuf-ud-layout-one' );
        wpuf_load_pro_template( 'layout-one.php', $all_data, WPUF_UD_TEMPLATES . '/profile/layouts/' );
        break;
    case 'layout1':
        wp_enqueue_style( 'wpuf-ud-layout-two' );
        wpuf_load_pro_template( 'layout-two.php', $all_data, WPUF_UD_TEMPLATES . '/profile/layouts/' );
        break;
    case 'layout2':
        wp_enqueue_style( 'wpuf-ud-layout-three' );
        wpuf_load_pro_template( 'layout-three.php', $all_data, WPUF_UD_TEMPLATES . '/profile/layouts/' );
        break;
}
