<?php
/**
 * GMW Premium Settings - Users Locator form settings.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Users Locator form settings
 */
class GMW_PS_UL_Form_Settings {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		add_filter( 'gmw_form_default_settings', array( $this, 'default_settings' ), 10, 2 );
		add_action( 'gmw_form_settings', array( $this, 'form_settings' ), 10, 2 );
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

		if ( 'users_locator' === $form['component'] ) {
			$settings['search_results']['orderby'] = 'distance:Distance,ID:User ID,display_name:Display name';
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

		if ( 'users_locator' !== $form['component'] ) {
			return $form_fields;
		}

		if ( 'global_maps' !== $form['addon'] && 'ajax_forms' !== $form['addon'] ) {

			$form_fields['page_load_results']['orderby'] = array(
				'name'       => 'orderby',
				'type'       => 'select',
				'default'    => 'distance',
				'label'      => __( 'Orderby', 'gmw-premium-settings' ),
				'desc'       => __( 'Select the default order of the results on page load.', 'gmw-premium-settings' ),
				'options'    => array(
					'distance'        => __( 'Distance', 'gmw-premium-settings' ),
					'ID'              => __( 'User ID', 'gmw-premium-settings' ),
					'display_name'    => __( 'Display name', 'gmw-premium-settings' ),
					'user_login'      => __( 'Username', 'gmw-premium-settings' ),
					'user_nicename'   => __( 'Nicename', 'gmw-premium-settings' ),
					'user_email'      => __( 'Email', 'gmw-premium-settings' ),
					'user_registered' => __( 'Registered', 'gmw-premium-settings' ),
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
					'distance'        => __( 'Distance', 'gmw-premium-settings' ),
					'ID'              => __( 'User ID', 'gmw-premium-settings' ),
					'display_name'    => __( 'Display name', 'gmw-premium-settings' ),
					'user_login'      => __( 'Username', 'gmw-premium-settings' ),
					'user_nicename'   => __( 'Nicename', 'gmw-premium-settings' ),
					'user_email'      => __( 'Email', 'gmw-premium-settings' ),
					'user_registered' => __( 'Registered', 'gmw-premium-settings' ),
				),
				'attributes' => array(),
				'priority'   => 25,
			);

			$form_fields['search_results']['orderby'] = array(
				'name'        => 'orderby',
				'type'        => 'text',
				'placeholder' => 'ex. distance:Distance,ID:User ID,display_name:Display name',
				'default'     => '',
				'label'       => __( 'Orderby', 'gmw-premium-settings' ),
				'desc'        => __( 'Generate an orderby select dropdown menu to display in the search results ( leave blank to omit ).<br />- Enter sets of value:label, comma separated and in the order that you would like them to appear in the dropdown menu. For ex. distance:Distance,ID:User ID,display_name:Display name.<br />- The availabe orderby values are: distance, ID, display_name, user_login ( username ), user_nicename, user_email and user_registered.', 'gmw-premium-settings' ),
				'priority'    => 18,
			);
		}

		$map_icons_options = array(
			'global' => __( 'Global', 'gmw-premium-settings' ),
			'avatar' => __( 'User avatar', 'gmw-premium-settings' ),
		);

		if ( gmw_get_option( 'users_locator', 'per_user_map_icon', false ) ) {
			$map_icons_options['per_user'] = __( 'Per User', 'gmw-premium-settings' );
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
}
new GMW_PS_UL_Form_Settings();
