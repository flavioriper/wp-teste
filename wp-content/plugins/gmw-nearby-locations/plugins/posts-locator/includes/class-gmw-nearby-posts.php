<?php
/**
 * Nearby Posts Location class.
 *
 * Filter and display nearby Posts Locator.
 *
 * @package gmw-nearby-locations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'GMW_Nearby_Locations' ) ) {
	return;
}

/**
 * GMW_Nearby_Posts class.
 *
 * @version 1.0
 *
 * @author Eyal Fitoussi
 */
class GMW_Nearby_Posts extends GMW_Nearby_Locations {

	/**
	 * Extends the default shortcode atts.
	 *
	 * @since 1.0
	 *
	 * @var array
	 */
	public $args = array(
		'object'              => 'post',
		'post_types'          => 'post',
		'taxonomies'          => '',
		'taxonomies_relation' => 'OR',
		'include_taxonomies'  => '',
		'exclude_taxonomies'  => '',
		'include_terms'       => '',
		'exclude_terms'       => '',
		'include_tax'         => '',   // deprecated.
		'exclude_tax'         => '',   // deprecated.
		'tax_relation'        => 'OR', // deprecated.
	);

	/**
	 * Enable/disable simple query taxonomies for advacned query
	 *
	 * @var boolean
	 */
	public $simple_query_taxonomies = false;

	/**
	 * _construct function.
	 *
	 * @param array $atts shortcode attributes.
	 */
	public function __construct( $atts = array() ) {

		parent::__construct( $atts );

		// Support deprecated attribtes.
		if ( '' !== $this->args['include_tax'] && '' === $this->args['include_taxonomies'] ) {
			$this->args['include_taxonomies'] = $this->args['include_tax'];
		}

		if ( '' !== $this->args['exclude_tax'] && '' === $this->args['exclude_taxonomies'] ) {
			$this->args['exclude_taxonomies'] = $this->args['exclude_tax'];
		}

		if ( 'AND' === $this->args['tax_relation'] && 'OR' === $this->args['taxonomies_relation'] ) {
			$this->args['taxonomies_relation'] = 'AND';
		}

		unset( $this->args['include_tax'], $this->args['exclude_tax'], $this->args['tax_relation'] );
	}

	/**
	 * Info window arguments.
	 *
	 * @param  object $location the location object.
	 *
	 * @return array  infor window args.
	 */
	public function get_info_window_args( $location ) {

		return array(
			'type'            => 'standard',
			'image'           => $location->image,
			'url'             => get_permalink( $location->object_id ),
			'title'           => get_the_title( $location->object_id ),
			'address_fields'  => $this->args['address_fields'],
			'directions_link' => ! empty( $this->args['directions_link'] ) ? true : false,
			'distance'        => $this->args['show_distance'],
		);
	}

	/**
	 * Get location based on specific post ID
	 *
	 * @since 1.0
	 */
	public function nearby_object_id() {

		// Get post's location base on the post/item ID.
		$location = gmw_get_post_location( $this->args['nearby'] );

		// abort if no post's location found.
		if ( empty( $location ) ) {
			return false;
		}

		$this->nearby        = 'post_id';
		$this->coords['lat'] = $location->latitude;
		$this->coords['lng'] = $location->longitude;
		$this->object_id     = $this->args['nearby'];

		return true;
	}

	/**
	 * Get location of post in single post page
	 */
	public function nearby_object() {

		// make sure this is a single post page.
		if ( ! is_single() && ! is_page() ) {
			return false;
		}

		$post_id = get_queried_object_id();

		// get post's location.
		$location = gmw_get_post_location( $post_id );

		// if no post's location found abort!
		if ( empty( $location ) ) {
			return false;
		}

		$this->nearby        = 'post';
		$this->coords['lat'] = $location->latitude;
		$this->coords['lng'] = $location->longitude;
		$this->object_id     = $post_id;

		return true;
	}

	/**
	 * Simple query taxonomies
	 *
	 * @param  boolean $return_ids return array of posts ID or SQL query.
	 *
	 * @return [type]              [description]
	 */
	public function query_taxonomies( $return_ids = false ) {

		$items = false;

		// include/exclude taxonomies/terms.
		if ( ! empty( $this->args['include_taxonomies'] ) ) {
			$type     = 'taxonomy';
			$items    = $this->args['include_taxonomies'];
			$operator = 'IN';
		} elseif ( ! empty( $this->args['exclude_taxonomies'] ) ) {
			$type     = 'taxonomy';
			$items    = $this->args['exclude_taxonomies'];
			$operator = 'NOT IN';
		} elseif ( ! empty( $this->args['include_terms'] ) ) {
			$type     = 'term_id';
			$items    = $this->args['include_terms'];
			$operator = 'IN';
		} elseif ( ! empty( $this->args['exclude_terms'] ) ) {
			$type     = 'term_id';
			$items    = $this->args['exclude_terms'];
			$operator = 'NOT IN';
		}

		// include/exclude taxonomies/tax terms.
		if ( false === $items ) {

			return '';
		} else {

			global $wpdb;

			// prepare for query.
			$items = str_replace( array( ' ', ',' ), array( '', "','" ), $items );
			$items = stripslashes( esc_sql( $items ) );

			$posts_id = $wpdb->get_col(
				"
				SELECT object_id 
				FROM {$wpdb->prefix}term_relationships tr
				INNER JOIN {$wpdb->prefix}term_taxonomy tt
				ON tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE tt.{$type} IN ( '{$items}' ) 
				GROUP BY tr.object_id "
			); // WPCS: db call ok, cache ok, unprepared SQL ok.

			if ( $return_ids ) {
				return $posts_id;
			} else {
				return ! empty( $posts_id ) ? " AND $wpdb->posts.ID {$operator} ( " . implode( ',', $posts_id ) . ' ) ' : ' AND 1 = 0 ';
			}
		}
	}

	/**
	 * Advanced taxonomies/terms include
	 *
	 * @return [type] [description]
	 */
	public function advanced_query_taxonomies() {

		$taxonomies = explode( ',', $this->args['taxonomies'] );
		$tax_args   = array( 'relation' => $this->args['taxonomies_relation'] );

		// loop through taxonomies and build the query.
		foreach ( $taxonomies as $taxonomy ) {

			$args = array( 'relation' => 'OR' );

			// support previous version with "tax".
			if ( isset( $this->args[ 'tax_' . $taxonomy . '_terms_include' ] ) ) {
				$this->args[ 'taxonomy_' . $taxonomy . '_terms_include' ] = $this->args[ 'tax_' . $taxonomy . '_terms_include' ];
			}
			// include specifc taxonomy terms if provided.
			if ( ! empty( $this->args[ 'taxonomy_' . $taxonomy . '_terms_include' ] ) ) {

				$terms_id = explode( ',', $this->args[ 'taxonomy_' . $taxonomy . '_terms_include' ] );

				// otherwise we need to pass all terms to include the entire taxonomy.
			} else {
				$terms_id = get_terms( $taxonomy, array( 'fields' => 'ids' ) );
			}

			$args[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'id',
				'terms'    => $terms_id,
				'operator' => 'IN',
			);

			// Support previous version with "tax_".
			if ( isset( $this->args[ 'tax_' . $taxonomy . '_terms_exclude' ] ) ) {
				$this->args[ 'taxonomy_' . $taxonomy . '_terms_exclude' ] = $this->args[ 'tax_' . $taxonomy . '_terms_exclude' ];
			}

			// Exclude taxonomies.
			if ( ! empty( $this->args[ 'taxonomy_' . $taxonomy . '_terms_exclude' ] ) ) {
				$args['relation'] = 'AND';
				$args[]           = array(
					'taxonomy' => $taxonomy,
					'field'    => 'id',
					'terms'    => explode( ',', $this->args[ 'taxonomy_' . $taxonomy . '_terms_exclude' ] ),
					'operator' => 'NOT IN',
				);
			}

			$tax_args[] = $args;
		}

		return $tax_args;
	}

	/**
	 * Get taxonomy attributes to be used in cache.
	 *
	 * @return [type] [description]
	 */
	public function get_taxonomy_attributes() {

		return array(
			'include_taxes' => $this->args['include_taxonomies'],
			'exclude_taxes' => $this->args['exclude_taxonomies'],
			'include_terms' => $this->args['include_terms'],
			'exclude_terms' => $this->args['exclude_terms'],
		);
	}

	/**
	 * Add taxonomies arguments to locations query cache key.
	 *
	 * @param  array $args query arguments.
	 *
	 * @return [type]       [description]
	 */
	public function locations_query_cache_key( $args ) {

		$args['taxonomies'] = $this->get_taxonomy_attributes();

		return $args;
	}

	/**
	 * Join locations table to WP Posts table.
	 *
	 * To be used with simple query.
	 *
	 * @param array $clauses  query clauses.
	 *
	 * @return array  modified clauses.
	 */
	public function join_posts_table( $clauses ) {

		global $wpdb;

		// query taxonomies.
		$clauses['where'] .= $this->query_taxonomies();

		// prepare post types for query.
		$post_types = str_replace( array( ' ', ',' ), array( '', "','" ), $this->args['post_types'] );
		$post_types = stripslashes( esc_sql( $post_types ) );

		$clauses['fields'] .= ",{$wpdb->posts}.post_title, {$wpdb->posts}.post_type";
		$clauses['from']   .= " INNER JOIN {$wpdb->posts} wp_posts ON gmw_locations.object_id = wp_posts.ID";
		$clauses['where']  .= " AND wp_posts.post_type IN ( '{$post_types}' ) AND wp_posts.post_status IN ( 'publish' ) ";

		// When displaying nearby post or post ID exclude the post being displayed from the results.
		if ( ( 'post' === $this->nearby || 'post_id' === $this->nearby ) && absint( $this->object_id ) ) {
			$clauses['where'] .= "AND wp_posts.ID NOT IN ( {$this->object_id} ) ";
		}

		if ( 'distance' !== $this->args['orderby'] ) {
			$clauses['orderby'] = "ORDER BY wp_posts.{$this->args['orderby']}";
		}

		if ( is_numeric( $this->args['results_count'] ) ) {
			$clauses['limit'] = "LIMIT {$this->args['results_count']}";
		}

		return $clauses;
	}

	/**
	 * Modify wp_query clauses to search by proximity
	 *
	 * @param array $clauses query clauses.
	 *
	 * @return modified $clauses
	 */
	public function query_clauses( $clauses ) {

		// Simple query taxonomies.
		if ( $this->simple_query_taxonomies ) {

			$tax_query         = $this->query_taxonomies();
			$clauses['where'] .= $tax_query;

			// abort early if no posts were found based on taxomoies.
			if ( ' AND 1 = 0 ' === $tax_query ) {
				return $clauses;
			}
		}

		$count     = 0;
		$db_fields = '';

		// generate the db fields.
		foreach ( $this->db_fields as $field ) {

			if ( $count > 0 ) {
				$db_fields .= ', ';
			}

			$count++;

			if ( strpos( $field, 'as' ) !== false ) {

				$field = explode( ' as ', $field );

				$db_fields .= "gmw_locations.{$field[0]} as {$field[1]}";

				// Here we are including latitude and longitude fields
				// using their original field name.
				// for backward compatibility, we also need to have "lat" and "lng"
				// in the location object and that is what we did in the line above.
				// The lat and lng field are too involve and need to carfully change it.
				// eventually we want to completely move to using latitude and longitude.
				if ( 'latitude' === $field[0] || 'longitude' === $field[0] ) {
					$db_fields .= ",gmw_locations.{$field[0]}";
				}
			} else {

				$db_fields .= "gmw_locations.{$field}";
			}
		}

		global $wpdb;

		$where_clause_filter = apply_filters( 'gmw_nbp_filter_object_type_in_where_clause', false, $this );

		// add the location db fields to the query.
		$clauses['fields'] .= ", {$db_fields}";
		$clauses['having']  = '';
		$tjoin              = "{$wpdb->base_prefix}gmw_locations gmw_locations ON $wpdb->posts.ID = gmw_locations.object_id ";

		if ( ! $where_clause_filter ) {
			$tjoin .= "AND gmw_locations.object_type = 'post' ";
		}

		// In multisite we need to check for the blog ID.
		if ( is_multisite() && ! empty( $wpdb->blogid ) ) {
			$blog_id           = absint( $wpdb->blogid );
			$clauses['where'] .= "AND gmw_locations.blog_id = {$blog_id} ";
		}

		/**
		 * Address filters disabled at the moment.
		 *
		 * Might be availabe in the future.
		 *
		 * $address_filters = GMW_Location::query_address_fields( $this->get_address_filters(), $this->args );
		 */
		$address_filters = '';

		// when address provided, and not filtering based on address fields, we will do proximity search.
		if ( '' === $address_filters && ! empty( $this->coords['lat'] ) && ! empty( $this->coords['lng'] ) ) {

			// generate some radius/units data.
			if ( in_array( $this->args['units'], array( 'imperial', 3959, 'miles', '3959' ), true ) ) {
				$earth_radius = 3959;
				$units        = 'mi';
				$degree       = 69.0;
			} else {
				$earth_radius = 6371;
				$units        = 'km';
				$degree       = 111.045;
			}

			// add units to locations data.
			$clauses['fields'] .= ", '{$units}' AS units";

			// since these values are repeatable, we escape them previous
			// the query instead of running multiple prepares.
			$lat      = esc_sql( $this->coords['lat'] );
			$lng      = esc_sql( $this->coords['lng'] );
			$distance = ! empty( $this->args['radius'] ) ? esc_sql( $this->args['radius'] ) : '';

			$clauses['fields'] .= ", ROUND( {$earth_radius} * acos( cos( radians( {$lat} ) ) * cos( radians( gmw_locations.latitude ) ) * cos( radians( gmw_locations.longitude ) - radians( {$lng} ) ) + sin( radians( {$lat} ) ) * sin( radians( gmw_locations.latitude ) ) ),1 ) AS distance";

			$clauses['join'] .= "INNER JOIN {$tjoin}";

			if ( ! empty( $distance ) ) {

				if ( ! apply_filters( 'gmw_disable_query_clause_between', false, $this ) ) {

					// calculate the between point.
					$bet_lat1 = $lat - ( $distance / $degree );
					$bet_lat2 = $lat + ( $distance / $degree );
					$bet_lng1 = $lng - ( $distance / ( $degree * cos( deg2rad( $lat ) ) ) );
					$bet_lng2 = $lng + ( $distance / ( $degree * cos( deg2rad( $lat ) ) ) );

					$clauses['where'] .= " AND gmw_locations.latitude BETWEEN {$bet_lat1} AND {$bet_lat2}";
					// $clauses['where'] .= " AND gmw_locations.longitude BETWEEN {$bet_lng1} AND {$bet_lng2} ";
				}

				if ( $where_clause_filter ) {
					$clauses['where'] .= " AND gmw_locations.object_type = 'post'";
				}

				// filter locations based on the distance.
				$clauses['having'] = "HAVING distance <= {$distance} OR distance IS NULL";

				// order by distance.
				if ( 'distance' === $this->args['query_args']['orderby'] ) {

					$order = esc_sql( $this->args['query_args']['order'] );

					$clauses['orderby'] = 'distance ' . $order;
				}
			}
		} else {

			$clauses['join']  .= " INNER JOIN {$tjoin}";
			$clauses['where'] .= " {$address_filters} AND ( gmw_locations.latitude != 0.000000 && gmw_locations.longitude != 0.000000 ) ";

			if ( $where_clause_filter ) {
				$clauses['where'] .= " AND gmw_locations.object_type = 'post'";
			}
		}

		// When displaying nearby post or post ID exclude the post being displayed from the results.
		if ( ( 'post' === $this->nearby || 'post_id' === $this->nearby ) && absint( $this->object_id ) ) {
			$clauses['where'] .= " AND {$wpdb->posts}.ID NOT IN ( {$this->object_id} ) ";
		}

		// modify the clauses.
		$clauses = apply_filters( 'gmw_nearby_posts_location_query_clauses', $clauses, $this->args, $this );

		// make sure we have groupby to only pull posts one time.
		if ( empty( $clauses['groupby'] ) ) {
			$clauses['groupby'] = $wpdb->prefix . 'posts.ID';
		}

		// add having clause.
		$clauses['groupby'] .= ' ' . $clauses['having'];

		unset( $clauses['having'] );

		return $clauses;
	}

	/**
	 * Search query
	 *
	 * @return [type] [description]
	 */
	public function search_query() {

		// Enable advacned query for advanced taxonomies.
		if ( ! empty( $this->args['taxonomies'] ) ) {
			$this->args['advanced_query'] = true;
		}

		/**
		 * Simple search query.
		 *
		 * This might be a bit faster then the advanced query
		 *
		 * but it does not use the native WP_Query.
		 */
		if ( ! $this->args['advanced_query'] ) {

			add_filter( 'gmw_get_locations_data_args', array( $this, 'locations_query_cache_key' ), 20 );
			add_filter( 'gmw_get_locations_query_clauses', array( $this, 'join_posts_table' ), 20 );

			// do locations query.
			$this->get_locations_data();

			remove_filter( 'gmw_get_locations_data_args', array( $this, 'locations_query_cache_key' ), 20 );
			remove_filter( 'gmw_get_locations_query_clauses', array( $this, 'join_posts_table' ), 20 );

			return $this->locations_data;

			/**
			 * Advanced query using WP_Query.
			 */
		} else {

			$tax_args = array();

			// tax query can be disable if a custom query is needed.
			if ( apply_filters( 'gmw_nearby_posts_enable_taxonomy_search_query', true, $this->args, $this ) ) {

				// advanced taxonomies query.
				if ( ! empty( $this->args['taxonomies'] ) ) {

					$tax_args = $this->advanced_query_taxonomies();

					// Otherwise, simple tax query is enabled and takes place in the query_clauses method.
				} else {
					$this->simple_query_taxonomies = true;
				}
			}

			// query args.
			$this->args['query_args'] = apply_filters(
				'gmw_nearby_posts_search_query_args',
				array(
					'post_type'           => explode( ',', $this->args['post_types'] ),
					'post_status'         => array( 'publish' ),
					'tax_query'           => apply_filters( 'gmw_nearby_posts_tax_query', $tax_args, $this->args ), // WPCS: slow query ok.
					'posts_per_page'      => ! empty( $this->args['results_count'] ) ? $this->args['results_count'] : -1,
					'meta_query'          => apply_filters( 'gmw_nearby_posts_meta_query', false, $this->args ), // WPCS: slow query ok.
					'ignore_sticky_posts' => 1,
					'orderby'             => $this->args['orderby'],
					'order'               => $this->args['order'],
					'gmw_args'            => array( // for cache key.
						'nearby'        => $this->args['nearby'],
						'object_id'     => $this->object_id,
						'nearby_coords' => $this->coords,
						'radius'        => $this->args['radius'],
						'units'         => $this->args['units'],
						'tax_cache'     => $this->get_taxonomy_attributes(),
						'rpl'           => $this->args['radius_per_location'],
					),
				),
				$this->args
			);

			$internal_cache = GMW()->internal_cache;

			if ( $internal_cache ) {

				// cache key.
				$hash            = md5( wp_json_encode( $this->args['query_args'] ) );
				$query_args_hash = 'gmw' . $hash . GMW_Cache_Helper::get_transient_version( 'gmw_get_object_post_query' );
				$this->query     = get_transient( $query_args_hash );
			}

			// look for query in cache.
			if ( ! $internal_cache || empty( $this->query ) ) {

				/** Echo 'Nearby Posts Query Done!'; */

				// add filters to wp_query to do radius calculation and get locations detail into results.
				add_filter( 'posts_clauses', array( $this, 'query_clauses' ) );

				// posts query.
				$this->query = new WP_Query( $this->args['query_args'] );

				remove_filter( 'posts_clauses', array( $this, 'query_clauses' ) );

				// Set new query in transient.
				if ( $internal_cache ) {

					/**
					 * This is a temporary solution for an issue with caching SQL requests
					 * For some reason when LIKE is being used in SQL WordPress replace the % of the LIKE
					 * with long random numbers. This SQL is still being saved in the transient. Hoever,
					 * it is not being pulled back properly when GEO my WP trying to use it.
					 * It shows an error "unserialize(): Error at offset " and the value returns blank.
					 * As a temporary work around, we remove the [request] value, which contains the long numbers, from the WP_Query and save it in the transien without it.
					 *
					 * @var [type]
					 */
					$request = $this->query->request;
					unset( $this->query->request );
					set_transient( $query_args_hash, $this->query, GMW()->internal_cache_expiration );
					$this->query->request = $request;
				}
			}

			return $this->query->posts;
		}
	}

	/**
	 * If no posts were found we will try to get posts
	 * by searching without a distance restriction to get random posts
	 *
	 * @return [type] [description]
	 */
	public function random_search_query() {
		$this->args['radius'] = '';
		return $this->search_query();
	}

	/**
	 * Display the list of results.
	 *
	 * The template file can be place in the theme's directory for safe update.
	 *
	 * Should be place in the theme's-or-childe-theme-folder/geo-my-wp/nearby-locations/templates/{item}/single-{item singular}.php
	 */
	public function results_loop() {

		global $post;

		// loop posts array.
		foreach ( $this->nearby_locations as $post ) { // WPCS: override globals ok.

			// when doing a simple query some data is missing from the post object.
			if ( ! $this->args['advanced_query'] ) {
				$post->ID = $post->object_id;
			}

			$distance   = false;
			$address    = false;
			$directions = false;

			if ( ! empty( $this->args['directions_link'] ) ) {
				$directions = $this->get_directions_link( $post );
			}

			$image_class = '';
			$image       = false;

			// check if we need to display featured image.
			if ( $this->args['show_image'] && has_post_thumbnail( $post->ID ) ) {
				$image_class = 'has-image';
				$image       = gmw_get_post_featured_image( $post, $this->args, 'medium', array( 'class' => 'skip-lazy' ) );

				// add the post image to the post object
				// so we could use it in the info-window as well.
				$post->image = $image;
			}

			// Title.
			$title = esc_html( stripslashes( get_the_title( $post->ID ) ) );
			$title = '<a href="' . esc_url( get_permalink( $post->ID ) ) . '">' . $title . '</a>';

			// create distance variable.
			if ( ! empty( $post->distance ) && $this->args['show_distance'] ) {
				$distance = esc_html( $post->distance . $post->units );
			}

			// create address variable.
			if ( ! empty( $this->args['address_fields'] ) ) {
				$address = esc_html( $this->get_address( $post ) );
			}

			// pass items to map info-window if needed.
			if ( $this->args['show_map'] ) {
				$this->map_location( $post );
			}

			// include list of results only if needed.
			if ( $this->args['show_locations_list'] ) {
				include $this->template['content_path'] . 'content-single-item.php';
			}
		}

		wp_reset_postdata();
	}
}
