<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function gmw_ps_bp_members_directory_geolocation_init() {
	// do stuff in admin
	if ( IS_ADMIN ) {
        // admin settings
        include( 'includes/admin/class-gmw-ps-md-admin-settings.php' );

    // do more cool stuff in front-end
	} else {
		include( 'includes/class-gmw-ps-md-template-functions.php' );
	}
}
add_action( 'bp_init', 'gmw_ps_bp_members_directory_geolocation_init', 50 );
