<?php
/**
 * GMW Premium Settings - Groups Locator info-window.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Get group data. We use alphabetical type so inactive group data will show as well.
if ( bp_has_groups(
	array(
		'include' => array( $location->object_id ),
		'type'    => 'alphabetical',
	)
) ) {
	global $groups_template;

	while ( bp_groups() ) :
		bp_the_group();

		// get additional group location data.
		$group_location = gmw_get_bp_group_location( $location->location_id, true );

		$fields = array(
			'lat',
			'lng',
			'latitude',
			'longitude',
			'street',
			'premise',
			'city',
			'region_name',
			'postcode',
			'country_code',
			'address',
			'formatted_address',
			'location_name',
			'featured_location',
		);

		// append location to the member object.
		foreach ( $fields as $field ) {

			if ( isset( $group_location->$field ) ) {
				$groups_template->group->$field = $group_location->$field;
			}
		}

		// get location meta if needed and append it to the member.
		if ( ! empty( $gmw['info_window']['location_meta'] ) ) {
			$groups_template->group->location_meta = gmw_get_location_meta( $location->location_id, $gmw['info_window']['location_meta'] );
		}

		// get distance from results.
		$groups_template->group->units    = $location->units;
		$groups_template->group->distance = $location->distance;

		// modify form and member.
		$groups_template->group = apply_filters( 'gmw_ps_group_before_info_window', $groups_template->group, $gmw );
		$group                  = $groups_template->group;

		$iw_type  = ! empty( $gmw['info_window']['iw_type'] ) ? $gmw['info_window']['iw_type'] : 'popup';
		$template = $gmw['info_window']['template'][ $iw_type ];

		// include template.
		$template_data = gmw_get_info_window_template( 'bp_groups_locator', $iw_type, $template, 'premium_settings' );

		include $template_data['content_path'];

		do_action( 'gmw_ps_after_group_info_window', $group, $gmw );

	endwhile;
}
