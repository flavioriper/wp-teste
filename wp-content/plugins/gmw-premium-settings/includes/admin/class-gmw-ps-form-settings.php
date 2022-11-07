<?php
/**
 * GMW Premium Settings - Form Settings.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_PS_Form_Settings class
 */
class GMW_PS_Form_Settings {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// default settings.
		add_filter( 'gmw_form_default_settings', array( $this, 'default_settings' ), 15 );

		// form fields.
		add_filter( 'gmw_form_settings', array( $this, 'form_settings' ), 15, 2 );

		// set keywords fields options.
		add_filter( 'gmw_ps_form_settings_keywords_field_options', array( $this, 'get_keywords_field_options' ), 15, 2 );

		$ps_helper = 'GMW_PS_Form_Settings_Helper';

		// global form settings for all forms.
		add_action( 'gmw_form_settings_ps_address_fields', array( $ps_helper, 'address_fields' ), 15, 2 );
		add_action( 'gmw_form_settings_ps_keywords_field', array( $ps_helper, 'keywords_field' ), 15, 3 );
		add_action( 'gmw_form_settings_ps_radius_slider', array( $ps_helper, 'radius_slider' ), 15, 2 );
		add_action( 'gmw_form_settings_ps_results_found_message', array( $ps_helper, 'results_found_message' ), 15, 2 );
		add_action( 'gmw_form_settings_ps_wider_search', array( $ps_helper, 'wider_search' ), 15, 2 );
		add_action( 'gmw_form_settings_ps_no_results_message', array( $ps_helper, 'no_results_message' ), 15, 3 );
		add_action( 'gmw_form_settings_ps_refresh_map_icons', array( $ps_helper, 'refresh_map_icons' ), 15, 3 );
		add_action( 'gmw_form_settings_ps_snazzy_maps_styles', array( $ps_helper, 'snazzy_maps_styles' ), 15, 2 );
		add_action( 'gmw_form_settings_ps_info_window_template', array( $ps_helper, 'info_window_template' ), 15, 4 );

		// validations.
		add_filter( 'gmw_validate_form_settings_ps_keywords_field', array( $ps_helper, 'validate_keywords_field' ), 15 );
		add_filter( 'gmw_validate_form_settings_ps_radius_slider', array( $ps_helper, 'validate_radius_slider' ), 15 );
		add_filter( 'gmw_validate_form_settings_ps_results_found_message', array( $ps_helper, 'validate_results_found_message' ), 15, 2 );
		add_filter( 'gmw_validate_form_settings_ps_no_results_message', array( $ps_helper, 'validate_no_results_message' ), 15 );
		add_filter( 'gmw_validate_form_settings_ps_wider_search', array( $ps_helper, 'validate_wider_search' ), 15, 2 );
		add_filter( 'gmw_validate_form_settings_ps_snazzy_maps_styles', array( $ps_helper, 'validate_snazzy_maps_styles' ), 15, 2 );
		add_filter( 'gmw_validate_form_settings_ps_info_window_template', array( $ps_helper, 'validate_info_window_template' ), 15, 4 );
	}

	/**
	 * Default settings
	 *
	 * @param  array $settings settings.
	 *
	 * @return [type]           [description]
	 */
	public function default_settings( $settings ) {

		$settings['search_form']['radius_slider'] = array(
			'enabled'       => '',
			'default_value' => '50',
			'min_value'     => '0',
			'max_value'     => '200',
		);

		$settings['form_submission']['orderby'] = 'distance';

		$settings['search_results']['results_found_message'] = array(
			'count_message'    => 'Showing {from_count} - {to_count} of {total_results} locations',
			'location_message' => ' within {radius}{units} from {address}',
		);

		$settings['search_results']['address_fields'] = array( 'address' );
		$settings['search_results']['location_meta']  = array();

		$settings['no_results']['wider_search']     = '';
		$settings['no_results']['all_results_link'] = 'click here';
		$settings['no_results']['message']          = 'No results found.';

		$settings['results_map']['min_zoom_level']     = '';
		$settings['results_map']['max_zoon_level']     = '';
		$settings['results_map']['map_controls']       = array( 'zoomControl', 'mapTypeControl' );
		$settings['results_map']['styles']             = '';
		$settings['results_map']['snazzy_maps_styles'] = '';

		$settings['map_markers'] = array(
			'grouping'       => 'markers_clusterer',
			'default_marker' => '_default.png',
			'user_marker'    => '_default.png',
		);

		$settings['info_window'] = array(
			'iw_type'         => 'popup',
			'ajax_enabled'    => 1,
			'template'        => array(
				'popup' => 'left-white',
			),
			'image'           => array(
				'enabled' => 1,
				'width'   => '200',
				'height'  => '200',
			),
			'address_fields'  => array( 'address' ),
			'distance'        => 1,
			'directions_link' => 1,
			'location_meta'   => array(),
		);

		return $settings;
	}

	/**
	 * Keywords fields options
	 *
	 * @param  array $options options.
	 * @param  array $form    gmw form.
	 *
	 * @return [type]          [description]
	 */
	public function get_keywords_field_options( $options, $form ) {

		if ( 'posts_locator' === $form['component'] ) {

			$options['title']   = __( 'Search post title', 'gmw-premium-settings' );
			$options['content'] = __( 'Search post title and content', 'gmw-premium-settings' );

		} elseif ( 'members_locator' === $form['component'] ) {

			$options['name'] = __( 'Search member name', 'gmw-premium-settings' );

		} elseif ( 'bp_groups_locator' === $form['component'] ) {

			$options['name'] = __( 'Search group name and description', 'gmw-premium-settings' );

		} elseif ( 'users_locator' === $form['component'] ) {

			$options['name'] = __( 'Search user name', 'gmw-premium-settings' );
		}

		return $options;
	}

	/**
	 * Form Settings for all forms types.
	 *
	 * @param  array $form_fields form fields.
	 *
	 * @param  array $form        gmw form.
	 *
	 * @return [type]           [description]
	 */
	public function form_settings( $form_fields, $form ) {

		$is_global_maps = ( 'global_maps' === $form['addon'] ) ? true : false;
		$is_ajax_form   = ( 'ajax_forms' === $form['addon'] ) ? true : false;

		// address fields.
		$form_fields['search_form']['address_field'] = array(
			'name'       => 'address_field',
			'type'       => 'function',
			'default'    => '',
			'function'   => 'ps_address_fields',
			'label'      => __( 'Address field', 'gmw-premium-settings' ),
			'desc'       => __( 'Setup the address field of the search form. You can choose betweem single or multiple address fields.', 'gmw-premium-settings' ),
			'attributes' => array(),
			'priority'   => 20,
		);

		$form_fields['search_form']['keywords'] = array(
			'name'       => 'keywords',
			'type'       => 'function',
			'function'   => 'ps_keywords_field',
			'default'    => '',
			'label'      => __( 'Keywords field', 'gmw-premium-settings' ),
			'desc'       => __( 'Add keywords field to the search form.', 'gmw-premium-settings' ),
			'attributes' => array(),
			'priority'   => 23,
		);

		// address fields.
		$form_fields['search_form']['radius_slider'] = array(
			'name'     => 'radius_slider',
			'type'     => 'function',
			'function' => 'ps_radius_slider',
			'default'  => '',
			'label'    => __( 'Radius Slider', 'gmw-premium-settings' ),
			'cb_label' => __( 'Enable', 'gmw-premium-settings' ),
			'desc'     => __( 'Enable this feature to display a slide that controls the radius value.', 'gmw-premium-settings' ),
			'priority' => 41,
		);

		if ( ! $is_global_maps ) {

			$form_fields['search_results']['results_found_message'] = array(
				'name'        => 'results_found_message',
				'type'        => 'function',
				'function'    => 'ps_results_found_message',
				'placeholder' => 'Enter results message',
				'default'     => '',
				'label'       => __( 'Results Message', 'gmw-premium-settings' ),
				'desc'        => __( 'Modify the message that shows when results are found. There are 2 parts for the message, the "Count Message" and the "Radius Message". You can use the availabe placeholders to generate the messages as you wish.', 'gmw-premium-settings' ),
				'attributes'  => array(),
				'priority'    => 15,
			);

			$form_fields['search_results']['address_fields'] = array(
				'name'        => 'address_fields',
				'type'        => 'multiselect',
				'placeholder' => 'Select address fields',
				'default'     => array(),
				'label'       => __( 'Address fields', 'gmw-premium-settings' ),
				'desc'        => __( 'Select the address fields that you would like to display in the list of results. Select "Formatted address" to display the full address, select specific address fields or leave blank to omit the address.', 'gmw-premium-settings' ),
				'attributes'  => array( 'data' => 'multiselect_address_fields' ),
				'options'     => array(
					'address'      => __( 'Formatted address ( full address )', 'gmw-premium-settings' ),
					'street'       => __( 'Street', 'gmw-premium-settings' ),
					'premise'      => __( 'Apt/Suit ', 'gmw-premium-settings' ),
					'city'         => __( 'City', 'gmw-premium-settings' ),
					'region_name'  => __( 'State', 'gmw-premium-settings' ),
					'postcode'     => __( 'Postcode', 'gmw-premium-settings' ),
					'country_code' => __( 'Country', 'gmw-premium-settings' ),
				),
				'priority'    => 30,
			);

			// temporary availabe for posts locator only.
			if ( 'pt' === $form['prefix'] ) {

				$form_fields['search_results']['location_meta'] = array(
					'name'       => 'location_meta',
					'type'       => 'multiselect',
					'default'    => '',
					'label'      => __( 'Location Meta', 'gmw-premium-settings' ),
					'desc'       => __( 'Select the location meta fields which you would like to display in each location in the search results.', 'gmw-premium-settings' ),
					'options'    => GMW_Form_Settings_Helper::get_location_meta(),
					'attributes' => array(),
					'priority'   => 36,
				);
			}

			/****************** No results */

			$form_fields['no_results']['wider_search'] = array(
				'name'       => 'wider_search',
				'type'       => 'function',
				'function'   => 'ps_wider_search',
				'default'    => '',
				'label'      => __( 'Wider search', 'gmw-premium-settings' ),
				'desc'       => __( 'Setup the wider search feature.', 'gmw-premium-settings' ),
				'attributes' => array(),
				'priority'   => 10,
			);

			$form_fields['no_results']['all_results_link'] = array(
				'name'       => 'all_results_link',
				'type'       => 'text',
				'default'    => 'click here',
				'label'      => __( 'All results link', 'gmw-premium-settings' ),
				'desc'       => __( 'Enter text that will be used as the All Results link. Then use the placeholder <code>{all_results_link}</code> anywhere in the No Results Message textarea where you would like the link to be.', 'gmw-premium-settings' ),
				'attributes' => array(),
				'priority'   => 20,
			);

			$form_fields['no_results']['message'] = array(
				'name'        => 'message',
				'type'        => 'function',
				'function'    => 'ps_no_results_message',
				'default'     => __( 'No results found.', 'gmw-premium-settings' ),
				'label'       => __( 'No results text', 'gmw-premium-settings' ),
				'desc'        => __( 'Enter text that you would like to display when no results found.', 'gmw-premium-settings' ),
				'attributes'  => array(
					'rows'  => '8',
					'style' => 'max-width: 400px',
				),
				'placeholder' => 'Ex. No results found.',
				'priority'    => 30,
			);

		}

		/***************** Map */

		$form_fields['results_map']['max_zoom_level'] = array(
			'name'       => 'max_zoom_level',
			'type'       => 'select',
			'default'    => '',
			'label'      => __( 'Maximum Zoom Level', 'gmw-premium-settings' ),
			'desc'       => __( 'Select a value to set the maximum zoom level of the map. You can use this feature for privacy purposes by preventing users from zooming in to a street level.', 'gmw-premium-settings' ),
			'options'    => array(
				''   => __( 'disabled', 'gmw-premium-settings' ),
				'1'  => '1',
				'2'  => '2',
				'3'  => '3',
				'4'  => '4',
				'5'  => '5',
				'6'  => '6',
				'7'  => '7',
				'8'  => '8',
				'9'  => '9',
				'10' => '10',
				'11' => '11',
				'12' => '12',
				'13' => '13',
				'14' => '14',
				'15' => '15',
				'16' => '16',
				'17' => '17',
				'18' => '18',
				'19' => '19',
				'20' => '20',
			),
			'attributes' => array(),
			'priority'   => 40,
		);

		if ( ! $is_global_maps ) {

			$controls = array(
				'zoomControl'      => __( 'Zoom', 'gmw-premium-settings' ),
				'scrollwheel'      => __( 'Scrollwheel zoom', 'gmw-premium-settings' ),
				'resizeMapControl' => __( 'Resize map trigger', 'gmw-premium-settings' ),
			);

			if ( 'google_maps' === GMW()->maps_provider ) {
				$controls['rotateControl']      = __( 'Rotate Control', 'gmw-premium-settings' );
				$controls['scaleControl']       = __( 'Scale', 'gmw-premium-settings' );
				$controls['mapTypeControl']     = __( 'Map Type', 'gmw-premium-settings' );
				$controls['streetViewControl']  = __( 'Street View', 'gmw-premium-settings' );
				$controls['overviewMapControl'] = __( 'Overview', 'gmw-premium-settings' );
			}

			$form_fields['results_map']['map_controls'] = array(
				'name'        => 'map_controls',
				'type'        => 'multiselect',
				'placeholder' => 'Select map controls',
				'default'     => array(),
				'label'       => __( 'Map controls', 'gmw-premium-settings' ),
				'desc'        => __( 'Select the map controls would you like to display.', 'gmw-premium-settings' ),
				'options'     => $controls,
				'attributes'  => array(),
				'priority'    => 50,
			);
		}

		if ( 'google_maps' === GMW()->maps_provider ) {

			$form_fields['results_map']['styles'] = array(
				'name'       => 'styles',
				'type'       => 'textarea',
				'default'    => '',
				'label'      => __( 'Maps Styles', 'gmw-premium-settings' ),
				'desc'       => sprintf(
					/* translators: %s: link */
					__( 'Enter the script of the map style that you would like to use. You can find a large collection of map styles on the <a href="%s" target="_blank">Snazzy Maps website</a>.', 'gmw-premium-settings' ),
					'https://snazzymaps.com'
				),
				'attributes' => array( 'style' => 'max-width:500px;min-height:200px' ),
				'priority'   => 60,
			);

			$form_fields['results_map']['snazzy_maps_styles'] = array(
				'name'       => 'snazzy_maps_styles',
				'type'       => 'function',
				'function'   => 'ps_snazzy_maps_styles',
				'default'    => '',
				'label'      => __( 'Snazzy Maps Styles', 'gmw-premium-settings' ),
				'desc'       => sprintf(
					/* translators: %s: link */
					__( 'Choose the map style which you would like to apply to the map of this form. You can <a href="%s">explore</a> more styles to be added to this list by saving them to your list. Note that selecting a map style here will overwite any custom style added in the textarea above.', 'gmw-premium-settings' ),
					'?page=snazzy_maps&tab=1'
				),
				'attributes' => array( 'style' => 'max-width:500px;min-height:200px' ),
				'priority'   => 70,
			);

		} else {

			$form_fields['results_map']['styles'] = array(
				'name'       => 'styles',
				'type'       => 'textarea',
				'default'    => '',
				'label'      => __( 'Maps Styles', 'gmw-premium-settings' ),
				'desc'       => __( 'This feature is availabe with Google Maps provider only.', 'gmw-premium-settings' ),
				'attributes' => array(
					'style'    => 'max-width:500px;min-height:200px;display:none',
					'disabled' => 'disabled',
				),
				'priority'   => 60,
			);

			$form_fields['results_map']['snazzy_maps_styles'] = array(
				'name'       => 'snazzy_maps_styles',
				'type'       => 'text',
				'default'    => '',
				'label'      => __( 'Snazzy Maps Styles', 'gmw-premium-settings' ),
				'desc'       => __( 'This feature is availabe with Google Maps provider only.', 'gmw-premium-settings' ),
				'attributes' => array(
					'style'    => 'max-width:500px;min-height:200px;display:none',
					'disabled' => 'disabled',
				),
				'priority'   => 70,
			);
		}

		/*********** Map Markers */

		$form_fields['map_markers']['grouping'] = array(
			'name'       => 'grouping',
			'type'       => 'select',
			'default'    => 'standard',
			'label'      => __( 'Markers grouping', 'gmw-premium-settings' ),
			'desc'       => __( 'Use marker Clusterer to group near locations.', 'gmw-premium-settings' ),
			'options'    => array(
				'standard'           => 'No Grouping',
				'markers_clusterer'  => 'Markers clusterer',
				'markers_spiderfier' => 'Markers Spiderfier',
			),
			'attributes' => array(),
			'priority'   => 10,
		);

		$icons_prefix = gmw_get_addon_data( $form['component'] );
		$icons_prefix = $icons_prefix['prefix'];

		$form_fields['map_markers']['default_marker'] = array(
			'name'       => 'default_marker',
			'type'       => 'radio',
			'default'    => '_default.png',
			'label'      => __( 'Default Location Icon', 'gmw-premium-settings' ),
			'desc'       => __( 'Select the default location map icon. All the locations on the map will have this icon when the "Map Icon Usage" is set to "Global", or when the "Map Icon Usage" is set to anything else but a specific map icon cannot be found or is not set.', 'gmw-premium-settings' ),
			'options'    => GMW_PS_Form_Settings_Helper::get_map_icons( $icons_prefix, false ),
			'attributes' => array(),
			'priority'   => 20,
		);

		$form_fields['map_markers']['icon_scaled_size'] = array(
			'name'       => 'icon_scaled_size',
			'type'       => 'fields_group',
			'label'      => __( 'Location Icon Size', 'gmw-premium-settings' ),
			'desc'       => __( 'Set custom icon size in pixels ( You must provide both width and height ). Otherwise, leave blank to use the original icons image size.', 'gmw-premium-settings' ),
			'fields'     => array(
				array(
					'name'        => 'icon_width',
					'type'        => 'number',
					'default'     => '',
					'label'       => __( 'Width', 'gmw-premium-settings' ),
					'placeholder' => __( 'Numeric value', 'gmw-premium-settings' ),
					'attributes'  => array(),
					'priority'    => 5,
				),
				array(
					'name'        => 'icon_height',
					'type'        => 'number',
					'default'     => '',
					'label'       => __( 'Height', 'gmw-premium-settings' ),
					'placeholder' => __( 'Numeric value', 'gmw-premium-settings' ),
					'priority'    => 10,
					'attributes'  => array(),
				),
			),
			'attributes' => '',
			'optionsbox' => 1,
			'priority'   => 30,
		);

		$form_fields['map_markers']['user_marker'] = array(
			'name'       => 'user_marker',
			'type'       => 'radio',
			'default'    => '_default.png',
			'label'      => __( 'User Location Icon', 'gmw-premium-settings' ),
			'desc'       => __( 'Choose the map icon that represents the user\'s/visitor\'s location on the map. ', 'gmw-premium-settings' ),
			'attributes' => array(),
			'options'    => GMW_PS_Form_Settings_Helper::get_map_icons( $icons_prefix, true ),
			'priority'   => 40,
		);

		$form_fields['map_markers']['user_icon_scaled_size'] = array(
			'name'       => 'icon_scaled_size',
			'type'       => 'fields_group',
			'label'      => __( 'User Icon Size', 'gmw-premium-settings' ),
			'desc'       => __( 'Set custom icon size for the user\'s map icon in pixels ( You must provide both width and height ). Otherwise, leave blank to use the original icons image size.', 'gmw-premium-settings' ),
			'fields'     => array(
				array(
					'name'        => 'user_icon_width',
					'type'        => 'number',
					'default'     => '',
					'label'       => __( 'Width', 'gmw-premium-settings' ),
					'placeholder' => __( 'Numeric value', 'gmw-premium-settings' ),
					'attributes'  => array(),
					'priority'    => 5,
				),
				array(
					'name'        => 'user_icon_height',
					'type'        => 'number',
					'default'     => '',
					'label'       => __( 'Height', 'gmw-premium-settings' ),
					'placeholder' => __( 'Numeric value', 'gmw-premium-settings' ),
					'priority'    => 10,
					'attributes'  => array(),
				),
			),
			'attributes' => '',
			'optionsbox' => 1,
			'priority'   => 50,
		);

		$form_fields['map_markers']['refresh_map_icons'] = array(
			'name'       => 'refresh_map_icons',
			'type'       => 'function',
			'function'   => 'ps_refresh_map_icons',
			'default'    => '',
			'label'      => __( 'Refresh Map Icons', 'gmw-premium-settings' ),
			'desc'       => __( 'Use the "Refresh Icons" button after uploading new icons or if map icons are missing in the form settings. ', 'gmw-premium-settings' ),
			'attributes' => array(),
			'priority'   => 51,
		);

		/*************** Info Window */

		if ( ! $is_global_maps && ! $is_ajax_form ) {

			$info_windows = array(
				'standard' => 'Standard',
				'popup'    => 'Popup Window',
			);

			if ( 'google_maps' === GMW()->maps_provider ) {
				$info_windows['infobubble'] = 'Info-Bubble';
				$info_windows['infobox']    = 'Info-Box';
			}

			$form_fields['info_window']['iw_type'] = array(
				'name'       => 'iw_type',
				'type'       => 'select',
				'default'    => 'infobox',
				'label'      => __( 'Info-window Type', 'gmw-premium-settings' ),
				'desc'       => __( 'Select the info-window type.', 'gmw-premium-settings' ),
				'options'    => $info_windows,
				'attributes' => array(),
				'priority'   => 10,
			);

			$form_fields['info_window']['ajax_enabled'] = array(
				'name'       => 'ajax_enabled',
				'type'       => 'checkbox',
				'default'    => '',
				'label'      => __( 'Ajax Powered Content', 'gmw-premium-settings' ),
				'cb_label'   => __( 'Enable', 'gmw-premium-settings' ),
				'desc'       => __( 'Load the info-window content via Ajax. This feature uses PHP template files that can be modified.', 'gmw-premium-settings' ),
				'attributes' => array(),
				'priority'   => 20,
			);

			$form_fields['info_window']['template'] = array(
				'name'       => 'template',
				'type'       => 'function',
				'default'    => 'default',
				'function'   => 'ps_info_window_template',
				'label'      => __( 'Template File', 'gmw-premium-settings' ),
				'desc'       => __( 'Select the template file. Template files can only be used when the AJAX content option is enabled.', 'gmw-premium-settings' ),
				'attributes' => array(),
				'priority'   => 30,
			);

			$form_fields['info_window']['image'] = array(
				'name'       => 'image',
				'type'       => 'function',
				'default'    => '',
				'label'      => __( 'Image', 'gmw-premium-settings' ),
				'desc'       => __( 'Display the object image in the info window. Enter the width and height in pixels ( numeric value only ).', 'gmw-premium-settings' ),
				'attributes' => array(),
				'priority'   => 50,
			);

			$form_fields['info_window']['address_fields'] = array(
				'name'        => 'address_fields',
				'type'        => 'multiselect',
				'placeholder' => 'Select address fields',
				'default'     => array(),
				'label'       => __( 'Address fields', 'gmw-premium-settings' ),
				'desc'        => __( 'Select the address fields which you would like to display in the list of results. Select "Formatted address" to display the full address, select specific address fields, or leave blank to omit the address.', 'gmw-premium-settings' ),
				'attributes'  => array( 'data' => 'multiselect_address_fields' ),
				'options'     => array(
					'address'      => __( 'Formatted address ( full address )', 'gmw-premium-settings' ),
					'street'       => __( 'Street', 'gmw-premium-settings' ),
					'premise'      => __( 'Apt/Suit ', 'gmw-premium-settings' ),
					'city'         => __( 'City', 'gmw-premium-settings' ),
					'region_name'  => __( 'State', 'gmw-premium-settings' ),
					'postcode'     => __( 'Postcode', 'gmw-premium-settings' ),
					'country_code' => __( 'Country', 'gmw-premium-settings' ),
				),
				'priority'    => 60,
			);

			$form_fields['info_window']['distance'] = array(
				'name'       => 'distance',
				'type'       => 'checkbox',
				'default'    => null,
				'label'      => __( 'Distance', 'gmw-premium-settings' ),
				'cb_label'   => __( 'Enable', 'gmw-premium-settings' ),
				'desc'       => __( 'Display the distance to the location.', 'gmw-premium-settings' ),
				'attributes' => array(),
				'priority'   => 70,
			);

			$form_fields['info_window']['directions_link'] = array(
				'name'       => 'directions_link',
				'type'       => 'checkbox',
				'default'    => '',
				'label'      => __( 'Directions Link', 'gmw-premium-settings' ),
				'cb_label'   => __( 'Enable', 'gmw-premium-settings' ),
				'desc'       => __( 'Display directions link that will open a new window with Google map showing the directions to the location.', 'gmw-premium-settings' ),
				'attributes' => array(),
				'priority'   => 80,
			);

			// for post types only at the moment.
			if ( 'pt' === $form['prefix'] ) {

				$form_fields['info_window']['location_meta'] = array(
					'name'       => 'location_meta',
					'type'       => 'multiselect',
					'default'    => '',
					'label'      => __( 'Location Meta', 'gmw-premium-settings' ),
					'desc'       => __( 'Select the location meta fields which you would like to display in the info-window.', 'gmw-premium-settings' ),
					'options'    => GMW_Form_Settings_Helper::get_location_meta(),
					'attributes' => array(),
					'priority'   => 90,
				);
			}

			$form_fields['info_window']['directions_system'] = array(
				'name'       => 'directions_system',
				'type'       => 'checkbox',
				'default'    => '',
				'label'      => __( 'Directions System', 'gmw-premium-settings' ),
				'cb_label'   => __( 'Enable', 'gmw-premium-settings' ),
				'desc'       => __( 'Display directions system inside the info-window. <br /> <span style="color:red;font-size:11px"> * Note - this feature requires Ajax to be enabled and Popup info-window type.</span>', 'gmw-premium-settings' ),
				'attributes' => array(),
				'priority'   => 100,
			);
		}

		return $form_fields;
	}
}
new GMW_PS_Form_Settings();
