<?php
/**
 * GMW Premium Settings - Groups Locator form settings.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_PT_Admin class
 */
class GMW_PS_GL_Form_Settings {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		add_filter( 'gmw_form_default_settings', array( $this, 'default_settings' ), 10, 2 );
		add_action( 'gmw_form_settings', array( $this, 'form_settings' ), 10, 2 );

		add_action( 'gmw_form_settings_ps_bp_include_exclude_group_types', array( $this, 'include_exclude_bp_group_types' ), 10, 2 );
		add_action( 'gmw_form_settings_ps_bp_group_types', array( $this, 'bp_group_types' ), 10, 3 );

		// fields validations.
		add_action( 'gmw_validate_form_settings_ps_bp_include_exclude_group_types', array( $this, 'validate_include_exclude_bp_group_types' ), 10, 2 );
		add_filter( 'gmw_validate_form_settings_ps_bp_group_types', array( $this, 'validate_bp_group_types' ), 10, 2 );
	}

	/**
	 * Default settings
	 *
	 * @param  array $settings form settings.
	 *
	 * @param  array $args     arguments.
	 *
	 * @return [type]           [description]
	 */
	public function default_settings( $settings, $args ) {

		if ( 'bp_groups_locator' === $form['component'] ) {

			$settings['search_results']['orderby'] = 'distance:Distance,alphabetical:Name,active:Last Active,newest:Newest,popular:Popular,online:Online,random:Random';

			$settings['page_load_results']['include_exclude_group_types'] = array(
				'usage'       => 'disabled',
				'group_types' => array(),
			);

			$settings['search_form']['group_types_filter'] = array(
				'usage'            => 'pre_defined',
				'group_types'      => array(),
				'label'            => 'Group types',
				'show_options_all' => 'All Types',
			);
		}

		return $settings;
	}

	/**
	 * Form Settings for all forms types
	 *
	 * @param  array $form_fields form fields.
	 *
	 * @param  array $form         gmw form.
	 *
	 * @return [type]           [description]
	 */
	public function form_settings( $form_fields, $form ) {

		if ( 'bp_groups_locator' !== $form['component'] ) {
			return $form_fields;
		}

		if ( 'global_maps' !== $form['addon'] && 'ajax_forms' !== $form['addon'] ) {

			$form_fields['page_load_results']['orderby'] = array(
				'name'       => 'orderby',
				'type'       => 'select',
				'default'    => 'distance',
				'label'      => __( 'Orderby', 'gmw-premium-settings' ),
				'desc'       => __( 'Select the order of the results.', 'gmw-premium-settings' ),
				'options'    => array(
					'distance'     => __( 'Distance', 'gmw-premium-settings' ),
					'active'       => __( 'Active', 'gmw-premium-settings' ),
					'alphabetical' => __( 'Alphabetical', 'gmw-premium-settings' ),
					'popular'      => __( 'Popular', 'gmw-premium-settings' ),
					'newest'       => __( 'Newest', 'gmw-premium-settings' ),
				),
				'attributes' => array(),
				'priority'   => 95,
			);

			$form_fields['form_submission']['orderby'] = array(
				'name'       => 'orderby',
				'type'       => 'select',
				'default'    => 'distance',
				'label'      => __( 'Orderby', 'gmw-premium-settings' ),
				'desc'       => __( 'Select the default order of the results on form submission.', 'gmw-premium-settings' ),
				'options'    => array(
					'distance'     => __( 'Distance', 'gmw-premium-settings' ),
					'active'       => __( 'Active', 'gmw-premium-settings' ),
					'alphabetical' => __( 'Alphabetical', 'gmw-premium-settings' ),
					'popular'      => __( 'Popular', 'gmw-premium-settings' ),
					'newest'       => __( 'Newest', 'gmw-premium-settings' ),
				),
				'attributes' => array(),
				'priority'   => 25,
			);

			$form_fields['search_results']['orderby'] = array(
				'name'        => 'orderby',
				'type'        => 'text',
				'placeholder' => 'ex. distance:Distance,alphabetical:Name,active:Last Active',
				'default'     => '',
				'label'       => __( 'Orderby', 'gmw-premium-settings' ),
				'desc'        => __( '<p>Generate an orderby select dropdown menu to display in the search results ( leave blank to omit ).</p><p> - Enter sets of value:label, comma separated and in the order that you would like them to appear in the dropdown menu. For ex. distance:Distance, alphabetical:Name, active:Last Active.</p><p>The availabe orderby values are distance, active, newest, popular, and alphabetical.</p>', 'gmw-premium-settings' ),
				'priority'    => 18,
			);
		}

		$form_fields['page_load_results']['include_exclude_group_types'] = array(
			'name'       => 'include_exclude_group_types',
			'type'       => 'function',
			'function'   => 'ps_bp_include_exclude_group_types',
			'default'    => '',
			'label'      => __( 'Include / Exclude Group Types', 'gmw-premium-settings' ),
			'desc'       => __( 'Include or exclude specific group types.', 'gmw-premium-settings' ),
			'attributes' => array(),
			'priority'   => 15,
		);

		$form_fields['search_form']['group_types_filter'] = array(
			'name'       => 'group_types_filter',
			'type'       => 'function',
			'function'   => 'ps_bp_group_types',
			'default'    => '',
			'label'      => __( 'Group Types', 'gmw-premium-settings' ),
			'desc'       => __( 'Setup the group types filter.', 'gmw-premium-settings' ),
			'attributes' => array(),
			'priority'   => 15,
		);

		$map_icons_options = array(
			'global' => __( 'Global', 'gmw-premium-settings' ),
			'avatar' => __( 'Groups avatar', 'gmw-premium-settings' ),
		);

		if ( gmw_get_option( 'bp_groups_locator', 'per_group_map_icon', false ) ) {
			$map_icons_options['per_group'] = __( 'Per group', 'gmw-premium-settings' );
		}

		$form_fields['map_markers']['usage'] = array(
			'name'       => 'usage',
			'type'       => 'select',
			'default'    => 'global',
			'label'      => __( 'Map icons usage', 'gmw-premium-settings' ),
			'desc'       => __( 'Select the map markers usage.', 'gmw-premium-settings' ),
			'options'    => $map_icons_options,
			'attributes' => array(),
			'priority'   => 13,
		);

		return $form_fields;
	}

	/**
	 * Include / Exclude group types
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 *
	 * @return [type]            [description]
	 */
	public function include_exclude_bp_group_types( $value, $name_attr ) {

		if ( empty( $value ) ) {
			$value = array(
				'usage'       => 'disabled',
				'group_types' => array(),
			);
		}

		// look for and get group types.
		$group_types = bp_groups_get_group_types( array(), 'object' );

		if ( empty( $group_types ) ) {
			?>
			<p><?php esc_html_e( 'No group types were found.', 'gmw-premium-settings' ); ?></p>

			<input 
				type="hidden" 
				name="<?php echo esc_attr( $name_attr ); ?>[usage]" 
				value="disabled" 
			/>
			<input 
				type="hidden" 
				name="<?php echo esc_attr( $name_attr ); ?>[group_types]" 
				value="" 
			/>
			<?php
			return;
		}
		?>
		<div class="gmw-options-box bp-include-exclude-group-types-settings-wrapper">  

			<div class="single-option usage">   

				<label><?php esc_html_e( 'Usage', 'gmw-premium-settings' ); ?></label>    

				<div class="option-content">

					<select name="<?php echo esc_attr( $name_attr ); ?>[usage]">

						<option value="disabled" selected="selected">
							<?php esc_html_e( 'Disabled', 'gmw-premium-settings' ); ?>
						</option>

						<option value="include" <?php echo 'include' === $value['usage'] ? 'selected="selected"' : ''; ?>>
							<?php esc_html_e( 'Include', 'gmw-premium-settings' ); ?>
						</option>
						<option value="exclude" <?php echo 'exclude' === $value['usage'] ? 'selected="selected"' : ''; ?>>
							<?php esc_html_e( 'Exclude', 'gmw-premium-settings' ); ?>
						</option>
					</select>
					<p class="description">
						<?php esc_html_e( 'Select to disable, include or exclude group type.', 'gmw-premium-settings' ); ?>
					</p>
				</div>
			</div>

			<div class="single-option terms">

				<label><?php esc_html_e( 'Group types to include or exclude', 'gmw-premium-settings' ); ?></label>  

				<div class="option-content">

					<?php $set_taxes = ! empty( $value['group_types'] ) ? $value['group_types'] : array(); ?>

					<select multiple name="<?php echo esc_attr( $name_attr ); ?>[group_types][]">

						<?php foreach ( $group_types as $group_type ) { ?>

							<?php $selected = ( isset( $value['group_types'] ) && in_array( $group_type->name, $value['group_types'], true ) ) ? 'selected="selected"' : ''; ?>

							<option <?php echo $selected; // WPCS: XSS ok. ?> value="<?php echo esc_attr( $group_type->name ); ?>">
								<?php echo esc_html( $group_type->labels['name'] ); ?>
							</option>
						<?php } ?>

					</select>

					<p class="description">
						<?php esc_html_e( 'Select the group types to include or exclude.', 'gmw-premium-settings' ); ?>
					</p>
				</div>

			</div>      
		</div>

		<?php
	}

	/**
	 * Validate bp group types
	 *
	 * @param  array $output input values before validation.
	 *
	 * @param  array $form   gmw form.
	 *
	 * @return array validated input
	 */
	public static function validate_include_exclude_bp_group_types( $output, $form ) {

		$group_types_object = bp_groups_get_group_types( array(), 'object' );
		$temp               = array();

		// save the groups data as array of array( label => name ) to
		// easily display it on the front-end.
		if ( ! empty( $output['group_types'] ) ) {

			foreach ( $output['group_types'] as $key => $group_type ) {

				$label = $group_types_object[ $group_type ]->labels['name'];

				$temp[ sanitize_text_field( $label ) ] = sanitize_key( $group_type );
			}

			$output['group_types'] = array_filter( $temp );

		} else {

			$output['group_types'] = array();
		}

		$options = array(
			'disabled',
			'include',
			'exclude',
		);

		if ( ! isset( $output['usage'] ) || ! in_array( $output['usage'], $options, true ) ) {
			$output['usage'] = 'disabled';
		}

		return $output;
	}

	/**
	 * BP group types form setting
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 *
	 * @param  [type] $form      [description].
	 *
	 * @return [type]            [description]
	 */
	public static function bp_group_types( $value, $name_attr, $form ) {

		// show message if Xprofile Fields component deactivated.
		if ( ! class_exists( 'Buddypress' ) || ! bp_is_active( 'groups' ) ) {
			return esc_html_e( 'Buddypress Groups component is required for this feature.', 'gmw-premium-settings' );
		}

		// verify that value exist, or generate default.
		if ( empty( $value ) || ! is_array( $value ) ) {
			$value = array(
				'usage'            => 'pre_defined',
				'group_types'      => array(),
				'label'            => 'Group types',
				'show_options_all' => 'All Types',
			);
		}

		// look for and get group types.
		$group_types = bp_groups_get_group_types( array(), 'object' );

		if ( empty( $group_types ) ) {
			?>
			<p><?php esc_html_e( 'No group types were found.', 'gmw-premium-settings' ); ?></p>

			<input 
				type="hidden" 
				name="<?php echo esc_attr( $name_attr ); ?>[usage]" 
				value="disabled" 
			/>
			<input 
				type="hidden" 
				name="<?php echo esc_attr( $name_attr ); ?>[group_types]" 
				value="" 
			/>
			<input 
				type="hidden" 
				name="<?php echo esc_attr( $name_attr ); ?>[label]" 
				value="Group types" 
			/>
			<input 
				type="hidden" 
				name="<?php echo esc_attr( $name_attr ); ?>[show_options_all]" 
				value="All Types" 
			/>

			<?php

			return;
		}

		?>
		<div class="bp-group-types-settings-wrapper gmw-options-box">

			<div class="bp-group-types-setting single-option usage">  

				<label>
					<?php esc_html_e( 'Usage', 'gmw-premium-settings' ); ?>      
				</label>    

				<div class="option-content">

					<?php $value['usage'] = isset( $value['usage'] ) ? $value['usage'] : 'pre_defined'; ?>

					<select name="<?php echo esc_attr( $name_attr ); ?>[usage]" id="bp-group-types-usage">

						<option value="pre_defined" <?php echo 'pre_defined' === $value['usage'] ? 'selected="selected"' : ''; ?>>
							<?php esc_html_e( 'Pre-defined', 'gmw-premium-settings' ); ?>
						</option>

						<option value="dropdown" <?php echo 'dropdown' === $value['usage'] ? 'selected="selected"' : ''; ?>>
							<?php esc_html_e( 'Dropdown', 'gmw-premium-settings' ); ?>
						</option>

						<option value="checkboxes" <?php echo 'checkboxes' === $value['usage'] ? 'selected="selected"' : ''; ?>>
							<?php esc_html_e( 'Checkboxes', 'gmw-premium-settings' ); ?>
						</option>

						<?php if ( gmw_is_addon_active( 'premium_settings' ) ) { ?>

							<option value="smartbox" <?php echo 'smartbox' === $value['usage'] ? 'selected="selected"' : ''; ?>>
								<?php esc_html_e( 'Smartbox', 'gmw-premium-settings' ); ?>
							</option>

							<option value="smartbox_multiple" <?php echo 'smartbox_multiple' === $value['usage'] ? 'selected="selected"' : ''; ?>>
								<?php esc_html_e( 'Smartbox ( multiple selections )', 'gmw-premium-settings' ); ?>
							</option>

						<?php } ?>

					</select>
					<p class="description">
						<?php esc_html_e( 'Select "Pre defined" to omit the filter from the search form, but your can still filter group types based on the selected types below. Otherwise, select how to display the filter in the search from.', 'gmw-premium-settings' ); ?>
					</p>
				</div>
			</div>

			<div class="bp-group-types-setting single-option groups">
				<label>
					<?php echo esc_html_e( 'Select Group Types', 'gmw-premium-settings' ); ?> 
				</label>
				<div class="option-content">
					<select multiple="multiple" data-placeholder="Select some group types..." name="<?php echo esc_attr( $name_attr ); ?>[group_types][]" id="" class="bp-groups-selector">

						<?php foreach ( $group_types as $group_type ) { ?>

							<?php $selected = ( isset( $value['group_types'] ) && in_array( $group_type->name, $value['group_types'], true ) ) ? 'selected="selected"' : ''; ?>

							<option <?php echo $selected; // WPCS: XSS ok. ?> value="<?php echo esc_attr( $group_type->name ); ?>">
								<?php echo esc_html( $group_type->labels['name'] ); ?>
							</option>
						<?php } ?>

					</select>
					<p class="description">
						<?php esc_html_e( 'Select group types to use in the filter or leave blank to use all types.', 'gmw-premium-settings' ); ?>
					</p>
				</div>
			</div>

			<div class="single-option label">
				<label><?php esc_html_e( 'Label', 'gmw-premium-settings' ); ?></label>   
				<div class="option-content">
					<input 
						type="text" 
						placeholder="<?php esc_html_e( 'Field label', 'gmw-premium-settings' ); ?>" 
						name="<?php echo esc_attr( $name_attr ); ?>[label]" 
						value="<?php echo ! empty( $value['label'] ) ? esc_attr( stripslashes( $value['label'] ) ) : ''; ?>" 
					/>
					<p class="description">
						<?php esc_html_e( 'Enter field label or leave blank to omit.', 'gmw-premium-settings' ); ?>
					</p>
				</div>
			</div>

			<div class="single-option options-all">
				<label><?php esc_html_e( 'Options all label', 'gmw-premium-settings' ); ?></label>    
				<div class="option-content">
					<input 
						type="text" 
						placeholder="<?php esc_html_e( 'Options all label', 'gmw-premium-settings' ); ?>" 
						name="<?php echo esc_attr( $name_attr ); ?>[show_options_all]" 
						value="<?php echo ! empty( $value['show_options_all'] ) ? esc_attr( stripcslashes( $value['show_options_all'] ) ) : ''; ?>" 
					/>
					<p class="description">
						<?php esc_html_e( 'Enter the options all label.', 'gmw-premium-settings' ); ?>
					</p>
				</div>
			</div>

		</div>
		<?php
	}

	/**
	 * Validate bp group types
	 *
	 * @param  array $output input values before validation.
	 *
	 * @param  array $form   gmw form.
	 *
	 * @return array validated input
	 */
	public static function validate_bp_group_types( $output, $form ) {

		$group_types_object = bp_groups_get_group_types( array(), 'object' );
		$temp               = array();

		if ( ! empty( $output['group_types'] ) ) {

			foreach ( $output['group_types'] as $key => $group_type ) {

				$label = $group_types_object[ $group_type ]->labels['name'];

				$temp[ sanitize_text_field( $label ) ] = sanitize_key( $group_type );
			}

			$output['group_types'] = array_filter( $temp );

		} else {

			$output['group_types'] = array();
		}

		$options = array(
			'disabled',
			'pre_defined',
			'dropdown',
			'checkboxes',
			'smartbox',
			'smartbox_multiple',
		);

		if ( ! isset( $output['usage'] ) || ! in_array( $output['usage'], $options, true ) ) {
			$output['usage'] = 'disabled';
		}

		$output['label']            = sanitize_text_field( $output['label'] );
		$output['show_options_all'] = sanitize_text_field( $output['show_options_all'] );

		return $output;
	}
}
new GMW_PS_GL_Form_Settings();
?>
