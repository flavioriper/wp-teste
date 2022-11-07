<?php
/**
 * GMW Premium Settings - Admin Settings
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_PT_Admin class
 */
class GMW_PS_Admin_Settings {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// admin settings.
		add_filter( 'gmw_admin_settings', array( $this, 'settings' ), 10 );
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

		$settings['general_settings']['smartbox_library'] = array(
			'name'       => 'smartbox_library',
			'type'       => 'select',
			'default'    => 'chosen',
			'label'      => __( 'Smartbox Library ', 'gmw-premium-settings' ),
			'desc'       => __( 'Select the library you would like to use to generate the smartboxes in GEO my WP search forms.', 'gmw-premium-settings' ),
			'options'    => array(
				'select2' => 'Select2',
				'chosen'  => 'jQuery Chosen',
			),
			'attributes' => array(),
			'priority'   => 90,
		);

		return $settings;
	}
}
new GMW_PS_Admin_Settings();
