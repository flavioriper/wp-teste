<?php
/**
 * Infobox "default" info-window template file .
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
 * your-theme's-or-child-theme's-folder/geo-my-wp/posts-locator/info-window-templates/infobox/
 *
 * Once the template folder is in the theme's folder, you will be able to select
 * it in the form editor.
 *
 * $gmw  - the form being used ( array )
 *
 * $post - the post being displayed ( object )
 *
 * @package gmw-premium-settings
 */

?>
<div class="template-content-wrapper">

	<?php do_action( 'gmw_ib_template_start', $post, $gmw ); ?>

	<?php if ( $gmw['info_window']['image']['enabled'] && has_post_thumbnail( $post->ID ) ) { ?>  	
		<?php do_action( 'gmw_ib_template_before_image', $post, $gmw ); ?>

		<div class="featured-image">
			<?php echo get_the_post_thumbnail( $post->ID, 'full' ); ?>
		</div>
	<?php } ?>	

	<?php do_action( 'gmw_ib_template_before_title', $post, $gmw ); ?>

	<h3 class="title">
		<a href="<?php gmw_info_window_permalink( get_permalink( $post->ID ), $post, $gmw ); ?>">
			<?php gmw_info_window_title( $post->post_title, $post, $gmw ); ?>
		</a>

		<?php gmw_info_window_distance( $post, $gmw ); ?>	
	</h3>

	<?php do_action( 'gmw_ib_template_before_address', $post, $gmw ); ?>

	<?php gmw_info_window_address( $post, $gmw ); ?>

	<?php gmw_info_window_directions_link( $post, $gmw ); ?>

	<?php do_action( 'gmw_ib_template_before_excerpt', $post, $gmw ); ?>

	<?php gmw_info_window_post_excerpt( $post, $gmw ); ?>

	<?php do_action( 'gmw_ib_template_before_location_meta', $post, $gmw ); ?>

	<?php gmw_info_window_location_meta( $post, $gmw, false ); ?>

	<?php do_action( 'gmw_ib_template_end', $post, $gmw ); ?>

</div>  
