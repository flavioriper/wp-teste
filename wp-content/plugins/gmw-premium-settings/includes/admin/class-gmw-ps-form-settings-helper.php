<?php
/**
 * GMW Premium Settings - Template functions helper class.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_PS_Form_Settings_Helper class.
 *
 * Premium Settings admin settings helper class.
 */
class GMW_PS_Form_Settings_Helper {

	/**
	 * Keywords field
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 *
	 * @param  [type] $form      [description].
	 */
	public static function keywords_field( $value, $name_attr, $form ) {

		$name_attr = esc_attr( $name_attr );

		if ( empty( $value ) ) {
			$value = array(
				'usage'       => '',
				'label'       => '',
				'placeholder' => 'Enter keywords',
			);
		}
		?>	
		<p>	
		<?php
		if ( empty( $value['usage'] ) ) {
			$value['usage'] = '';
		}
		$options = apply_filters( 'gmw_ps_form_settings_keywords_field_options', array( '' => 'Disabled' ), $form );
		?>

		<select class="gmw-keywords-field-usage" name="<?php echo $name_attr . '[usage]'; ?>">
			<?php foreach ( $options as $opt_value => $name ) { ?>
				<option value="<?php echo esc_attr( $opt_value ); ?>" <?php selected( $value['usage'], $opt_value ); ?>>
					<?php echo esc_html( $name ); ?>
				</option>
			<?php } ?>
		</select>
		</p>

		<?php $display = empty( $value['usage'] ) ? 'style="display:none"' : ''; ?>

		<div class="keywords-options-wrapper gmw-options-box" <?php echo $display; // WPCS: XSS ok. ?>>		

			<div class="single-option label">

			<label><?php _e( 'Label', 'gmw-premium-settings' ); ?></label>	

			<div class="option-content">
					<input 
						type="text" 
						placeholder="<?php _e( 'Enter label', 'gmw-premium-settings' ); ?>" 
						name="<?php echo $name_attr; ?>[label]" 
						value="<?php echo isset( $value['label'] ) ? esc_attr( stripcslashes( $value['label'] ) ) : ''; ?>" 
					/>
					<p class="description">
						<?php _e( 'Enter the field label or leave blank to omit.', 'gmw-premium-settings' ); ?>
					</p>
				</div>
			</div>

			<div class="single-option placeholder">
				<label><?php _e( 'Placeholder', 'gmw-premium-settings' ); ?></label>	
				<div class="option-content">
					<input 
						type="text" 
						placeholder="<?php _e( 'Enter placeholder', 'gmw-premium-settings' ); ?>" 
						name="<?php echo $name_attr; ?>[placeholder]" 
						value="<?php echo isset( $value['placeholder'] ) ? esc_attr( stripcslashes( $value['placeholder'] ) ) : ''; ?>" 
					/>
					<p class="description">
						<?php _e( 'Enter the field placeholder or leave blank to omit.', 'gmw-premium-settings' ); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Validate keywords field.
	 *
	 * @param  array $output unvalidated output.
	 *
	 * @return [type]         validated output.
	 */
	public static function validate_keywords_field( $output ) {

		$output['usage']       = ! empty( $output['usage'] ) ? sanitize_key( $output['usage'] ) : '';
		$output['label']       = isset( $output['label'] ) ? sanitize_text_field( $output['label'] ) : '';
		$output['placeholder'] = isset( $output['placeholder'] ) ? sanitize_text_field( $output['placeholder'] ) : '';

		return $output;
	}

	/**
	 * Address field settings
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 */
	public static function address_fields( $value, $name_attr ) {
		$name_attr = esc_attr( $name_attr );
		?>
	<p>
		<select name="<?php echo esc_attr( $name_attr ); ?>[usage]" id="address-fields-usage">

			<option value="single" selected="selected">
				<?php _e( 'Single Field', 'gmw-premium-settings' ); ?>	
			</option>

			<option value="multiple" 
			<?php
			if ( ! empty( $value['usage'] ) && $value['usage'] === 'multiple' ) {
				echo 'selected="selected"'; }
			?>
			>
				<?php _e( 'Multiple Fields', 'gmw-premium-settings' ); ?>
			</option>

		</select>
	</p>

	<div class="address-fields-settings-wrapper">

		<?php GMW_Form_Settings_Helper::address_field( $value, $name_attr ); ?>

		<div class="gmw-options-box gmw-address-fields-settings multiple" style="display:none;">  

			<?php $address_fields = array( 'street', 'city', 'state', 'zipcode', 'country' ); ?>

				<?php
				if ( ! is_array( $value ) ) {
					$value = array();
				}

				if ( empty( $value['multiple'] ) || ! is_array( $value['multiple'] ) ) {
					$value['multiple'] = array();
				}
				?>
				<?php
				foreach ( $address_fields as $field ) :
					$sy = false;
					?>
					<?php

					if ( empty( $value['multiple'][ $field ] ) ) {
						$value['multiple'][ $field ] = array(
							'usage'       => 'default',
							'value'       => '',
							'title'       => '',
							'placeholder' => '',
						);
					}
					?>
					<div class="single-address-field-raw">                   
						<div class="single-option usage third 
						<?php
						if ( $value['multiple'][ $field ]['usage'] == 'disabled' ) {
							echo 'disabled';}
						?>
						">
							<label>
								<?php echo sprintf( __( '%s Field', 'gmw-premium-settings' ), $field ); ?>
							</label>
							<div class="option-content">
								<select name="<?php echo $name_attr . '[multiple][' . $field . ']'; ?>[usage]">
									<option value="disabled" selected="selected">
										<?php _e( 'Disabled', 'gmw-premium-settings' ); ?>	
									</option>

									<option value="default"
										<?php selected( $value['multiple'][ $field ]['usage'], 'default' ); ?>>
										<?php _e( 'Pre-defined', 'gmw-premium-settings' ); ?>	
									</option>

									<option value="include" 
										<?php selected( $value['multiple'][ $field ]['usage'], 'include' ); ?>>
										<?php _e( 'Include', 'gmw-premium-settings' ); ?>
									</option>
								</select>
								<p class="description">
									<?php /*_e( 'Select "disabled" to omit the field, "Pre-defined" to hide the field and use a default value, or "Include" to display the field in the search form.', 'gmw-premium-settings' ); */ ?>
								</p>
							</div>
						</div>

						<div class="address-fields-settings default" 
							<?php
							if ( 'default' !== $value['multiple'][ $field ]['usage'] ) {
								echo 'style="display:none"';}
							?>
						>

							<div class="single-option value half">

								<label><?php echo _e( 'Value', 'gmw-premium-settings' ); ?></label>

								<div class="option-content">
									<input 
										type="text" 
										name="<?php echo $name_attr . '[multiple][' . $field . '][value]'; ?>" 
										value="<?php echo isset( $value['multiple'][ $field ]['value'] ) ? $value['multiple'][ $field ]['value'] : ''; ?>" 
									/>
									<p class="description">
										<?php _e( 'Enter the pre-defined value.', 'gmw-premium-settings' ); ?>
									</p>
								</div>
							</div>
						</div>

						<div class="address-fields-settings include" 
						<?php
						if ( 'include' !== $value['multiple'][ $field ]['usage'] ) {
							echo 'style="display:none"';}
						?>
						>

							<div class="single-option label third">

								<label><?php echo _e( 'Label', 'gmw-premium-settings' ); ?></label>

								<div class="option-content">
									<input 
										type="text" 
										name="<?php echo $name_attr . '[multiple][' . $field . '][title]'; ?>" 
										value="<?php echo isset( $value['multiple'][ $field ]['title'] ) ? $value['multiple'][ $field ]['title'] : ''; ?>" 
									/>
									<p class="description">
										<?php _e( 'Enter the field label or leave blank to omit.', 'gmw-premium-settings' ); ?>
									</p>
								</div>
							</div>

							<div class="single-option placeholder third">
								<label><?php echo _e( 'Placeholder', 'gmw-premium-settings' ); ?></label>

								<div class="option-content">
									<input 
										type="text" 
										name="<?php echo $name_attr . '[multiple][' . $field . '][placeholder]'; ?>" 
										value="<?php echo isset( $value['multiple'][ $field ]['placeholder'] ) ? $value['multiple'][ $field ]['placeholder'] : ''; ?>" 
									/>
									<p class="description">
										<?php _e( 'Enter the field placeholder or leave blank to omit.', 'gmw-premium-settings' ); ?>
									</p>
								</div>
							</div>
						</div>

						<?php
						/*
						<div class="single-option mandatory">
							<label><?php echo _e( 'Mandatory','gmw-premium-settings' ); ?></label>

							<div class="option-content">
								<input
									type="checkbox"
									value="1"
									name="<?php echo $name_attr.'[multiple]['.$field.'][mandatory]'; ?>"
									<?php echo isset( $value['multiple'][$field]['mandatory'] ) ? " checked=checked " : ''; ?>
								/>
							</div>

						</div>
						*/
						?>
						<?php
						/*
						<div class="gmw-saf-default" style="display:none;>
							<label style="display:block"><?php echo _e( 'Default Value:', 'gmw-premium-settings' ); ?></label>
							<input type="text" name="<?php echo $name_attr.'[multiple]['.$field.'][value]'; ?>" size="25" value="<?php echo isset( $value['multiple'][$field]['value'] ) ? $value['multiple'][$field]['value'] : ''; ?>" />

						</div>
						*/
						?>
						   
					</div>

				<?php endforeach; ?>
			</div>
		</div>
		<?php
		if ( empty( $value['usage'] ) ) {
			$value['usage'] = 'single';
		}
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function() {

				var usage = '<?php echo esc_attr( $value['usage'] ); ?>';

				if ( usage == 'multiple' ) {
					jQuery( '.gmw-options-box.gmw-address-fields-settings.single' ).hide();
					jQuery( '.gmw-options-box.gmw-address-fields-settings.multiple' ).show();
				}
			});
		</script>
		<?php
	}

	/**
	 * Slider Radius
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 */
	public static function radius_slider( $value, $name_attr ) {
		$name_attr = esc_attr( $name_attr );

		if ( empty( $value ) ) {
			$value = array(
				'enabled'       => '',
				'default_value' => '50',
				'min_value'     => '0',
				'max_value'     => '200',
			);
		}
		?>
		<p>
		<label>
			<input 
				onclick="jQuery( '.radius-slider-options' ).slideToggle();" 
				type="checkbox" 
				value="1" 
				name="<?php echo $name_attr . '[enabled]'; ?>" 
				<?php echo ! empty( $value['enabled'] ) ? 'checked="checked"' : ''; ?>
			/>
			<?php echo _e( 'Enable', 'gmw-premium-settings' ); ?>
		</label>
		</p>

		<div class="radius-slider-options gmw-options-box" <?php echo empty( $value['enabled'] ) ? 'style="display:none";' : ''; ?>>
			<div class="single-option third">
				<label><?php echo _e( 'Default value', 'gmw-premium-settings' ); ?></label>
				<div class="option-content">
					<input 
						name="<?php echo $name_attr . '[default_value]'; ?>" 
						type="number" 
						value="<?php echo isset( $value['default_value'] ) ? esc_attr( $value['default_value'] ) : '50'; ?>" 
						placeholder="Numeric value"
					/>
					<p class="description">
						<?php _e( 'Enter the default value.', 'gmw-premium-settings' ); ?>
					</p>
				</div>
			</div>

			<div class="single-option third">
				<label><?php echo _e( 'Minimum Value', 'gmw-premium-settings' ); ?></label>
				<div class="option-content">
					<input 
						name="<?php echo $name_attr . '[min_value]'; ?>" 
						type="number" 
						value="<?php echo isset( $value['min_value'] ) ? esc_attr( $value['min_value'] ) : '0'; ?>"
					/>
					<p class="description">
						<?php _e( 'Enter the minimum value of the slide.', 'gmw-premium-settings' ); ?>
					</p>
				</div>
			</div>
			<div class="single-option third">
				<label><?php echo _e( 'Maximum value', 'gmw-premium-settings' ); ?></label>
				<div class="option-content">
					<input
						name="<?php echo $name_attr . '[max_value]'; ?>"
						type="number" 
						value="<?php echo isset( $value['max_value'] ) ? esc_attr( $value['max_value'] ) : '200'; ?>"
					/>
					<p class="description">
						<?php _e( 'Enter the maximum value of the slide.', 'gmw-premium-settings' ); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Validate Radius Slider Field.
	 *
	 * @param  [type] $output [description].
	 *
	 * @return [type]         [description]
	 */
	public static function validate_radius_slider( $output ) {

		$output = array(
			'enabled'       => ! empty( $output['enabled'] ) ? 1 : '',
			'default_value' => ! empty( $output['default_value'] ) ? preg_replace( '/[^0-9]/', '', $output['default_value'] ) : '50',
			'min_value'     => ! empty( $output['min_value'] ) ? preg_replace( '/[^0-9]/', '', $output['min_value'] ) : '0',
			'max_value'     => ! empty( $output['max_value'] ) ? preg_replace( '/[^0-9]/', '', $output['max_value'] ) : '200',
		);

		return $output;
	}

	/**
	 * Get array of map icons.
	 *
	 * @param  string  $prefix      [description].
	 *
	 * @param  boolean $user_marker [description].
	 *
	 * @return [type]               [description]
	 */
	public static function get_map_icons( $prefix = 'pt', $user_marker = false ) {

		$icons_data = gmw_get_icons();
		$map_icons  = array();

		if ( ! empty( $icons_data[ $prefix . '_map_icons' ] ) && ! empty( $icons_data[ $prefix . '_map_icons' ]['url'] ) ) {
			$map_icons = ! empty( $icons_data[ $prefix . '_map_icons' ]['all_icons'] ) ? $icons_data[ $prefix . '_map_icons' ]['all_icons'] : array();
			$icons_url = ! empty( $icons_data[ $prefix . '_map_icons' ]['url'] ) ? $icons_data[ $prefix . '_map_icons' ]['url'] : '';
		}

		if ( empty( $map_icons ) ) {

			return array();

		} else {

			$icons = array();
			$url   = ( ! $user_marker ) ? GMW()->default_icons['location_icon_url'] : GMW()->default_icons['user_location_icon_url'];

			foreach ( $map_icons as $map_icon ) {
				$icons[ $map_icon ] = '<img src="' . esc_url( $icons_url . $map_icon ) . '" />';
			}

			if ( array_key_exists( 'defaultUserMarker.png', $icons ) ) {
				$icons = array( 'defaultUserMarker.png' => $icons['defaultUserMarker.png'] ) + $icons;
			}

			if ( array_key_exists( '_default.png', $icons ) ) {
				$icons = array( '_default.png' => $icons['_default.png'] ) + $icons;
			}

			return $icons;
		}
	}

	/**
	 * Refresh map icons button.
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 *
	 * @param  [type] $form      [description].
	 */
	public static function refresh_map_icons( $value, $name_attr, $form ) {
		gmw_refresh_map_icons_button( 'admin.php?page=gmw-forms&gmw_action=edit_form&form_id=' . $form['ID'], false );
	}

	/**
	 * All results message settings
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 */
	public static function results_found_message( $value, $name_attr ) {
		$count_message    = isset( $value['count_message'] ) ? esc_attr( stripcslashes( $value['count_message'] ) ) : 'Showing {from_count} - {to_count} of {total_results} locations';
		$location_message = isset( $value['location_message'] ) ? esc_attr( stripcslashes( $value['location_message'] ) ) : ' within {radius}{units} from {address}';
		?>
		<div class="gmw-options-box">
			<div class="single-option">	
				<label><?php echo _e( 'Count Message', 'gmw-premium-settings' ); ?></label>
				<div class="option-content">	
					<input 
						type="text" 
						name="<?php echo esc_attr( $name_attr ) . '[count_message]'; ?>" 
						value="<?php echo $count_message; ?>"
						placeholder="ex. Showing {from_count} - {to_count} of {total_results}"
					/>
					<p class="description">
						<?php _e( 'This message displays the number of results that were found ( ex. Showing 5 out of 10 results ).<br /><b>placeholders:</b> <br />- {from_count}: the count of the first location in the results.<br />- {to_count}: the count of the last location in the results.<br />- {results_count}: the number of results showing on the viewd page.<br />- {total_results}: the total results that were found.', 'gmw-premium-settings' ); ?>
					</p>
				</div>
			</div>

			<div class="single-option">	
				<label><?php echo _e( 'Radius Message', 'gmw-premium-settings' ); ?></label>
				<div class="option-content">	
					<input 
						type="text" 
						name="<?php echo esc_attr( $name_attr ) . '[location_message]'; ?>" 
						value="<?php echo $location_message; ?>"
						placeholder="ex. within {radius} {units} from {address}"
					/>
					<p class="description">
						<?php _e( 'This message displays the address and radius ( ex. within 200 mi from New York ).<br /><b>placeholders:</b> <br />- {radius}: the radius/distance value.<br />- {units}: miles/kilometers.<br />- {address}: the address was used in the form.', 'gmw-premium-settings' ); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Validate results found message
	 *
	 * @param  [type] $output [description].
	 *
	 * @return [type]         [description]
	 */
	public static function validate_results_found_message( $output ) {

		$allowed_html = array(
			'a'    => array(
				'title' => array(),
				'href'  => array(),
			),
			'p'    => array(),
			'em'   => array(),
			'span' => array(
				'class' => array(),
			),
		);

		$output['count_message']    = isset( $output['count_message'] ) ? wp_kses( $output['count_message'], $allowed_html ) : '';
		$output['location_message'] = isset( $output['location_message'] ) ? wp_kses( $output['location_message'], $allowed_html ) : '';

		return $output;
	}

	/**
	 * Results template form settings posts.
	 *
	 * @param  [type] $value       [description].
	 *
	 * @param  [type] $name_attr   [description].
	 *
	 * @param  [type] $form        [description].
	 *
	 * @param  [type] $form_fields [description].
	 */
	public static function info_window_template( $value, $name_attr, $form, $form_fields ) {

		echo '<div id="info-window-templates-wrapper" style="display: none;">';

		$iw_types = $form_fields['info_window']['iw_type']['options'];

		foreach ( $iw_types as $iw_name => $iw_title ) {

			// Get templates.
			$templates = gmw_get_info_window_templates( $form['component'], $iw_name, 'premium_settings' );
			?>
			<div class="gmw-info-window-template <?php echo esc_attr( $iw_name ); ?>" style="display:none;">
				<select name="<?php echo esc_attr( $name_attr . '[' . $iw_name . ']' ); ?>">			

					<?php foreach ( $templates as $template_value => $template_name ) { ?>

						<?php $selected = ( isset( $value[ $iw_name ] ) && $value[ $iw_name ] == $template_value ) ? 'selected="selected"' : ''; ?>

						<option value="<?php echo esc_attr( $template_value ); ?>" <?php echo $selected; ?>>
							<?php echo esc_html( $template_name ); ?>	
						</option>
					<?php } ?>
				</select>
			</div>
			<?php
		}
		?>
		</div>
		<p id="info-window-no-templates-message" style="display: none;">
			<?php _e( 'Template files availabe when using AJAX only.', 'gmw-premium-settings' ); ?>
		</p>
		<?php
	}

	/**
	 * Validate info window settings
	 *
	 * @param  [type] $output [description]
	 * @return [type]         [description]
	 */
	public static function validate_info_window_template( $output ) {

		if ( ! is_array( $output ) ) {
			$output = array();
		}

		$output = array_map( 'sanitize_key', $output );

		return $output;
	}

	/**
	 * Wider search settings
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 */
	public static function wider_search( $value, $name_attr ) {
		?>
		<div class="gmw-options-box">
			<div class="single-option">	
				<label><?php echo _e( 'Distance', 'gmw-premium-settings' ); ?></label>
				<div class="option-content">	
					<input 
						type="number" 
						size="10"
						placeholder="Enter numeric value"
						name="<?php echo esc_attr( $name_attr ) . '[radius]'; ?>" 
						value="<?php echo ! empty( $value['radius'] ) ? esc_attr( stripslashes( $value['radius'] ) ) : ''; ?>" 
					/>
					<p class="description">
						<?php _e( 'Enter a distance value of the wider search.', 'gmw-premium-settings' ); ?>
					</p>
				</div>
			</div>

			<div class="single-option">	
				<label><?php echo _e( 'Link text', 'gmw-premium-settings' ); ?></label>
				<div class="option-content">	
					<input 
						type="text" 
						placeholder="Enter text"
						name="<?php echo esc_attr( $name_attr ) . '[link_text]'; ?>" 
						value="<?php echo ! empty( $value['link_text'] ) ? esc_attr( stripslashes( $value['link_text'] ) ) : 'click here'; ?>"
					/>
					<p class="description">
						<?php _e( 'Enter text that will be used as the wider search link in the results message. Then use the <code>{wider_search_link}</code> placeholder anywhere in the "No results" message below where you would like to display the link.', 'gmw-premium-settings' ); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Validate wider search settings
	 *
	 * @param  [type] $output [description].
	 *
	 * @return [type]         [description]
	 */
	public static function validate_wider_search( $output ) {

		$output['radius']    = ! empty( $output['radius'] ) ? preg_replace( '/[^0-9]/', '', $output['radius'] ) : '';
		$output['link_text'] = ! empty( $output['link_text'] ) ? stripslashes( sanitize_text_field( $output['link_text'] ) ) : 'click here';

		return $output;
	}

	/**
	 * No Results Message.
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 *
	 * @param  [type] $form      [description].
	 */
	public static function no_results_message( $value, $name_attr, $form ) {

		$value = ! empty( $value ) ? $value : '';

		/***** To support when upgrading from a previous version. will be removed in the future. */

		if ( isset( $form['no_results']['wider_search']['enabled'] ) && ! empty( $form['no_results']['wider_search']['link_text'] ) ) {

			$before = ! empty( $form['no_results']['wider_search']['before_link'] ) ? $form['no_results']['wider_search']['before_link'] . ' ' : '';
			$after  = ! empty( $form['no_results']['wider_search']['after_link'] ) ? ' ' . $form['no_results']['wider_search']['after_link'] : '';

			$wider_search = '<p>' . $before . '{widwer_search_link}' . $after . '</p>';

			$value .= '. ' . $wider_search;
		}

		if ( isset( $form['no_results']['all_results']['enabled'] ) && ! empty( $form['no_results']['all_results']['link_text'] ) ) {

			$before = ! empty( $form['no_results']['all_results']['before_link'] ) ? $form['no_results']['all_results']['before_link'] . ' ' : '';
			$after  = ! empty( $form['no_results']['all_results']['after_link'] ) ? ' ' . $form['no_results']['all_results']['after_link'] : '';

			$all_results = '<p>' . $before . '{all_results_link}' . $after . '</p>.';

			$value .= '. ' . $all_results;
		}

		/***** ***** */

		?>
		<p><?php _e( '<ol><li>Set the Wider Search and No Results settings above as you wish.</li><li>Enter in the textbox, text that will show when no results found.</li><li>Use the placehoder <code>{wider_search_link}</code> where you would like the "wider search" link to be in the message.</li><li>Use the placehoder <code>{all_results_link}</code> where you would like the "all results" link to be in the message.</li><li>You can use the HTML characters p,a,br,em, and strong in your message.</li><p>An example: No results found. {wider_search_link} to search within a wider radius, or {all_results_link} to view all locations.<p></ol>', 'gmw-premium-settings' ); ?>
			<textarea 
				name="<?php echo esc_attr( $name_attr ); ?>" 
				rows="8"
				cols="0"
				style="width:400px;"><?php echo esc_textarea( stripslashes( $value ) ); ?></textarea>
		</p>
		<?php
	}

	/**
	 * Validate no results message
	 *
	 * @param  [type] $output [description].
	 *
	 * @return [type]         [description]
	 */
	public static function validate_no_results_message( $output ) {

		$allowed = array(
			'a'      => array(
				'href'  => array(),
				'title' => array(),
				'alt'   => array(),
			),
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
			'p'      => array(),
		);

		return wp_kses( $output, $allowed );
	}

	/**
	 * Map styles list to pick from
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 */
	public static function snazzy_maps_styles( $value, $name_attr ) {
		$name_attr = esc_attr( $name_attr );
		?>
		<p>
			<input 
				type="radio" 
				value="" 
				name="<?php echo $name_attr; ?>" 
				checked="checked"
			/>
			<label><?php _e( 'Disabled', 'gmw-premium-settings' ); ?></label>
		</p>

		<?php

		$styles = get_option( 'SnazzyMapStyles', null );

		if ( $styles == null ) {
			$styles = array();
		}

		if ( count( $styles ) > 0 ) {
			?>

			<div id="gmw-map-styles-wrapper">

				<?php foreach ( (array) $styles as $index => $style ) { ?> 

					<div class="single-map-style">       		
						<label>
							<img src="<?php echo esc_url( $style['imageUrl'] ); ?>" alt="<?php echo esc_attr( $style['name'] ); ?>" />
							<input 
								type="radio" 
								value="<?php echo esc_attr( $style['id'] ); ?>" 
								name="<?php echo $name_attr; ?>"
								<?php
								if ( ! empty( $value ) && $value == $style['id'] ) {
									echo 'checked="checked"'; }
								?>
							/>
							<span><?php echo esc_html( $style['name'] ); ?></span>
						</label>
					</div>
				<?php } ?>
			</div>

		<?php } elseif ( class_exists( 'SnazzyMaps_Services_JSON' ) ) { ?>
			<div class="nothing">
				<p><?php printf( __( 'Looks like you haven\'t picked any styles yet. <a href="%s">Explore</a> and choose some styles to be used with your forms', 'gmw-premium-settings' ), '?page=snazzy_maps&tab=1' ); ?></p>
			</div>
		<?php } else { ?>
			<div class="no-plugin">
				<p><?php printf( __( 'This feature requires the <a href="%s" target="_blank">Snazzy Maps plugin</a>.', 'gmw-premium-settings' ), 'https://wordpress.org/plugins/snazzy-maps/' ); ?></p>
			</div>
		<?php
}
	}

	/**
	 * Validate snazzy maps
	 *
	 * @param  [type] $output [description].
	 *
	 * @return [type]         [description]
	 */
	public static function validate_snazzy_maps_styles( $output ) {
		return ! empty( $output ) ? absint( $output ) : '';
	}
}
