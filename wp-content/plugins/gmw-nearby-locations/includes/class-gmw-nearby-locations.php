<?php
/**
 * Nearby Locations main class.
 *
 * @package gmw-nearby-locations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_Nearby_Posts class
 *
 * @version 1.0
 * @author Eyal Fitoussi
 */
class GMW_Nearby_Locations {

	/**
	 * Default shortcode attributes.
	 *
	 * Cab be extended in child class using the $args variable.
	 *
	 * @since 1.1
	 */
	private function get_default_args() {

		return array(
			'element_id'            => 0,
			'item_type'             => '', // deprecated.
			'object'                => 'post',
			'nearby'                => 'user',
			'results_count'         => 5,
			'radius'                => 200,
			'units'                 => 'metric',
			'orderby'               => 'distance',
			'order'                 => 'ASC',
			'show_map'              => 1,
			'map_width'             => '100%',
			'map_height'            => '250px',
			'map_type'              => 'ROADMAP',
			'group_markers'         => 'markers_clusterer',
			'map_icon_url'          => GMW()->default_icons['location_icon_url'],
			'map_icon_size'         => '',
			'user_map_icon_url'     => GMW()->default_icons['user_location_icon_url'],
			'user_map_icon_size'    => '',
			'show_locations_list'   => 1,
			'results_template'      => 'lightcoral',
			'show_image'            => 1,
			'show_distance'         => 1,
			'address_fields'        => 'address',
			'directions_link'       => 'Get directions',
			'show_random_locations' => 1,
			'no_results_message'    => 'Nothing was found near you.',
			'advanced_query'        => 1,
			'widget_title'          => '',
			'radius_per_location'   => '',
		);
	}

	/**
	 * Array for child class to extends the main array above.
	 *
	 * @since 1.0
	 *
	 * @var array
	 */
	public $args = array();

	/**
	 * Boolean shortcodes atts
	 *
	 * @var array
	 */
	public $boolean_items = array(
		'show_locations_list',
		'show_map',
		'show_image',
		'show_distance',
		'show_random_locations',
		'advanced_query',
	);

	/**
	 * Database fields for gmw_location() function that will be pulled in the search query.
	 *
	 * The fields can be modified using the filter 'gmw_location_query_db_fields'
	 *
	 * @var array
	 */
	public $db_fields = array(
		'ID as location_id',
		'object_type',
		'object_id',
		'user_id',
		'latitude as lat',
		'longitude as lng',
		'street_name',
		'street_number',
		'street',
		'premise',
		'city',
		'region_name',
		'region_code',
		'postcode',
		'country_name',
		'country_code',
		'address',
		'formatted_address',
	);

	/**
	 * Get info window args.
	 *
	 * This is where some data that will pass to the map info-window is generated
	 *
	 * as ach object might generate this data differently.
	 *
	 * This method will run in the_location() method and will have the $object availabe to use.
	 *
	 * For posts types, for example, we will use the function as below:
	 *
	 * return array(
	 *      'type'            => 'standard',
	 *      'url'             => get_permalink( $post_id ),
	 *      'title'           => get_the_title( $post_id ),
	 *      'image'           => get_the_post_thumbnail( $post_id ),
	 *      'directions_link' => true,
	 *      'address'         => true,
	 *      'distance'        => true,
	 *      'location_meta'   => array( 'phone', 'fax', 'email' )
	 * );
	 *
	 * @param object $object the location object.
	 *
	 * @return [type]           [description]
	 */
	public function get_info_window_args( $object ) {
		return array(
			'prefix'          => 'nbl_' . $this->args['object'],
			'type'            => 'standard',
			'url'             => '#',
			'title'           => false,
			'image_url'       => false,
			'image'           => false,
			'directions_link' => true,
			'address'         => true,
			'distance'        => true,
		);
	}

	/**
	 * $locations_data
	 *
	 * Holder for the locations data pulled from GEO my WP DB
	 *
	 * @var array
	 */
	public $locations_data = array();

	/**
	 * The object ID when showing location based on a spesific item
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	public $object_id = false;

	/**
	 * Collect location data for the map
	 *
	 * @var array
	 */
	public $map_locations = array();

	/**
	 * Type of nearby
	 *
	 * @since 1.0
	 * @var mix
	 */
	public $nearby = 'user';

	/**
	 * Coordinates to search for locations
	 *
	 * @since 1.0
	 *
	 * @var array
	 */
	public $coords = array(
		'lat' => false,
		'lng' => false,
	);

	/**
	 * Running random query
	 *
	 * @var boolean
	 */
	public $doing_random = false;

	/**
	 * Is there a location ( coords ) we search nearby?
	 *
	 * @var boolean
	 */
	public $location_exists = false;

	/**
	 * Get the location based on the object being displyed in single object page.
	 *
	 * ( use in child class )
	 *
	 * @since 1.0
	 */
	public function nearby_object() {}

	/**
	 * Get the location based on specific object ID
	 *
	 * ( use in child class )
	 *
	 * @since 1.0
	 */
	public function nearby_object_id() {}

	/**
	 * Run the list of results loop
	 *
	 * @since 1.0
	 */
	public function results_loop() {}

	/**
	 * Construct.
	 *
	 * @param array $atts attributes.
	 */
	public function __construct( $atts = array() ) {

		// deprecated attributes.
		if ( isset( $atts['item_map_icon'] ) ) {

			$atts['locations_map_icon'] = $atts['item_map_icon'];

			gmw_trigger_error( '[gmw_nearby_locations] shortcode attribute item_map_icon is deprecated since version 2.0. Use locations_map_icon instead.' );

			unset( $atts['item_map_icon'] );
		}

		if ( isset( $atts['locations_map_icon'] ) ) {

			$atts['map_icon_url'] = $atts['locations_map_icon'];

			unset( $atts['locations_map_icon'] );
		}

		if ( isset( $atts['map_icon'] ) ) {

			$atts['map_icon_url'] = $atts['map_icon'];

			unset( $atts['map_icon'] );
		}

		if ( isset( $atts['user_map_icon'] ) ) {

			$atts['user_map_icon_url'] = $atts['user_map_icon'];

			unset( $atts['user_map_icon'] );
		}

		if ( isset( $atts['get_directions'] ) ) {

			$atts['directions_link'] = $atts['get_directions'];

			gmw_trigger_error( '[gmw_nearby_locations] shortcode attribute get_directions is deprecated since version 2.0. Use directions_link instead.' );

			unset( $atts['get_directions'] );
		}

		if ( isset( $atts['nearby'] ) && 'item' === $atts['nearby'] ) {

			$atts['nearby'] = 'object';

			gmw_trigger_error( 'The value "item" of the [gmw_nearby_locations] shortcode attribute "nearby" is deprecated since version 2.0. Use nearby="object" instead.' );
		}

		// extend the default args.
		$this->args = array_merge( $this->get_default_args(), $this->args );

		/**
		 * Merge the default shortcode attributes with the incoming shortcode atts
		 *
		 * $this->args = shortcode_atts( $this->args, $atts, 'gmw_nearby_locations' );
		 *
		 * merge the default shortcode attributes with the provided shortcode atts.
		 */
		$this->args = apply_filters( 'gmw_nearby_locations_shortcode_attributes', wp_parse_args( $atts, $this->args ) );

		// For previous versions.
		$this->args['object_type'] = gmw_get_object_type( $this->args['object'] );

		// allow boolean attributes accespt 1/yes/true as true value.
		foreach ( $this->boolean_items as $boolean_item ) {
			$this->args[ $boolean_item ] = filter_var( $this->args[ $boolean_item ], FILTER_VALIDATE_BOOLEAN );
		}

		// Verify element ID.
		if ( ! ( $this->args['element_id'] = absint( $this->args['element_id'] ) ) ) {
			$this->args['element_id'] = wp_rand( 400, 800 );
		}

		// verify results count.
		$this->args['results_count'] = is_numeric( $this->args['results_count'] ) ? $this->args['results_count'] : '';

		// verify radius.
		$this->args['radius'] = is_numeric( $this->args['radius'] ) ? $this->args['radius'] : false;

		// Enable / disable advanced query.
		$this->args['advanced_query'] = apply_filters( 'gmw_nbl_advanced_query', $this->args['advanced_query'], $this );

		// verify units.
		$this->args['units'] = ( 'imperial' === $this->args['units'] || 'miles' === $this->args['units'] ) ? 'imperial' : 'metric';

		$this->db_fields = apply_filters( 'gmw_nbl_db_fields', $this->db_fields, $this->args, $this );
	}

	/**
	 * Get location based on entered coordinates
	 *
	 * @since 1.0
	 */
	public function nearby_coords() {

		$this->nearby        = 'coords';
		$coords              = explode( ',', $this->args['nearby'] );
		$this->coords['lat'] = $coords[0];
		$this->coords['lng'] = $coords[1];

		return true;
	}

	/**
	 * Get location based on user location
	 *
	 * @since 1.0
	 */
	public function nearby_user() {

		if ( isset( $_COOKIE['gmw_ul_lat'] ) && isset( $_COOKIE['gmw_ul_lng'] ) ) {
			$this->nearby        = 'user';
			$this->coords['lat'] = urldecode( $_COOKIE['gmw_ul_lat'] );
			$this->coords['lng'] = urldecode( $_COOKIE['gmw_ul_lng'] );
		}

		return true;
	}

	/**
	 * Get the address.
	 *
	 * @param  object $object the location object.
	 *
	 * @return [type]         [description]
	 */
	public function get_address( $object ) {
		return apply_filters( 'gmw_nbl_address', gmw_get_location_address( $object, $this->args['address_fields'] ), $this->args, $object );
	}

	/**
	 * Get directions link
	 *
	 * @param  obejct $object the location object.
	 *
	 * @return [type]         [description]
	 */
	public function get_directions_link( $object ) {

		$args = array(
			'to_lat'   => $object->latitude,
			'to_lng'   => $object->longitude,
			'from_lat' => $this->coords['lat'],
			'from_lng' => $this->coords['lng'],
			'units'    => 'imperial' === $this->args['units'] ? 'ptm' : 'ptk',
			'label'    => $this->args['directions_link'],
		);

		return apply_filters( 'gmw_nbl_get_directions_link', GMW_Maps_API::get_directions_link( $args ), $this->args, $this->coords, $object );
	}

	/**
	 * Get the results template
	 *
	 * @return [type] [description]
	 */
	public function get_template() {

		// allow using 'custom:' before template name for custom template.
		$this->args['results_template'] = str_replace( 'custom:', 'custom_', $this->args['results_template'] );

		$args = array(
			'component'     => 'posts_locator',
			'addon'         => 'nearby_locations',
			'folder_name'   => '',
			'template_name' => $this->args['results_template'],
			'file_name'     => '',
		);

		return gmw_get_template( $args );
	}

	/**
	 * Get location data.
	 *
	 * Prepare data before quering locations
	 *
	 * @return [type] [description]
	 */
	public function get_locations_data() {

		$args = array(
			'object_type'       => $this->args['object_type'],
			'lat'               => $this->coords['lat'],
			'lng'               => $this->coords['lng'],
			'radius'            => $this->args['radius'],
			'units'             => $this->args['units'],
			'output_objects_id' => true,
		);

		// Query locations from database.
		$output = GMW_Location::get_locations_data( $args, false, false, 'gmw_locations', $this->db_fields, $this->args );

		$this->locations_data = $output['locations_data'];
		$this->objects_id     = $output['objects_id'];

		return $this->objects_id;
	}

	/**
	 * Generate location data to pass to the map.
	 *
	 * Array contains latitude, longitude, map icon and info window content.
	 *
	 * @param object  $location    the location object.
	 *
	 * @param boolean $info_window enable/disable info window.
	 */
	public function map_location( $location, $info_window = false ) {

		// allow disabling info window data. If using AJAX for example.
		if ( apply_filters( 'gmw_nbl_get_info_window_content', true, $this->args, $this ) ) {
			$info_window = gmw_get_info_window_content( $location, $this->get_info_window_args( $location ), $this->args, $this );
		}

		$this->map_locations[] = apply_filters(
			'gmw_nbl_map_location_args',
			array(
				'ID'                  => $location->location_id,
				'location_id'         => $location->location_id,
				'object_id'           => $location->object_id,
				'object_type'         => $location->object_type,
				'lat'                 => $location->lat,
				'lng'                 => $location->lng,
				'icon_url'            => $this->args['map_icon_url'],
				'icon_size'           => $this->args['map_icon_size'],
				'info_window_content' => $info_window,
			),
			$location,
			$this->args,
			$this
		);
	}

	/**
	 * Generate the map element
	 *
	 * @return [type] [description]
	 */
	public function get_map_element() {

		$args = array(
			'map_id'     => $this->args['object'] . '-' . $this->args['element_id'],
			'prefix'     => $this->args['object'],
			'map_type'   => 'nearby_' . $this->args['object'] . '_locations',
			'map_width'  => $this->args['map_width'],
			'map_height' => $this->args['map_height'],
		);

		// get the map element.
		return GMW_Maps_API::get_map_element( $args );
	}

	/**
	 * Generate the map object
	 */
	public function generate_map() {

		$iw_type     = ! empty( $this->form['info_window']['iw_type'] ) ? $this->form['info_window']['iw_type'] : 'standard';
		$iw_ajax     = ! empty( $this->form['info_window']['ajax_enabled'] ) ? 1 : 0;
		$iw_template = ! empty( $this->form['info_window']['template'][ $iw_type ] ) ? $this->form['info_window']['template'][ $iw_type ] : 'default';

		$map_args = array(
			'map_id'               => $this->args['object'] . '-' . $this->args['element_id'],
			'map_type'             => $this->args['object'] . '-' . $this->args['element_id'],
			'prefix'               => 'nbl_' . $this->args['object'],
			'info_window_type'     => 'standard',
			'info_window_ajax'     => false,
			'info_window_template' => '',
			'group_markers'        => $this->args['group_markers'],
			'draggable_window'     => false,
			'icon_url'             => $this->args['map_icon_url'],
			'icon_size'            => $this->args['map_icon_size'],
		);

		$map_options = array(
			'zoom'      => 'auto',
			'mapTypeId' => isset( $this->args['map_type'] ) ? $this->args['map_type'] : 'ROADMAP',
		);

		$user_position = array(
			'lat'        => $this->coords['lat'],
			'lng'        => $this->coords['lng'],
			'map_icon'   => $this->args['user_map_icon_url'],
			'icon_size'  => $this->args['user_map_icon_size'],
			'iw_content' => 'You are here',
			'iw_open'    => false,
		);

		// generate the map.
		$map_element = gmw_get_map_object( $map_args, $map_options, $this->map_locations, $user_position, $this->args );

		// generate map scripts.
		add_action( 'wp_footer', array( $this, 'map_scripts' ) );
	}

	/**
	 * Display the list of results.
	 *
	 * The template file can be place in the theme's directory to be able to modify it and be safe update.
	 *
	 * Should be place in the theme's-or-childe-theme-folder/geo-my-wp/nearby-locations/templates/{item}/single-{item singular}.php
	 *
	 * @since 1.0
	 */
	public function display() {

		// make sure search query exists in child class.
		if ( ! method_exists( $this, 'search_query' ) ) {

			gmw_trigger_error( 'Nearby Locations search_query method not found.' );

			return;
		}

		$nearby_data = true;

		// get location based on coords.
		if ( strpos( $this->args['nearby'], ',' ) !== false ) {

			$nearby_data = $this->nearby_coords();

			// get location based on object ID.
		} elseif ( is_numeric( $this->args['nearby'] ) ) {

			$nearby_data = $this->nearby_object_id();

			// get location based on object single page ( nearby "item" to support previous versions ).
		} elseif ( 'object' === $this->args['nearby'] || 'item' === $this->args['nearby'] ) {

			$nearby_data = $this->nearby_object();

			// get location based on user's current location.
		} else {
			$nearby_data = $this->nearby_user();
		}

		// abort if nearby details do not match.
		if ( ! $nearby_data ) {
			return;
		}

		// display widget title if needed.
		if ( ! empty( $this->args['widget_title'] ) ) {
			echo html_entity_decode( $this->args['widget_title'] );
		}

		// if missing coordinates, means that we cannot find nearby locations
		// So we pass empty results.
		if ( empty( $this->coords['lat'] ) || empty( $this->coords['lng'] ) ) {

			$this->nearby_locations = array();

			// Otherwise, we run the search query.
		} else {
			$this->nearby_locations = $this->search_query();
		}

		// if no nearby locations found then check for random locations.
		if ( empty( $this->nearby_locations ) && $this->args['show_random_locations'] && method_exists( $this, 'random_search_query' ) ) {
			$this->nearby_locations = $this->random_search_query();
		}

		// get results template files.
		$this->template = $this->get_template();

		// abort if results template not found.
		if ( ! $this->template ) {

			gmw_trigger_error( 'The Nearby Locations results template you chose does not exist.' );

			return;
		}

		// abort if no results found.
		if ( empty( $this->nearby_locations ) ) {

			// display no results message if needed.
			if ( ! empty( $this->args['no_results_message'] ) ) {
				include $this->template['content_path'] . 'no-results.php';
			}

			return;
		}

		// load results stylesheet.
		if ( ! wp_style_is( $this->template['stylesheet_handle'], 'enqueue' ) ) {
			wp_enqueue_style( $this->template['stylesheet_handle'], $this->template['stylesheet_uri'] );
		}

		// create the map element if needed.
		$results_map = false;
		// deprecated.
		$map_output  = false;

		if ( $this->args['show_map'] ) {

			$results_map = $this->get_map_element();
			// For backward compatibility.
			$map_output = $results_map;
		}

		include $this->template['content_path'] . 'content-start.php';

		// run the loop.
		$this->results_loop();

		include $this->template['content_path'] . 'content-end.php';

		// generate the map.
		if ( $this->args['show_map'] ) {
			$this->generate_map();
		}
	}

	/**
	 * Marker Clusters map scripts.
	 */
	public function map_scripts() {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function(){"google_maps"===gmwVars.mapsProvider&&GMW.add_filter("gmw_map_init",function(r){return"undefined"!=typeof r.markerGroupingTypes.markers_clusterer?r:(r.markerGroupingTypes.markers_clusterer={init:function(r){"function"==typeof MarkerClusterer&&(r.clusters=new MarkerClusterer(r.map,r.markers,{imagePath:r.clustersPath,clusterClass:r.prefix+"-cluster cluster",maxZoom:15}))},clear:function(r){"function"==typeof MarkerClusterer&&0!=r.clusters&&r.clusters.clearMarkers()},addMarker:function(r,e){e.clusters.addMarker(r)},markerClick:function(r,e){google.maps.event.addListener(r,"click",function(){e.markerClick(this)})}},r)}),"leaflet"===gmwVars.mapsProvider&&GMW.add_filter("gmw_map_init",function(r){return"undefined"!=typeof r.markerGroupingTypes.markers_clusterer?r:(r.markerGroupingTypes.markers_clusterer={init:function(r){"function"==typeof L.markerClusterGroup&&(r.clusters=L.markerClusterGroup(r.options.markerClustersOptions),r.map.addLayer(r.clusters))},clear:function(r){"function"==typeof L.markerClusterGroup&&0!=r.clusters&&r.clusters.clearLayers()},addMarker:function(r,e){e.clusters.addLayer(r)},markerClick:function(r,e){r.on("click",function(){e.markerClick(this)})}},r)})});
		</script>
		<?php
	}
}
