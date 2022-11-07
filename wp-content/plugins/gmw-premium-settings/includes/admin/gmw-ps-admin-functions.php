<?php
/**
 * GMW Premium Settings - Admin functions.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Admin notices
 *
 * @param  array $messages messages.
 *
 * @return [type]           [description]
 */
function gmw_ps_notices_messages( $messages ) {

	$messages['map_icons_refreshed'] = __( 'Map icons updated.', 'gmw-premium-settings' );

	return $messages;
}
add_filter( 'gmw_admin_notices_messages', 'gmw_ps_notices_messages' );

/**
 * Generate map icons refresh button
 *
 * @param  string  $page    admin page.
 *
 * @param  boolean $message show message?.
 *
 * @return [type]       [description]
 */
function gmw_refresh_map_icons_button( $page = '', $message = true ) {

	if ( empty( $page ) ) {
		return;
	}

	$nonce = wp_create_nonce( 'gmw_map_icons_refresh_nonce' );
	$url   = admin_url( $page . '&action=refresh_map_icons&_wpnonce=' . $nonce );

	?>
	<div class="gmw-refresh-icons-wrapper">
		<?php if ( $message ) { ?>
		<em>
			<?php esc_html_e( '* Use the "Refresh Icons" button after uploading new icons or if map icons are missing.', 'gmw-premium-settings' ); ?>	
		</em>
		<?php } ?>
		<input type="button" class="button-secondary" onclick="location.href='<?php echo esc_url( $url ); ?>';" value="<?php esc_html_e( 'Refresh Icons', 'gmw-premium-settings' ); ?>" />
	</div>
	<?php
}

/**
 * When Manually refresh map icons with a button
 *
 * @param string $page the page the icons displayed on.
 */
function gmw_refresh_map_icons( $page ) {

	// collect icons if not already exist in options or if manually refreshed by user.
	if ( ! empty( $_GET['action'] ) && 'refresh_map_icons' === $_GET['action'] && check_admin_referer( 'gmw_map_icons_refresh_nonce' ) ) { // WPCS: CSRF ok.

		// collect icons.
		gmw_ps_collect_icons();

		if ( ! empty( $_GET['page'] ) ) {

			$action  = ! empty( $_GET['gmw_action'] ) ? '&gmw_action=' . sanitize_text_field( wp_unslash( $_GET['gmw_action'] ) ) : '';
			$form_id = ! empty( $_GET['form_id'] ) ? '&form_id=' . absint( $_GET['form_id'] ) : '';
			$page    = 'admin.php?page=' . sanitize_text_field( wp_unslash( $_GET['page'] ) ) . $action . $form_id;

		} elseif ( ! empty( $_GET['taxonomy'] ) ) {
			$page = 'edit-tags.php?taxonomy=' . sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) );
		} else {
			$page = 'admin.php?page=gmw-settings';
		}

		// refresh page.
		wp_safe_redirect( $page . '&gmw_notice=map_icons_refreshed&gmw_notice_status=updated' );
	}
}
add_action( 'admin_init', 'gmw_refresh_map_icons' );

/**
 * Collect different icons into a global array that will be saved in options.
 *
 * This is an "expensive" function and we prevent the plugin from executing it on each page load.
 */
function gmw_ps_collect_icons() {

	$icons                = array();
	$stylesheet_directory = get_stylesheet_directory();

	// save pt map icons in settings.
	if ( gmw_is_addon_active( 'posts_locator' ) ) {

		// get addon's data.
		$posts_locator_data = gmw_get_addon_data( 'posts_locator' );

		// look for map icons in custom template folder.
		if ( is_dir( $stylesheet_directory . "/geo-my-wp/{$posts_locator_data['templates_folder']}/map-icons/" ) ) {
			$pt_map_icons_path = $stylesheet_directory . "/geo-my-wp/{$posts_locator_data['templates_folder']}/map-icons/";
			$pt_map_icons_url  = get_stylesheet_directory_uri() . "/geo-my-wp/{$posts_locator_data['templates_folder']}/map-icons/";
			$pt_map_icons      = glob( $pt_map_icons_path . '*.{jpg,png,gif,svg}', GLOB_BRACE );
		}

		// if no custom icons were found grab the plugin's default icons.
		if ( empty( $pt_map_icons ) ) {
			$pt_map_icons_path = GMW_PS_PATH . '/assets/map-icons/';
			$pt_map_icons_url  = GMW_PS_URL . '/assets/map-icons/';
			$pt_map_icons      = glob( $pt_map_icons_path . '*.{jpg,png,gif,svg}', GLOB_BRACE );
		}

		// collect icons into array.
		foreach ( $pt_map_icons as $key => $file ) {
			$pt_map_icons[ $key ] = basename( $file );
		}

		$icons['pt_map_icons']['path']      = $pt_map_icons_path;
		$icons['pt_map_icons']['url']       = $pt_map_icons_url;
		$icons['pt_map_icons']['all_icons'] = $pt_map_icons;
		$icons['pt_map_icons']['set_icons'] = get_option( 'gmw_category_map_icons' );

		// look for category icons in custom folder.
		if ( is_dir( $stylesheet_directory . "/geo-my-wp/{$posts_locator_data['templates_folder']}/category-icons/" ) ) {
			$pt_category_icons_path = $stylesheet_directory . "/geo-my-wp/{$posts_locator_data['templates_folder']}/category-icons/";
			$pt_category_icons_url  = get_stylesheet_directory_uri() . "/geo-my-wp/{$posts_locator_data['templates_folder']}/category-icons/";
			$pt_category_icons      = glob( $pt_category_icons_path . '*.{jpg,png,gif,svg}', GLOB_BRACE );
		}

		// if no custom icons were found get plugin's default icons.
		if ( empty( $pt_category_icons ) ) {
			$pt_category_icons_path = GMW_PS_PATH . '/assets/map-icons/';
			$pt_category_icons_url  = GMW_PS_URL . '/assets/map-icons/';
			$pt_category_icons      = glob( $pt_category_icons_path . '*.{jpg,png,gif,svg}', GLOB_BRACE );
		}

		foreach ( $pt_category_icons as $key => $file ) {
			$pt_category_icons[ $key ] = basename( $file );
		}

		$icons['pt_category_icons']['path']      = $pt_category_icons_path;
		$icons['pt_category_icons']['url']       = $pt_category_icons_url;
		$icons['pt_category_icons']['all_icons'] = $pt_category_icons;
		$icons['pt_category_icons']['set_icons'] = get_option( 'gmw_category_icons' );
	}

	$addons = apply_filters( 'gmw_ps_map_icons_addons', array( 'members_locator', 'bp_groups_locator', 'users_locator' ) );

	foreach ( $addons as $addon ) {

		if ( ! gmw_is_addon_active( $addon ) ) {
			continue;
		}

		$map_icons_path = false;
		$map_icons_url  = false;
		$map_icons      = false;

		// get addon's data.
		$addon_data = gmw_get_addon_data( $addon );

		if ( is_dir( $stylesheet_directory . "/geo-my-wp/{$addon_data['templates_folder']}/map-icons/" ) ) {
			$map_icons_path = $stylesheet_directory . "/geo-my-wp/{$addon_data['templates_folder']}/map-icons/";
			$map_icons_url  = get_stylesheet_directory_uri() . "/geo-my-wp/{$addon_data['templates_folder']}/map-icons/";
			$map_icons      = glob( $map_icons_path . '*.{jpg,png,gif,svg}', GLOB_BRACE );
		}

		if ( empty( $map_icons ) ) {
			$map_icons_path = GMW_PS_PATH . '/assets/map-icons/';
			$map_icons_url  = GMW_PS_URL . '/assets/map-icons/';
			$map_icons      = glob( $map_icons_path . '*.{jpg,png,gif,svg}', GLOB_BRACE );
		}

		foreach ( $map_icons as $key => $file ) {
			$map_icons[ $key ] = basename( $file );
		}

		$icons[ $addon_data['prefix'] . '_map_icons' ]['path']      = $map_icons_path;
		$icons[ $addon_data['prefix'] . '_map_icons' ]['url']       = $map_icons_url;
		$icons[ $addon_data['prefix'] . '_map_icons' ]['all_icons'] = $map_icons;
	}

	$icons = apply_filters( 'gmw_ps_collect_map_icon', $icons );

	// update global with new icons.
	GMW()->icons = $icons;
	// update options with new icons.
	update_option( 'gmw_icons', $icons );
}

// collect map icons if not yet exists.
if ( empty( GMW()->icons ) ) {
	add_action( 'admin_init', 'gmw_ps_collect_icons' );
}

/**
 * Exclude location form tabs from post edit page
 *
 * @param  array $args tab arguments.
 *
 * @return [type]       [description]
 */
function gmw_pt_exclude_location_form_tabs( $args ) {

	$exclude_tabs = gmw_get_option( 'post_types_settings', 'edit_post_exclude_lf_tabs', array() );

	if ( ! empty( $exclude_tabs ) ) {
		$args['exclude_tabs'] = implode( ',', $exclude_tabs );
	}

	return $args;
}
add_filter( 'gmw_edit_post_location_form_args', 'gmw_pt_exclude_location_form_tabs' );
