<?php
/**
 * Popup "center-white" info-window template file .
 *
 * The content of this file will be displayed in the map markers info-window.
 *
 * You can modify this file to apply custom changes. However, it is not recomended
 * to make the changes directly in this file,
 * because your changes will be overwritten with the next update of the plugin.
 *
 * Instead, you can copy or move this template ( the folder contains this file
 * and the "css" folder ) into the theme's or child theme's folder of your site,
 * and apply your changes from there.
 *
 * The custom template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/users-locator/info-window/popup/
 *
 * Once the template folder is in the theme's folder, you will be able to select
 * it in the form editor.
 *
 * @param array  $gmw   GEO my WP form.
 *
 * @param object $user  the user object.
 *
 * @package gmw-premium-settings
 */

?>
<?php do_action( 'gmw_info_window_before', $user, $gmw ); ?>  

<div class="buttons-wrapper">
	<?php gmw_element_dragging_handle(); ?>
	<?php gmw_element_toggle_button(); ?>
	<?php gmw_element_close_button( 'gmw-icon-cancel' ); ?>
</div>

<div class="gmw-info-window-inner popup">

	<?php do_action( 'gmw_info_window_start', $user, $gmw ); ?>

	<?php gmw_info_window_user_avatar( $user, $gmw ); ?>	

	<?php do_action( 'gmw_info_window_before_title', $user, $gmw ); ?>

	<?php gmw_ul_user_title( $user, $gmw ); ?>

	<?php do_action( 'gmw_info_window_before_address', $user, $gmw ); ?>

	<?php gmw_info_window_address( $user, $gmw ); ?>

	<?php gmw_info_window_directions_link( $user, $gmw ); ?>

	<?php gmw_info_window_distance( $user, $gmw ); ?>

	<?php do_action( 'gmw_info_window_before_location_meta', $user, $gmw ); ?>

	<?php gmw_info_window_location_meta( $user, $gmw, false ); ?>

	<?php gmw_info_window_directions_system( $user, $gmw ); ?>

	<?php do_action( 'gmw_info_window_end', $user, $gmw ); ?>		

</div>  

<?php do_action( 'gmw_info_window_after', $user, $gmw ); ?>
