<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modify some map features ( user location map icon, map control ) in search results
 * 
 * @param  array $mapElements  the original map element
 * @param  array $gmw          the form being displayed
 * @return array               modifyed map element
 */
function gmw_ps_bpmdg_modify_map_elements( $map_args ) {
    
	$options = gmw_get_options_group( 'bp_members_directory_geolocation' );

    $map_args['settings']['info_window_type'] = $options['iw_type'];
    $map_args['settings']['group_markers']    = $options['markers_grouping'];

    return $map_args;


    //$icons_data = gmw_get_icons();
    
    // look for ul icon
    //$ul_icon = isset( $gmw['map_markers']['user_marker'] ) ? $gmw['map_markers']['user_marker'] : '_default.png';
    
    //set the user location marker
    //$mapElements['user_location']['map_icon'] = $icons_data[$gmw['prefix'].'_map_icons']['url'].$ul_icon;
  
    //disable the map control. We will enable each one based on the form settings
    /*$mapElements['map_options'] = array_merge( $mapElements['map_options'], array(
        'zoomControl'        => false,
        'rotateControl'      => false,
        'mapTypeControl'     => false,
        'streetViewControl'  => false,
        'overviewMapControl' => false,
        'scrollwheel'        => false,
        'scaleControl'       => false,
        'resizeMapControl'   => false
    ) ); */
    
    /*if ( ! empty( $gmw['results_map']['min_zoom_level'] ) ) {
        $mapElements['map_options']['minZoom'] = $gmw['results_map']['min_zoom_level'];
    } */

    /*if ( ! empty( $gmw['results_map']['max_zoom_level'] ) ) {
        $mapElements['map_options']['maxZoom'] = $gmw['results_map']['max_zoom_level'];
    }

    //enabled map controls based on settings
    if ( ! empty( $gmw['results_map']['map_controls'] ) ) {
        foreach ( $gmw['results_map']['map_controls'] as $value ) {
            if ( $value == 'resizeMapControl' ) {
                $mapElements['map_options']['resizeMapControl'] = 'gmw-resize-map-trigger-'.$gmw['ID'];
            } else {
                $mapElements['map_options'][$value] = true;
            }
        }
    }

    if ( ! empty( $gmw['results_map']['styles'] ) ) {
        $mapElements['map_options']['styles'] = json_decode( $gmw['results_map']['styles'] );
    } else if ( ! empty( $gmw['results_map']['snazzy_maps_styles'] ) ) {

        $styles = get_option( 'SnazzyMapStyles', null );

        foreach ( $styles as $style ) {
            if ( $style['id'] == $gmw['results_map']['snazzy_maps_styles'] ) {
                $mapElements['map_options']['styles'] = json_decode( $style['json'] );
            }
        }
    }*/

    //return $mapElements;        
}
add_filter( 'gmw_map_element_bpmdg', 'gmw_ps_bpmdg_modify_map_elements', 10 );
?>