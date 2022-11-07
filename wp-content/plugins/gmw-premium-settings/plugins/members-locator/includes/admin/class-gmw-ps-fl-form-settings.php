<?php
/**
 * GMW Premium Settings - Members Locator form settings.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_PT_Admin class
 */
class GMW_PS_FL_Form_Settings {

	/**
	 * Plugin options
	 *
	 * @var array
	 */
	public $options;

	/**
	 * __construct function.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function __construct() {

		// default settings.
		add_filter( 'gmw_form_default_settings', array( $this, 'default_settings' ), 15, 2 );

		// settings fields.
		add_action( 'gmw_form_settings', array( $this, 'form_settings' ), 15, 2 );

		// custom settings.
		add_action( 'gmw_form_settings_ps_bp_include_exclude_member_types', array( $this, 'include_exclude_bp_member_types' ), 15, 2 );
		add_action( 'gmw_form_settings_ps_bp_member_types', array( $this, 'bp_member_types' ), 10, 3 );
		add_action( 'gmw_form_settings_ps_fl_bp_groups', array( $this, 'bp_groups' ), 15, 3 );

		add_action( 'gmw_members_locator_ajax_form_settings_xprofile_fields', array( 'GMW_Form_Settings_Helper', 'bp_xprofile_fields' ), 10, 2 );

		// fields validations.
		add_action( 'gmw_validate_form_settings_ps_bp_include_exclude_member_types', array( $this, 'validate_include_exclude_bp_member_types' ), 15, 2 );
		add_action( 'gmw_validate_form_settings_ps_bp_member_types', array( $this, 'validate_bp_member_types' ), 10, 3 );
		add_filter( 'gmw_validate_form_settings_ps_fl_bp_groups', array( $this, 'validate_bp_groups' ), 15, 2 );
		add_filter( 'gmw_members_locator_ajax_validate_form_settings_xprofile_fields', array( 'GMW_Form_Settings_Helper', 'validate_bp_xprofile_fields' ) );
	}

	/**
	 * Default settings
	 *
	 * @param  array $settings settings.
	 *
	 * @param  array $args     arguments.
	 *
	 * @return [type]           [description]
	 */
	public function default_settings( $settings, $args ) {

		if ( 'members_locator' === $args['component'] ) {

			$settings['search_results']['orderby'] = 'distance:Distance,alphabetical:Name,active:Last Active,newest:Newest,popular:Popular,online:Online,random:Random';

			$settings['page_load_results']['include_exclude_member_types'] = array(
				'usage'        => 'disabled',
				'member_types' => array(),
			);

			$settings['search_form']['member_types_filter'] = array(
				'usage'            => 'pre_defined',
				'member_types'     => array(),
				'label'            => 'Member types',
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
	 * @param  array $form        gmw form.
	 *
	 * @return [type]           [description]
	 */
	public function form_settings( $form_fields, $form ) {

		if ( 'members_locator' !== $form['component'] ) {
			return $form_fields;
		}

		// no need for global map.
		if ( 'global_maps' !== $form['addon'] ) {

			if ( 'ajax_forms' !== $form['addon'] ) {

				$form_fields['page_load_results']['orderby'] = array(
					'name'       => 'orderby',
					'type'       => 'select',
					'default'    => 'distance',
					'label'      => __( 'Orderby', 'gmw-premium-settings' ),
					'desc'       => __( 'Select the order of the results.', 'gmw-premium-settings' ),
					'options'    => array(
						'distance'     => __( 'Distance', 'gmw-premium-settings' ),
						'active'       => __( 'Active', 'gmw-premium-settings' ),
						'newest'       => __( 'Newest', 'gmw-premium-settings' ),
						'popular'      => __( 'Popular', 'gmw-premium-settings' ),
						'online'       => __( 'Online', 'gmw-premium-settings' ),
						'alphabetical' => __( 'Alphabetical', 'gmw-premium-settings' ),
						'random'       => __( 'Random', 'gmw-premium-settings' ),
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
						'newest'       => __( 'Newest', 'gmw-premium-settings' ),
						'popular'      => __( 'Popular', 'gmw-premium-settings' ),
						'online'       => __( 'Online', 'gmw-premium-settings' ),
						'alphabetical' => __( 'Alphabetical', 'gmw-premium-settings' ),
						'random'       => __( 'Random', 'gmw-premium-settings' ),
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
					'desc'        => __( '<p>Generate an orderby select dropdown menu to display in the search results ( leave blank to omit ).</p><p> - Enter sets of value:label, comma separated and in the order that you would like them to appear in the dropdown menu. For ex. distance:Distance, alphabetical:Name, active:Last Active.</p><p>The availabe orderby values are distance, active, newest, popular, online, alphabetical and random.</p>', 'gmw-premium-settings' ),
					'priority'    => 18,
				);
			}

			// This field executed using the same hook as the search form xprofile fields settings.
			$form_fields['search_results']['xprofile_fields'] = array(
				'name'       => 'xprofile_fields',
				'type'       => 'function',
				'function'   => 'xprofile_fields',
				'default'    => '',
				'label'      => __( 'Xprofile Fields', 'gmw-premium-settings' ),
				'desc'       => __( 'Select the profile fields which you would like to display for each member in the list of results.', 'gmw-premium-settings' ),
				'attributes' => '',
				'priority'   => 30,
			);

			// This field executed using the same hook as the search form xprofile fields settings.
			$form_fields['info_window']['xprofile_fields'] = array(
				'name'     => 'xprofile_fields',
				'type'     => 'function',
				'function' => 'xprofile_fields',
				'default'  => '',
				'label'    => __( 'Xprofile Fields', 'gmw-premium-settings' ),
				'desc'     => __( 'Select the profile fields which you would like to display. <p style="color:red;font-size:11px;"> * Note - this feature require AJAX content to be enabled.</p>', 'gmw-premium-settings' ),
				'priority' => 95,
			);
		}

		$form_fields['page_load_results']['include_exclude_member_types'] = array(
			'name'       => 'include_exclude_member_types',
			'type'       => 'function',
			'function'   => 'ps_bp_include_exclude_member_types',
			'default'    => '',
			'label'      => __( 'Include / Exclude Member Types', 'gmw-premium-settings' ),
			'desc'       => __( 'Include or exclude members by member types.', 'gmw-premium-settings' ),
			'attributes' => array(),
			'priority'   => 15,
		);

		$form_fields['search_form']['member_types_filter'] = array(
			'name'       => 'member_types_filter',
			'type'       => 'function',
			'function'   => 'ps_bp_member_types',
			'default'    => '',
			'label'      => __( 'Member Types', 'gmw-premium-settings' ),
			'desc'       => __( 'Setup the member types filter.', 'gmw-premium-settings' ),
			'attributes' => array(),
			'priority'   => 18,
		);

		$form_fields['search_form']['bp_groups'] = array(
			'name'       => 'bp_groups',
			'type'       => 'function',
			'function'   => 'ps_fl_bp_groups',
			'default'    => '',
			'label'      => __( 'BuddyPress Groups', 'gmw-premium-settings' ),
			'desc'       => __( 'Setup the groups filter.', 'gmw-premium-settings' ),
			'attributes' => array(),
			'priority'   => 19,
		);

		$map_icons_options = array(
			'global' => __( 'Global', 'gmw-premium-settings' ),
			'avatar' => __( 'Member avatar', 'gmw-premium-settings' ),
		);

		if ( gmw_get_option( 'members_locator', 'per_member_map_icon', false ) ) {
			$map_icons_options['per_member'] = __( 'Per member', 'gmw-premium-settings' );
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
	 * Include / Exclude member types
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 *
	 * @return [type]            [description]
	 */
	public function include_exclude_bp_member_types( $value, $name_attr ) {

		if ( empty( $value ) ) {
			$value = array(
				'usage'        => 'disabled',
				'member_types' => array(),
			);
		}

		// look for and get member types.
		$member_types = bp_get_member_types( array(), 'object' );

		if ( empty( $member_types ) ) {
			?>
			<p><?php esc_html_e( 'No member types were found.', 'gmw-premium-settings' ); ?></p>
			<input 
				type="hidden" 
				name="<?php echo esc_attr( $name_attr ); ?>[usage]" 
				value="disabled" 
			/>
			<input 
				type="hidden" 
				name="<?php echo esc_attr( $name_attr ); ?>[member_types]" 
				value="" 
			/>
			<?php

			return;
		}

		?>
		<div class="gmw-options-box bp-include-exclude-member-types-settings-wrapper">  

			<div class="single-option usage">   

				<label><?php esc_html_e( 'Usage', 'gmw-premium-settings' ); ?></label>    

				<div class="option-content">

					<select name="<?php echo esc_attr( $name_attr ); ?>[usage]">

						<option value="disabled" selected="selected">
							<?php esc_html_e( 'Disabled', 'gmw-premium-settings' ); ?>
						</option>

						<option value="include" <?php echo ( 'include' === $value['usage'] ) ? 'selected="selected"' : ''; ?>>
							<?php esc_html_e( 'Include', 'gmw-premium-settings' ); ?>
						</option>

						<option value="exclude" <?php echo ( 'exclude' === $value['usage'] ) ? 'selected="selected"' : ''; ?>>
							<?php esc_html_e( 'Exclude', 'gmw-premium-settings' ); ?>
						</option>
					</select>

					<p class="description">
						<?php esc_html_e( 'Select to disable, include or exclude member type.', 'gmw-premium-settings' ); ?>
					</p>
				</div>
			</div>

			<div class="single-option terms">   

				<label><?php esc_html_e( 'Member types to include or exclude', 'gmw-premium-settings' ); ?></label>  

				<div class="option-content">

					<?php $set_taxes = ! empty( $value['member_types'] ) ? $value['member_types'] : array(); ?>

					<select multiple name="<?php echo esc_attr( $name_attr ); ?>[member_types][]">

						<?php foreach ( $member_types as $member_type ) { ?>

							<?php $selected = ( isset( $value['member_types'] ) && in_array( $member_type->name, $value['member_types'], true ) ) ? 'selected="selected"' : ''; ?>

							<option <?php echo $selected; // WPCS: XSS ok. ?> value="<?php echo esc_attr( $member_type->name ); ?>"><?php echo esc_html( $member_type->labels['name'] ); ?></option>
						<?php } ?>

					</select>

					<p class="description">
						<?php esc_html_e( 'Select the member types to include or exclude.', 'gmw-premium-settings' ); ?>
					</p>
				</div>

			</div>      
		</div>

		<?php
	}

	/**
	 * Validate bp member types
	 *
	 * @param  array $output input values before validation.
	 *
	 * @param  array $form   gmw form.
	 *
	 * @return array validated input
	 */
	public static function validate_include_exclude_bp_member_types( $output, $form ) {

		$member_types_object = bp_get_member_types( array(), 'object' );
		$temp                = array();

		// save the members data as array of array( label => name ) to
		// easily display it on the front-end.
		if ( ! empty( $output['member_types'] ) ) {

			foreach ( $output['member_types'] as $key => $member_type ) {

				$label = $member_types_object[ $member_type ]->labels['name'];

				$temp[ sanitize_text_field( $label ) ] = sanitize_key( $member_type );
			}

			$output['member_types'] = array_filter( $temp );

		} else {

			$output['member_types'] = array();
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
	 * BP member types form setting
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 *
	 * @param  [type] $form      [description].
	 *
	 * @return [type]            [description]
	 */
	public static function bp_member_types( $value, $name_attr, $form ) {

		// verify that value exist, or generate default.
		if ( empty( $value ) || ! is_array( $value ) ) {
			$value = array(
				'usage'            => 'pre_defined',
				'member_types'     => array(),
				'label'            => 'Member types',
				'show_options_all' => 'All Types',
			);
		}

		// look for and get member types.
		$member_types = bp_get_member_types( array(), 'object' );

		if ( empty( $member_types ) ) {
			?>
			<p><?php esc_html_e( 'No member types were found.', 'gmw-premium-settings' ); ?></p>

			<input 
				type="hidden" 
				name="<?php echo esc_attr( $name_attr ); ?>[usage]" 
				value="disabled" 
			/>
			<input 
				type="hidden" 
				name="<?php echo esc_attr( $name_attr ); ?>[member_types]" 
				value="" 
			/>
			<input 
				type="hidden" 
				name="<?php echo esc_attr( $name_attr ); ?>[label]" 
				value="Member types" 
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
		<div class="bp-member-types-settings-wrapper gmw-options-box">

			<div class="bp-member-types-setting single-option usage">  

				<label>
					<?php esc_html_e( 'Usage', 'gmw-premium-settings' ); ?>      
				</label>    

				<div class="option-content">

					<?php $value['usage'] = isset( $value['usage'] ) ? $value['usage'] : 'pre_defined'; ?>

					<select name="<?php echo esc_attr( $name_attr ); ?>[usage]" id="bp-member-types-usage">

						<option value="pre_defined" <?php echo ( 'pre_defined' === $value['usage'] ) ? 'selected="selected"' : ''; ?>>
							<?php esc_html_e( 'Pre-defined', 'gmw-premium-settings' ); ?>
						</option>

						<option value="dropdown" <?php echo ( 'dropdown' === $value['usage'] ) ? 'selected="selected"' : ''; ?>>
							<?php esc_html_e( 'Dropdown', 'gmw-premium-settings' ); ?>
						</option>

						<option value="checkboxes" <?php echo ( 'checkboxes' === $value['usage'] ) ? 'selected="selected"' : ''; ?>>
							<?php esc_html_e( 'Checkboxes', 'gmw-premium-settings' ); ?>
						</option>

						<?php if ( gmw_is_addon_active( 'premium_settings' ) ) { ?>

							<option value="smartbox" <?php echo ( 'smartbox' === $value['usage'] ) ? 'selected="selected"' : ''; ?>>
								<?php esc_html_e( 'Smartbox', 'gmw-premium-settings' ); ?>
							</option>

							<option value="smartbox_multiple" <?php echo ( 'smartbox_multiple' === $value['usage'] ) ? 'selected="selected"' : ''; ?>>
								<?php esc_html_e( 'Smartbox ( multiple selections )', 'gmw-premium-settings' ); ?>
							</option>

						<?php } ?>

					</select>
					<p class="description">
						<?php esc_html_e( 'Select "Pre defined" to omit the filter from the search form, but your can still filter member types based on the selected types below. Otherwise, select how to display the filter in the search from.', 'gmw-premium-settings' ); ?>
					</p>
				</div>
			</div>

			<div class="bp-member-types-setting single-option members">
				<label>
					<?php echo esc_html_e( 'Select Member Types', 'gmw-premium-settings' ); ?> 
				</label>
				<div class="option-content">
					<select multiple="multiple" data-placeholder="Select some member types..." name="<?php echo esc_attr( $name_attr ); ?>[member_types][]" id="" class="bp-members-selector">

						<?php foreach ( $member_types as $member_type ) { ?>

							<?php $selected = ( isset( $value['member_types'] ) && in_array( $member_type->name, $value['member_types'], true ) ) ? 'selected="selected"' : ''; ?>

							<option <?php echo $selected; // WPCS: XSS ok. ?> value="<?php echo esc_attr( $member_type->name ); ?>"><?php echo esc_html( $member_type->labels['name'] ); ?></option>
						<?php } ?>

					</select>
					<p class="description">
						<?php esc_html_e( 'Select member types to use in the filter or leave blank to use all types.', 'gmw-premium-settings' ); ?>
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
	 * Validate bp member types
	 *
	 * @param  array $output input values before validation.
	 *
	 * @param  array $form   gmw form.
	 *
	 * @return array validated input
	 */
	public static function validate_bp_member_types( $output, $form ) {

		$member_types_object = bp_get_member_types( array(), 'object' );
		$temp                = array();

		if ( ! empty( $output['member_types'] ) ) {

			foreach ( $output['member_types'] as $key => $member_type ) {

				$label = $member_types_object[ $member_type ]->labels['name'];

				$temp[ sanitize_text_field( $label ) ] = sanitize_key( $member_type );
			}

			$output['member_types'] = array_filter( $temp );

		} else {

			$output['member_types'] = array();
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

	/**
	 * BP groups form settings
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 *
	 * @param  [type] $form      [description].
	 *
	 * @return [type]            [description]
	 */
	public static function bp_groups( $value, $name_attr, $form ) {

		global $bp;

		// show message if Xprofile Fields component deactivated.
		if ( ! class_exists( 'Buddypress' ) || ! bp_is_active( 'groups' ) ) {
			return esc_html_e( 'Buddypress Groups component is required for this feature.', 'gmw-premium-settings' );
		}

		if ( empty( $value ) ) {
			$value = array(
				'usage'            => 'pre_defined',
				'label'            => 'Groups',
				'show_options_all' => 'All groups',
			);
		}

		$groups = BP_Groups_Group::get(
			array(
				'type'     => 'alphabetical',
				'per_page' => 999,
			)
		);

		$groups = $groups['groups'];
		?>
		<div class="bp-groups-settings-wrapper gmw-options-box">

			<div class="bp-group-setting single-option usage">	

				<label><?php esc_html_e( 'Usage', 'gmw-premium-settings' ); ?></label>	
				   
				<div class="option-content">

					<?php $value['usage'] = isset( $value['usage'] ) ? $value['usage'] : 'pre_defined'; ?>

					<select name="<?php echo esc_attr( $name_attr ); ?>[usage]" id="bp-groups-usage">

						<option value="pre_defined" selected="selected">
							<?php esc_html_e( 'Pre-defined', 'gmw-premium-settings' ); ?>	
						</option>

						<option value="dropdown" <?php echo ( 'dropdown' === $value['usage'] ) ? 'selected="selected"' : ''; ?>>
							<?php esc_html_e( 'Dropdown', 'gmw-premium-settings' ); ?>
						</option>

						<option value="checkboxes" <?php echo ( 'checkboxes' === $value['usage'] ) ? 'selected="selected"' : ''; ?>>
							<?php esc_html_e( 'Checkboxes', 'gmw-premium-settings' ); ?>
						</option>

						<option value="smartbox" <?php echo ( 'smartbox' === $value['usage'] ) ? 'selected="selected"' : ''; ?>>
							<?php esc_html_e( 'Smartbox', 'gmw-premium-settings' ); ?>	
						</option>

						<option value="smartbox_multiple" <?php echo ( 'smartbox_multiple' === $value['usage'] ) ? 'selected="selected"' : ''; ?>>
							<?php esc_html_e( 'Smartbox ( multiple selections )', 'gmw-premium-settings' ); ?>
						</option>
					</select>
					<p class="description">
						<?php esc_html_e( 'Select "Pre defined" to omit the filter from the search form, but still filter members based on the selected groups below. Otherwise, select how to display the filter in the search from.', 'gmw-premium-settings' ); ?>
					</p>
				</div>
			</div>

			<div class="bp-group-setting single-option groups">
				<label>
					<?php echo esc_html_e( 'Select Groups', 'gmw-premium-settings' ); ?>	
				</label>

				<div class="option-content">

					<select multiple="multiple" data-placeholder="Select some groups..." name="<?php echo esc_attr( $name_attr ); ?>[groups][]" id="" class="bp-groups-selector">

						<?php foreach ( $groups as $group ) { ?>
							<?php
							if ( 0 === absint( $group->id ) ) {
								continue;
							}
							?>
							<?php $selected = ( isset( $value['groups'] ) && in_array( $group->id, $value['groups'], true ) ) ? 'selected="selected"' : ''; ?>

							<option <?php echo $selected; // WPCS: XSS ok. ?> value="<?php echo absint( $group->id ); ?>">
								<?php echo esc_html( $group->name ); ?>
							</option>

						<?php } ?>
					</select>
					<p class="description">
						<?php esc_html_e( 'Select groups to use in the filter or leave blank to use all groups.', 'gmw-premium-settings' ); ?>
					</p>
				</div>
			</div>

			<div class="single-option label" style="display:none;">

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

			<div class="single-option options-all" style="display:none;">
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
	 * Validate bp groups
	 *
	 * @param  array $output input values before validation.
	 *
	 * @param  array $form   gmw form.
	 *
	 * @return array validated input
	 */
	public static function validate_bp_groups( $output, $form ) {

		$output['enabled'] = isset( $output['enabled'] ) ? 1 : null;

		if ( ! empty( $output['groups'] ) ) {
			$output['groups'] = array_map( 'absint', $output['groups'] );
			$output['groups'] = array_filter( $output['groups'] );
		} else {
			$output['groups'] = array();
		}

		$options = array(
			'pre_defined',
			'dropdown',
			'checkboxes',
			'smartbox',
			'smartbox_multiple',
		);

		if ( ! isset( $output['usage'] ) || ! in_array( $output['usage'], $options, true ) ) {
			$output['usage'] = 'dropdown';
		}

		$output['label']       = isset( $output['label'] ) ? sanitize_text_field( $output['label'] ) : '';
		$output['placeholder'] = isset( $output['placeholder'] ) ? sanitize_text_field( $output['placeholder'] ) : '';

		return $output;
	}
}
new GMW_PS_FL_Form_Settings();
?>
