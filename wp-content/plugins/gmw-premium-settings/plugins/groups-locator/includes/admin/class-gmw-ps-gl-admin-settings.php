<?php
/**
 * GMW Premium Settings - Groups Locator admin Settings.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_PS_FL_Admin class
 */
class GMW_PS_GL_Admin_Settings {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// admin settings.
		add_filter( 'gmw_bp_groups_locator_admin_settings', array( $this, 'admin_settings' ), 10 );
	}

	/**
	 * Extend admin settings
	 *
	 * @access public
	 *
	 * @return $settings
	 */
	public function admin_settings( $settings ) {

		/**
		$settings['bp_groups_locator']['activity_update_address_fields'] = array(
			'name'       	=> 'activity_update_address_fields',
			'type'      	=> 'multiselect',
			'default'       => array(),
			'label'      	=> __( 'Activity Update Address Fields.', 'gmw-premium-settings' ),
			'desc'       	=> __( 'Select the address field which you would like to display in the activity update. You can either select the full address or any of the different address field.', 'gmw-premium-settings' ),
			'attributes' 	=> array( 'data' => 'multiselect_address_fields' ),
			'options'	 	=> array(
				'address'	   => __( 'Formatted address ( full address )', 'gmw-premium-settings' ),
				'street' 	   => __( 'Street', 'gmw-premium-settings' ),
				'premise'	   => __( 'Apt/Suit ', 'gmw-premium-settings' ),
				'city'	 	   => __( 'City', 'gmw-premium-settings' ),
				'region_name'  => __( 'State', 'gmw-premium-settings' ),
				'postcode'	   => __( 'Postcode', 'gmw-premium-settings' ),
				'country_code' => __( 'Country', 'gmw-premium-settings' ),
			),
			'priority'    	=> 15
		);
		*/

		$settings['per_group_map_icon'] = array(
			'name'       => 'per_group_map_icon',
			'type'       => 'checkbox',
			'default'    => '',
			'label'      => __( 'Per Group Map Icon', 'gmw-premium-settings' ),
			'cb_label'   => __( 'Enable', 'gmw-premium-settings' ),
			'desc'       => __( 'Add map icon tab to the group Location form to allow group admins to select a specific map icon for the group.', 'gmw-premium-settings' ),
			'attributes' => array(),
			'priority'   => 25,
		);

		return $settings;
	}
}
new GMW_PS_GL_Admin_Settings();
