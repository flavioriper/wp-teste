<?php
/**
 * GMW Premium Settings - Members Locator admin settings.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_PS_FL_Admin class
 */
class GMW_PS_FL_Admin_Settings {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// admin settings.
		add_filter( 'gmw_members_locator_admin_settings', array( $this, 'settings' ), 10 );
		add_filter( 'gmw_admin_settings_setup_defaults', array( $this, 'setup_defaults' ) );
		add_filter( 'gmw_main_settings_displayed_member_location_tab', array( $this, 'displayed_member_location_tab' ), 20, 2 );
	}

	/**
	 * Generate default settings values
	 *
	 * @param  [type] $defaults [description].
	 *
	 * @return [type]           [description]
	 */
	public function setup_defaults( $defaults ) {

		$defaults['members_locator'] = array(
			'location_form_exclude_fields_groups'          => array(),
			'location_form_exclude_fields'                 => array(),
			'location_form_exclude_address_fields'         => array(),
			'displayed_member_location_tab_elements'       => array(
				'address',
				'map',
				'directions_link',
			),
			'displayed_member_location_tab_address_fields' => array(
				'address',
			),
			'activity_update_address_fields'               => array( 'address' ),
		);

		return $defaults;
	}

	/**
	 * Extend admin settings
	 *
	 * @param array $settings settings.
	 *
	 * @access public
	 *
	 * @return $settings
	 */
	public function settings( $settings ) {

		$settings['location_form_options'] = array(
			'name'       => 'location_form_options',
			'type'       => 'fields_group',
			'label'      => __( 'Member Location Form', 'gmw-premium-settings' ),
			'desc'       => __( 'Setup the member location form ( in the Location tab of the profile page ).', 'gmw-premium-settings' ),
			'fields'     => array(
				'location_form_exclude_fields_groups' => array(
					'name'       => 'location_form_exclude_fields_groups',
					'type'       => 'multiselect',
					'default'    => array(),
					'label'      => __( 'Exclude Fields Group', 'gmw-premium-settings' ),
					'desc'       => __( 'Select the fields groups that you would like to exclude from the location tab', 'gmw-premium-settings' ),
					'options'    => array(
						'location'    => __( 'Location', 'gmw-premium-settings' ),
						'address'     => __( 'Address', 'gmw-premium-settings' ),
						'coordinates' => __( 'Coordinates', 'gmw-premium-settings' ),
					),
					'attributes' => array(),
					'priority'   => 5,
				),
				'location_form_exclude_fields'        => array(
					'name'       => 'location_form_exclude_fields',
					'type'       => 'multiselect',
					'default'    => array(),
					'label'      => __( 'Exclude Location Form Fields', 'gmw-premium-settings' ),
					'desc'       => __( 'Select specific fields that you would like to exclude from the location form', 'gmw-premium-settings' ),
					'options'    => array(
						'address'      => __( 'Address ( with autocomplete )', 'gmw-premium-settings' ),
						'map'          => __( 'Map', 'gmw-premium-settings' ),
						'street'       => __( 'Street', 'gmw-premium-settings' ),
						'premise'      => __( 'Apt/Suit ', 'gmw-premium-settings' ),
						'city'         => __( 'City', 'gmw-premium-settings' ),
						'region_name'  => __( 'State', 'gmw-premium-settings' ),
						'postcode'     => __( 'Postcode', 'gmw-premium-settings' ),
						'country_code' => __( 'Country', 'gmw-premium-settings' ),
						'latitude'     => __( 'Latitude', 'gmw-premium-settings' ),
						'longitude'    => __( 'Longitude', 'gmw-premium-settings' ),
					),
					'attributes' => array(),
					'priority'   => 10,
				),
				'location_form_template'              => array(
					'name'       => 'location_form_template',
					'type'       => 'select',
					'default'    => 'location-form-tabs-left',
					'label'      => __( 'Form Template', 'gmw-premium-settings' ),
					'desc'       => __( 'Select the Location form template', 'gmw-premium-settings' ),
					'options'    => array(
						'location-form-tabs-left' => __( 'Tabs Left', 'gmw-premium-settings' ),
						'location-form-tabs-top'  => __( 'Tabs Top ', 'gmw-premium-settings' ),
						'location-form-no-tabs'   => __( 'No Tabs', 'gmw-premium-settings' ),
					),
					'attributes' => array(),
					'priority'   => 15,
				),
			),
			'attributes' => '',
			'optionsbox' => 1,
			'priority'   => 5,
		);

		$settings['displayed_member_location_tab'] = array(
			'name'       => 'displayed_member_location_tab',
			'type'       => 'fields_group',
			'label'      => __( 'Displayed Member Location Tab', 'gmw-premium-settings' ),
			'desc'       => __( 'Setup the location tab of a displayed member.', 'gmw-premium-settings' ),
			'fields'     => array(
				'displayed_member_location_tab_elements' => array(
					'name'       => 'displayed_member_location_tab_elements',
					'type'       => 'multiselect',
					'default'    => array(),
					'label'      => __( 'Select elements to display', 'gmw-premium-settings' ),
					'desc'       => __( 'Select the location elements that you would like to display, or leave blank to completely disable the Location tab of the displayed user.', 'gmw-premium-settings' ),
					'options'    => array(
						'address'         => __( 'Address', 'gmw-premium-settings' ),
						'map'             => __( 'Map', 'gmw-premium-settings' ),
						'directions_link' => __( 'Directions link', 'gmw-premium-settings' ),
					),
					'attributes' => array(),
					'priority'   => 5,
				),
				'displayed_member_location_tab_address_fields' => array(
					'name'       => 'displayed_member_location_tab_address_fields',
					'type'       => 'multiselect',
					'default'    => array( 'address' ),
					'label'      => __( 'Address fields', 'gmw-premium-settings' ),
					'desc'       => __( 'Select the address field to display, when "address" is selected in the elements above.', 'gmw-premium-settings' ),
					'options'    => array(
						'address'      => __( 'Formatted address ( full address )', 'gmw-premium-settings' ),
						'street'       => __( 'Street', 'gmw-premium-settings' ),
						'premise'      => __( 'Apt/Suit ', 'gmw-premium-settings' ),
						'city'         => __( 'City', 'gmw-premium-settings' ),
						'region_name'  => __( 'State', 'gmw-premium-settings' ),
						'postcode'     => __( 'Postcode', 'gmw-premium-settings' ),
						'country_code' => __( 'Country', 'gmw-premium-settings' ),
					),
					'attributes' => array( 'data' => 'multiselect_address_fields' ),
					'priority'   => 10,
				),
			),
			'attributes' => '',
			'optionsbox' => 1,
			'priority'   => 10,
		);

		$settings['activity_update_address_fields'] = array(
			'name'       => 'activity_update_address_fields',
			'type'       => 'multiselect',
			'default'    => array(),
			'label'      => __( 'Member Activity Address Fields', 'gmw-premium-settings' ),
			'desc'       => __( 'Select the address field which you would like to display in the activity update after member updated new location.', 'gmw-premium-settings' ),
			'attributes' => array( 'data' => 'multiselect_address_fields' ),
			'options'    => array(
				'address'      => __( 'Formatted address ( full address )', 'gmw-premium-settings' ),
				'street'       => __( 'Street', 'gmw-premium-settings' ),
				'premise'      => __( 'Apt/Suit ', 'gmw-premium-settings' ),
				'city'         => __( 'City', 'gmw-premium-settings' ),
				'region_name'  => __( 'State', 'gmw-premium-settings' ),
				'postcode'     => __( 'Postcode', 'gmw-premium-settings' ),
				'country_code' => __( 'Country', 'gmw-premium-settings' ),
			),
			'priority'   => 15,
		);

		$settings['per_member_map_icon'] = array(
			'name'       => 'per_member_map_icon',
			'type'       => 'checkbox',
			'default'    => '',
			'label'      => __( 'Per Members Map Icon', 'gmw-premium-settings' ),
			'cb_label'   => __( 'Enable', 'gmw-premium-settings' ),
			'desc'       => __( 'Add map icon tab to the Location form to allow memebrs to select their own map icon.', 'gmw-premium-settings' ),
			'attributes' => array(),
			'priority'   => 20,
		);

		return $settings;
	}

	/**
	 * Display member location tab settings
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 */
	public function displayed_member_location_tab( $value, $name_attr ) {

		$checked = '';
		$display = 'style="display:none;"';

		if ( isset( $value['enabled'] ) ) {
			$checked = 'checked="checked"';
			$display = '';
		}
		?>
		<p>
		<label>
			<input 
				class="setting-per_category_icons" 
				name="<?php echo esc_attr( $name_attr ) . '[enabled]'; ?>" 
				onchange="jQuery('#displayed-user-tab-content' ).slideToggle();" 
				type="checkbox" 
				value="1" 
				<?php echo $checked; // WPCS: XSS ok. ?>
			>
			<span>
				<?php esc_html_e( 'Enable Location tab', 'gmw-premium-settings' ); ?>
			</span>
		</label>
		</p>

		<div <?php echo $display; // WPCS: XSS ok. ?> id="displayed-user-tab-content">

			<div style="float: left;">

				<label><b><?php esc_html_e( 'Elements', 'gmw-premium-settings' ); ?></b></label>

				<ul>
					<?php
					$items = array(
						'address'         => __( 'Address', 'gmw-premium-settings' ),
						'map'             => __( 'Map', 'gmw-premium-settings' ),
						'directions_link' => __( 'Directions link', 'gmw-premium-settings' ),
					);

					foreach ( $items as $name => $label ) {

						$checked = ( ! empty( $value['elements'] ) && in_array( $name, $value['elements'], true ) ) ? 'checked="checked"' : '';
						$onclick = ( 'address' === $name ) ? 'onchange="jQuery(\'#displayed-user-tab-address-fields\' ).slideToggle();"' : '';
						?>
						<li>
							<label>
								<input 
									name="<?php echo esc_attr( $name_attr ) . '[elements][]'; ?>" 
									type="checkbox" 
									value="<?php echo esc_attr( $name ); ?>"
									<?php echo $checked . ' ' . $onclick; // WPCS: XSS ok. ?>
								> 
								<span>
									<?php echo esc_html( $label ); ?>
								</span>
							</label>
						</li>
						<?php
					}
					?>
				</ul>
			</div>

			<?php
			if ( is_array( $value['elements'] ) && in_array( 'address', $value['elements'], true ) ) {
				$display = 'style="float: left; margin-left: 50px"';
			} else {
				$display = 'style="display:none;float: left; margin-left: 50px"';
			}
			?>
			<div <?php echo $display; // WPCS: XSS ok. ?> id="displayed-user-tab-address-fields">

				<label><b><?php esc_html_e( 'Address fields', 'gmw-premium-settings' ); ?></b></label>
				<ul>
					<?php
					$items = array(
						'street'       => __( 'Street', 'gmw-premium-settings' ),
						'premise'      => __( 'Apt/Suit ', 'gmw-premium-settings' ),
						'city'         => __( 'City', 'gmw-premium-settings' ),
						'region_name'  => __( 'State', 'gmw-premium-settings' ),
						'postcode'     => __( 'Postcode', 'gmw-premium-settings' ),
						'country_code' => __( 'Country', 'gmw-premium-settings' ),
					);

					foreach ( $items as $name => $label ) {

						$checked = ( ! empty( $value['address_fields'] ) && in_array( $name, $value['address_fields'], true ) ) ? 'checked="checked"' : '';
						?>
						<li>
							<label>
								<input 
									name="<?php echo esc_attr( $name_attr ) . '[address_fields][]'; ?>" 
									type="checkbox" 
									value="<?php echo esc_attr( $name ); ?>"
									<?php echo $checked; // WPCS: XSS ok. ?>
								> 
								<span>
									<?php echo esc_html( $label ); ?>
								</span>
							</label>
						</li>
						<?php
					}
					?>
				</ul>
			</div>
		</div>
		<?php
	}
}
new GMW_PS_FL_Admin_Settings();
?>
