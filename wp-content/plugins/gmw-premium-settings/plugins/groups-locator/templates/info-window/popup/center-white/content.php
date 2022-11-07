<?php
/**
 * Popup "center-white" info-window template file .
 *
 * The content of this file will be displayed in the map marker info-window.
 *
 * You can modify this file to apply custom changes. However, it is not recomended
 * to make the changes directly in this file, because your changes will be
 * overwritten with the next update of the plugin.
 *
 * Instead, you can copy or move this template ( the folder contains this file
 * and the "css" folder ) into the theme's or child theme's folder of your site,
 * and apply your changes from there.
 *
 * The custom template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/groups-locator/info-window/popup/
 *
 * Once the template folder is in the theme's folder, you will be able to select
 * it in the form editor.
 *
 * @param array  $gmw   GEO my WP form.
 *
 * @param object $group the group object
 *
 * @package gmw-premium-settings
 */

?>
<?php do_action( 'gmw_info_window_before', $group, $gmw ); ?>  

<div class="buttons-wrapper">
	<?php gmw_element_dragging_handle(); ?>
	<?php gmw_element_toggle_button(); ?>
	<?php gmw_element_close_button( 'gmw-icon-cancel' ); ?>
</div>

<div class="gmw-info-window-inner popup template-content-wrapper">

	<?php do_action( 'gmw_info_window_start', $group, $gmw ); ?>

	<?php gmw_info_window_bp_avatar( $group, $gmw ); ?>	

	<?php do_action( 'gmw_info_window_before_title', $group, $gmw ); ?>

	<a class="title" href="<?php gmw_info_window_permalink( bp_group_permalink(), $group, $gmw ); ?>">
		<?php gmw_info_window_title( bp_group_name(), $group, $gmw ); ?>
	</a>

	<span class="last-active">
		<?php bp_group_last_active(); ?>	
	</span>

	<?php do_action( 'gmw_info_window_before_address', $group, $gmw ); ?>

	<?php gmw_info_window_address( $group, $gmw ); ?>

	<?php gmw_info_window_directions_link( $group, $gmw ); ?>

	<?php gmw_info_window_distance( $group, $gmw ); ?>

	<?php do_action( 'gmw_info_window_before_location_meta', $group, $gmw ); ?>

	<?php gmw_info_window_location_meta( $group, $gmw, false ); ?>

	<?php gmw_info_window_directions_system( $group, $gmw ); ?>

	<?php do_action( 'gmw_info_window_end', $group, $gmw ); ?>		

</div>  

<?php do_action( 'gmw_info_window_after', $group, $gmw ); ?>
