<?php
/**
 * GMW Premium Settings - functions.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get icons data
 *
 * @return [type] [description]
 */
function gmw_get_icons() {

	if ( ! empty( GMW()->icons ) ) {

		return GMW()->icons;

	} else {

		$icons = get_option( 'gmw_icons' );

		if ( empty( $icons ) ) {
			$icons = array();
		}

		GMW()->icons = $icons;
	}

	return $icons;
}

/**
 * Enqueue smartbox libraray
 */
function gmw_ps_enqueue_smartbox() {

	$library = gmw_get_option( 'general_settings', 'smartbox_library', 'chosen' );

	if ( ! wp_script_is( $library, 'enqueued' ) ) {
		wp_enqueue_script( $library );
		wp_enqueue_style( $library );
	}
}

/**
 * Map Icons tab in location form
 *
 * @param  array $tabs location form tabs array.
 *
 * @return [type]       [description]
 */
function gmw_ps_location_form_map_icons_tab( $tabs ) {

	$tabs['map_icons'] = array(
		'label'    => __( 'Map Marker ', 'gmw-premium-settings' ),
		'icon'     => 'gmw-icon-map-pin',
		'priority' => 30,
	);

	return $tabs;
}

/**
 * Output map icons panel in Location form
 *
 * @param  object $form location form.
 */
function gmw_ps_location_form_map_icons_panel( $form ) {

	$addon_data = gmw_get_addon_data( $form->slug );
	$prefix     = $addon_data['prefix'];

	// get saved icon.
	$saved_icon = ! empty( $form->saved_location->map_icon ) ? $form->saved_location->map_icon : '_default.png';
	?>
	<div id="map_icons-tab-panel" class="section-wrapper map-icons">

		<?php do_action( 'gmw_lf_' . $prefix . '_map_icons_section_start', $form ); ?>

		<div class="icons-wrapper">
			<?php
			$icons_data = gmw_get_icons();

			if ( ! empty( $icons_data[ $prefix . '_map_icons' ] ) ) {

				$map_icons = $icons_data[ $prefix . '_map_icons' ]['all_icons'];
				$icons_url = $icons_data[ $prefix . '_map_icons' ]['url'];
				$cic       = 1;

				foreach ( $map_icons as $map_icon ) {

					$checked = ( ( $saved_icon === $map_icon ) || 1 === $cic ) ? 'checked="checked"' : '';

					echo '<label>';
					echo '<input type="radio" name="gmw_location_form[map_icon]" value="' . esc_attr( $map_icon ) . '" ' . $checked . ' />'; // WPCS: XSS ok.
					echo '<img src="' . esc_url( $icons_url . $map_icon ) . '" />';
					echo '</label>';

					$cic++;
				}
			}
			?>
		</div>
		<?php do_action( 'gmw_lf_' . $prefix . '_map_icons_section_end', $form ); ?>
	</div>
	<?php
}
