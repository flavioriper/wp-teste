<?php
$profile_header_template = wpuf_get_option( 'profile_header_template', 'user_directory', 'layout' );

$profile_fields = $this->get_options();
// get the saved profile tabs from userlisting builder setting
$profile_tabs = isset( $profile_fields['profile_tabs'] ) ? $profile_fields['profile_tabs'] : [];

$saved_tabs = [];

// if profile tabs are set from userlisting builder
if ( count( $profile_tabs ) ) {
    foreach ( $profile_tabs as $saved ) {
        foreach ( $saved as $key => $value ) {
            if ( $value['show_tab'] ) {
                $saved_tabs[ $key ] = [
                    'label' => $value['label'],
                    'id'    => $value['id'],
                ];
            }
        }
    }
}

switch ( $profile_header_template ) {
    case 'layout':
        require_once WPUF_UD_VIEWS . '/frontend/profile/layouts/layout-one.php';
        break;
    case 'layout1':
        require_once WPUF_UD_VIEWS . '/frontend/profile/layouts/layout-two.php';
        break;
    case 'layout2':
        require_once WPUF_UD_VIEWS . '/frontend/profile/layouts/layout-three.php';
        break;
}
