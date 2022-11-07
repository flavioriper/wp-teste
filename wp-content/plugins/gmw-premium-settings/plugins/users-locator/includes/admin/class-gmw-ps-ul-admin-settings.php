<?php
/**
 * GMW Premium Settings - Users Locator admin settings.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Users Locator admin settings
 */
class GMW_PS_Users_Locator_Admin_Settings {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_filter( 'gmw_users_locator_admin_settings', array( $this, 'settings' ), 10 );
	}

	/**
	 * Extend admin settings
	 *
	 * @access public
	 *
	 * @param array $settings settings.
	 *
	 * @return $settings
	 */
	public function settings( $settings ) {

		$settings['per_user_map_icon'] = array(
			'name'       => 'per_user_map_icon',
			'type'       => 'checkbox',
			'default'    => '',
			'label'      => __( 'Per User Map Icon', 'gmw-premium-settings' ),
			'cb_label'   => __( 'Enable', 'gmw-premium-settings' ),
			'desc'       => __( 'Enable map icon tab in the users location form to allow users choose thier own map icon.', 'gmw-premium-settings' ),
			'attributes' => array(),
			'priority'   => 40,
		);

		return $settings;
	}
}
new GMW_PS_Users_Locator_Admin_Settings();

