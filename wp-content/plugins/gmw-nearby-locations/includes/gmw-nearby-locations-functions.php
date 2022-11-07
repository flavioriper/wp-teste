<?php
/**
 * Nearby Locations functions.
 *
 * @package gmw-nearby-locations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Default widget settings
 *
 * @param  string $object [description].
 *
 * @return [type]         [description]
 */
function gmw_nbl_get_widget_settings( $object = 'post' ) {

	$settings = array(
		'widget_title'        => array(
			'type'        => 'text',
			'default'     => __( 'Nearby locations', 'gmw-nearby-locations' ),
			'label'       => __( 'Widget title', 'gmw-nearby-locations' ),
			'description' => __( 'Enter a title for the widget or leave blank to omit.', 'gmw-nearby-locations' ),
		),
		'element_id'          => array(
			'type'        => 'number',
			'step'        => 1,
			'min'         => 1,
			'max'         => '',
			'default'     => wp_rand( 100, 549 ),
			'label'       => __( 'Element ID', 'gmw-nearby-locations' ),
			'description' => __( 'Use the element ID to assign a unique ID to this widget. The unique ID can be useful for styling purposes as well when using the hooks provided by the plugin when custom modifications required.', 'gmw-nearby-locations' ),
		),
		'nearby'              => array(
			'type'        => 'text',
			'default'     => __( 'user', 'gmw-nearby-locations' ),
			'label'       => __( 'Nearby Element', 'gmw-nearby-locations' ),
			'description' => '<ol><li>' . __( "Enter \"user\" to display locations nearby the user's current location.", 'gmw-nearby-locations' ) . '</li><li>' . __( 'Enter "object" to display locations nearby the item that is being displayed when viewing in a single template file.', 'gmw-nearby-locations' ) . '</li><li>' . __( 'Enter object ID ( ex. post ID or member ID ) to display locations nearby a specific object.', 'gmw-nearby-locations' ) . '</li><li>' . __( 'Enter coordinates ( latitude,longitude ) to display locations nearby specific coordinates.', 'gmw-nearby-locations' ) . '</li></ol>',
		),
		'results_template'    => array(
			'type'        => 'select',
			'default'     => 'default',
			'label'       => __( 'Results template', 'gmw-nearby-locations' ),
			'options'     => gmw_get_templates(
				array(
					'component' => 'posts_locator',
					'addon'     => 'nearby_locations',
				)
			),
			'description' => __( 'Select the results template.', 'gmw-nearby-locations' ),
		),
		'results_count'       => array(
			'type'        => 'text',
			'default'     => 10,
			'label'       => __( 'Number of locations', 'gmw-nearby-locations' ),
			'description' => __( 'Enter the maximum number of location to display.', 'gmw-nearby-locations' ),
		),
		'radius'              => array(
			'type'        => 'text',
			'default'     => 200,
			'label'       => __( 'Radius', 'gmw-nearby-locations' ),
			'description' => __( 'Enter the radius to search nearby, or leave blank for no radius limit.', 'gmw-nearby-locations' ),
		),
		'units'               => array(
			'type'    => 'select',
			'default' => 'imperial',
			'label'   => __( 'Distance units', 'gmw-nearby-locations' ),
			'options' => array(
				'imperial' => __( 'Miles', 'gmw-nearby-locations' ),
				'metric'   => __( 'Kilometers', 'gmw-nearby-locations' ),
			),
		),
		'orderby'             => array(
			'type'    => 'select',
			'default' => '',
			'label'   => __( 'Order by', 'gmw-nearby-locations' ),
			'options' => array(),
		),
		'order'               => array(
			'type'    => 'select',
			'default' => 'ASC',
			'label'   => __( 'Order', 'gmw-nearby-locations' ),
			'options' => array(
				'ASC'  => __( 'Ascending ( ASC )', 'gmw-nearby-locations' ),
				'DESC' => __( 'Descending ( DESC )', 'gmw-nearby-locations' ),
			),
		),
		'show_locations_list' => array(
			'type'        => 'checkbox',
			'default'     => 1,
			'label'       => __( 'Show list of results', 'gmw-nearby-locations' ),
			'description' => __( 'Show or hide the list of results.', 'gmw-nearby-locations' ),
		),
		'show_map'            => array(
			'type'        => 'checkbox',
			'default'     => 1,
			'label'       => __( 'Show map', 'gmw-nearby-locations' ),
			'description' => __( 'Show or hide Google map.', 'gmw-nearby-locations' ),
		),
		'map_width'           => array(
			'type'        => 'text',
			'default'     => '100%',
			'label'       => __( 'Map width', 'gmw-nearby-locations' ),
			'description' => __( 'Set the map width in pixels or percentage ( ex. 250px or 100% ).', 'gmw-nearby-locations' ),
		),
		'map_height'          => array(
			'type'        => 'text',
			'default'     => '250px',
			'label'       => __( 'Map height', 'gmw-nearby-locations' ),
			'description' => __( 'Set the map height in pixels or percentage ( ex. 250px or 100% ).', 'gmw-nearby-locations' ),
		),
	);

	if ( 'google_maps' === GMW()->maps_provider ) {

		$settings['map_type'] = array(
			'type'    => 'select',
			'default' => 'ROADMAP',
			'label'   => __( 'Map type', 'gmw-nearby-locations' ),
			'options' => array(
				'ROADMAP'   => __( 'ROADMAP', 'gmw-nearby-locations' ),
				'SATELLITE' => __( 'SATELLITE', 'gmw-nearby-locations' ),
				'HYBRID'    => __( 'HYBRID', 'gmw-nearby-locations' ),
				'TERRAIN'   => __( 'TERRAIN', 'gmw-nearby-locations' ),
			),
		);
	}

	/*'zoom_level'   => array(
		'type'        => 'select',
		'default'     => '',
		'label'       => __( 'Zoom level', 'gmw-nearby-locations' ),
		'options'     => $zoom_options
	),*/
	/*'scrollwheel' => array(
		'type'        => 'checkbox',
		'default'     => 0,
		'label'       => __( 'Mouse wheel zoom', 'gmw-nearby-locations' ),
		'description' => __( 'When enabled, the map will zoom in/out using the mouse scroll wheel.', 'gmw-nearby-locations' ),
	),*/

	$settings = $settings + array(
		'group_markers'         => array(
			'type'        => 'select',
			'default'     => 'standard',
			'label'       => __( 'Markers Grouping', 'gmw-nearby-locations' ),
			'options'     => array(
				'standard'          => __( 'No grouping', 'gmw-nearby-locations' ),
				'markers_clusterer' => __( 'Markers Clusterer', 'gmw-nearby-locations' ),
			),
			'description' => __( 'Select how to group nearby markers on the map.', 'gmw-nearby-locations' ),
		),
		'map_icon'              => array(
			'type'        => 'text',
			'default'     => GMW()->default_icons['location_icon_url'],
			'label'       => __( 'Map icon', 'gmw-nearby-locations' ),
			'description' => __( 'URL of the image that you would like to use as the map icon that represents the object location on the map.', 'gmw-nearby-locations' ),
		),
		'map_icon_size'         => array(
			'type'        => 'text',
			'default'     => '',
			'label'       => __( 'Map icon size', 'gmw-nearby-locations' ),
			'description' => __( 'Enter custom map icon size if needed ( when default size does not show properly ). Enter the width and height, comma separated. For example, enter 25,41 for 25px width and 41px height.', 'gmw-nearby-locations' ),
			'placeholder' => __( 'Icon size. For ex, 25,41', 'gmw-nearby-locations' ),
		),
		'user_map_icon'         => array(
			'type'        => 'text',
			'default'     => GMW()->default_icons['user_location_icon_url'],
			'label'       => __( 'User location map icon', 'gmw-nearby-locations' ),
			'description' => __( 'Link to the image that you would like to use as the map marker that represents the user\'s location on the map. Leave blank to disable.', 'gmw-nearby-locations' ),
		),
		'user_map_icon_size'    => array(
			'type'        => 'text',
			'default'     => '',
			'label'       => __( 'User\'s map icon size', 'gmw-nearby-locations' ),
			'description' => __( 'Enter custom map icon size if needed ( when default size does not show properly ). Enter the width and height, comma separated. For example, enter 25,41 for 25px width and 41px height.', 'gmw-nearby-locations' ),
			'placeholder' => __( 'Icon size. For ex, 25,41', 'gmw-nearby-locations' ),
		),
		'show_image'            => array(
			'type'        => 'checkbox',
			'default'     => 1,
			'label'       => __( 'Show Image', 'gmw-nearby-locations' ),
			'description' => __( 'show/hide the object image in the list of results ( post featured image, user avatar...).', 'gmw-nearby-locations' ),
		),
		'show_distance'         => array(
			'type'        => 'checkbox',
			'default'     => 1,
			'label'       => __( 'Show distance', 'gmw-nearby-locations' ),
			'description' => __( 'Show the distance to each location in the list of results.', 'gmw-nearby-locations' ),
		),
		'directions_link'       => array(
			'type'        => 'text',
			'default'     => __( 'Get directions', 'gmw-nearby-locations' ),
			'label'       => __( 'Show directions link', 'gmw-nearby-locations' ),
			'description' => __( 'To show direction link for each location in the results, enter the label for the link in the input box. Otherwise, leave blank to omit.', 'gmw-nearby-locations' ),
		),
		'address_fields'        => array(
			'type'        => 'multicheckbox',
			'default'     => array( 'address' ),
			'label'       => __( 'Address fields', 'gmw-nearby-locations' ),
			'options'     => array(
				'address'      => __( 'Full address', 'gmw-nearby-locations' ),
				'street'       => __( 'Street', 'gmw-nearby-locations' ),
				'city'         => __( 'City', 'gmw-nearby-locations' ),
				'region_name'  => __( 'State', 'gmw-nearby-locations' ),
				'postcode'     => __( 'Postcode', 'gmw-nearby-locations' ),
				'country_code' => __( 'Country', 'gmw-nearby-locations' ),
			),
			'description' => __( 'Choose the address fields to display. Choose "Full address" to display the full address, or choose any of the specific address fields.', 'gmw-nearby-locations' ),
		),
		'show_random_locations' => array(
			'type'        => 'checkbox',
			'default'     => 1,
			'label'       => __( 'Show random locations', 'gmw-nearby-locations' ),
			'description' => __( 'Show random locations if no locations were found nearby.', 'gmw-nearby-locations' ),
		),
		'no_results_message'    => array(
			'type'        => 'text',
			'default'     => __( 'No locations found', 'gmw-nearby-locations' ),
			'label'       => __( 'No results message', 'gmw-nearby-locations' ),
			'description' => __( 'Enter the message to display when no results found. leave blank to omit the message.', 'gmw-nearby-locations' ),
		),
	);

	return apply_filters( 'gmw_nbl_get_widget_settings', $settings );
}
