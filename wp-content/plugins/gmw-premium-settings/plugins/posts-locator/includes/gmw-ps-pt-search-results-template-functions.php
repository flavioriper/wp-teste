<?php
/**
 * GMW PS PT search results template functions.
 *
 * @package gmw-premium-settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Set additional arguments in gmw WP_Query cache.
 *
 * @param  [type] $gmw [description].
 *
 * @return [type]      [description]
 */
function gmw_ps_pt_set_gmw_query_args( $gmw ) {

	/**
	 * Set map icons settings in gmw query cache.
	 *
	 * The icons are being generated in the WP_Query,
	 *
	 * we need to make sure the query cache udpates when icons changes.
	 *
	 * @param  [type] $gmw [description]
	 * @return [type]      [description]
	 */
	$gmw['query_args']['gmw_args']['map_icons']['usage']              = isset( $gmw['map_markers']['usage'] ) ? $gmw['map_markers']['usage'] : 'global';
	$gmw['query_args']['gmw_args']['map_icons']['default_marker']     = isset( $gmw['map_markers']['default_marker'] ) ? $gmw['map_markers']['default_marker'] : '_default.png';
	$gmw['query_args']['gmw_args']['map_icons']['post_types_markers'] = gmw_get_option( 'post_types_settings', 'post_types_icons', array() );
	$gmw['query_args']['gmw_args']['map_icons']['per_category_icons'] = gmw_get_option( 'post_types_settings', 'per_category_icons', array() );

	return $gmw;
}

/**
 * Disable keywords content search if set in form settings.
 *
 * By default, the keywords query funciton below will search
 *
 * in post title and content.
 *
 * This function disables the content search if set only to title
 *
 * in the form editor.
 *
 * @since 2.0
 *
 * @param  array $gmw  gmw form.
 *
 * @return [type]             [description]
 */
function gmw_disable_keywords_content_search( $gmw ) {

	if ( empty( $gmw['search_form']['keywords']['usage'] ) ) {

		$gmw['query_args']['keywords_usage'] = false;

	} elseif ( 'title' === $gmw['search_form']['keywords']['usage'] ) {

		// set keywords usage for gmw cache key.
		$gmw['query_args']['keywords_usage'] = 'title';

		// disable content search.
		add_filter( 'gmw_ps_disable_keywords_content_search', '__return_true' );

	} else {
		// set keywords usage for gmw cache key.
		$gmw['query_args']['keywords_usage'] = 'content';
	}

	return $gmw;
}
add_filter( 'gmw_pt_form_before_posts_query', 'gmw_disable_keywords_content_search', 10 );

/**
 * Filter WP_Query with keywords
 *
 * This filter should execute automatically when keywords key exists in gmw_args array.
 *
 * gmw_args should exists in the different queries so we know the query
 *
 * belongs to GEO my WP.
 *
 * By default keywords filter based on title and post content.
 *
 * The post content can be disabled using the filter provided.
 *
 * @since 2.0
 *
 * @param string $where where clause.
 *
 * @param object $wp_query query object.
 *
 * @author Eyal Fitoussi
 */
function gmw_ps_pt_query_keywords( $where, $wp_query ) {

	$gmw_args = $wp_query->get( 'gmw_args' );

	// abort if this is not GMW Form.
	if ( empty( $gmw_args ) ) {
		return $where;
	}

	// this value is set on form submission.
	if ( empty( $gmw_args['keywords'] ) ) {
		return $where;
	}

	global $wpdb;

	// get keywords value from URL.
	$keywords = $gmw_args['keywords'];

	// support for WordPress lower then V4.0.
	$like = method_exists( $wpdb, 'esc_like' ) ? $wpdb->esc_like( trim( $keywords ) ) : like_escape( trim( $keywords ) );
	$like = esc_sql( $like );
	$like = '%' . $like . '%';

	// search title.
	$query = "{$wpdb->posts}.post_title LIKE '{$like}'";

	// search content.
	if ( ! apply_filters( 'gmw_ps_disable_keywords_content_search', false ) ) {
		$query .= " OR {$wpdb->posts}.post_content LIKE '{$like}' OR {$wpdb->posts}.post_excerpt LIKE '{$like}'";
	}

	$where .= " AND ({$query})";

	return $where;
}
add_filter( 'posts_where', 'gmw_ps_pt_query_keywords', 20, 2 );

/**
 * Modify orderby posts query
 *
 * @since 2.0
 *
 * @param  [type] $args [description].
 *
 * @param  [type] $form [description].
 *
 * @return [type]       [description]
 */
function gmw_ps_pt_orderby_query( $args, $form ) {

	$value = gmw_ps_get_orderby_value( $form );

	// abort if no value found.
	if ( empty( $value ) ) {
		return $args;
	}

	if ( 'distance' === $value ) {

		$args['orderby'] = 'distance';

	} else {

		$args['orderby'] = $value;

		if ( 'post_modified' !== $args['orderby'] && 'post_date' !== $args['orderby'] ) {
			$args['order'] = 'ASC';
		}
	}

	return $args;
}
add_filter( 'gmw_pt_search_query_args', 'gmw_ps_pt_orderby_query', 50, 2 );

/**
 * Query include/exclude taxonomies
 *
 * @param  array $query_args tax_query array to pass to WP_Query.
 *
 * @param  array $gmw        processed form.
 *
 * @since 2.0
 *
 * @return array
 */
function gmw_ps_query_pre_defined_taxonomies( $query_args, $gmw ) {

	// for page load results.
	if ( $gmw['page_load_action'] ) {

		if ( empty( $gmw['page_load_results']['include_exclude_terms'] ) ) {
			return $query_args;
		}

		$tax_args = $gmw['page_load_results']['include_exclude_terms'];

		// on form submission.
	} else {

		if ( ! isset( $gmw['search_form']['post_types'] ) ) {
			return $query_args;
		}

		if ( 1 === count( $gmw['search_form']['post_types'] ) ) {

			$post_type = $gmw['search_form']['post_types'][0];

			// abort if no taxonomies selected.
			if ( empty( $gmw['search_form']['taxonomies'][ $post_type ] ) ) {
				return $query_args;
			}

			$tax_args = $gmw['search_form']['taxonomies'][ $post_type ];

		} else {

			if ( empty( $gmw['search_form']['taxonomies']['include_exclude_terms'] ) ) {
				return $query_args;
			}

			$tax_args = $gmw['search_form']['taxonomies']['include_exclude_terms'];
		}
	}

	foreach ( $tax_args as $taxonomy => $args ) {

		// include terms.
		if ( isset( $args['include'] ) ) {

			$query_args['tax_query'][] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'id',
				'terms'    => is_array( $args['include'] ) ? $args['include'] : explode( ',', $args['include'] ),
				'operator' => 'IN',
			);
		}

		// exclude terms.
		if ( isset( $args['exclude'] ) ) {

			$query_args['tax_query'][] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'id',
				'terms'    => is_array( $args['exclude'] ) ? $args['exclude'] : explode( ',', $args['exclude'] ),
				'operator' => 'NOT IN',
			);
		}
	}

	return $query_args;
}
add_filter( 'gmw_pt_search_query_args', 'gmw_ps_query_pre_defined_taxonomies', 15, 2 );
/**
 * Query include/exclude author - 16/08
 *
 * @param  array $gmw  processed form.
 *
 * @return array|void
 *@since 2.0
 *
 */
function gmw_ps_query_author( $gmw ) {
    // for page load results.
    if ( !empty( $gmw['search_form']['filter_author']) ){
        $user = uwp_get_displayed_user();
        if (!empty($user)){
            $gmw['query_args']['author__in'] = $user->ID; // WPCS: slow query ok.
            return $gmw;
        }
    }
    return $gmw;
}
add_filter( 'gmw_pt_form_before_posts_query', 'gmw_ps_query_author', 10, 1 );

/**
 * Date format convertor for custom fields queries
 *
 * @author Eyal Fitoussi
 *
 * @param string $date the date value.
 *
 * @param string $type the date type.
 *
 * @since 1.5
 */
function gmw_ps_pt_date_converter( $date, $type ) {

	if ( empty( $date ) ) {

		$date = date( 'ymd' );

	} else {

		if ( 'mm/dd/yyyy' === $type ) {

			$date = explode( '/', $date );
			$date = $date[2] . $date[0] . $date[1];

		} elseif ( 'dd/mm/yyyy' === $type ) {

			$date = explode( '/', $date );
			$date = $date[2] . $date[1] . $date[0];
		}
	}

	return $date;
}

/**
 * Custom fields query args.
 *
 * @param  [type] $custom_fields [description].
 *
 * @param  [type] $values        [description].
 *
 * @param  array  $gmw           gmw form.
 *
 * @return [type]            [description]
 *
 * @since 1.5
 */
function gmw_ps_pt_get_meta_query_args( $custom_fields, $values, $gmw ) {

	// Loop values.
	$count     = 0;
	$meta_args = array( 'relation' => 'AND' );

	// Loop through all fields set in the form.
	foreach ( $custom_fields as $field_args ) {

		// remove spaces from the field name.
		// We do this becuase that is how we pass it to the name attribute in the form.
		// Below we will use the otiginal name field to pass to the query.
		$field_name = str_replace( ' ', '_', $field_args['name'] );

		// Verify that field value is not empty.
		if ( empty( $values[ $field_name ] ) || ( is_array( $values[ $field_name ] ) && ! array_filter( $values[ $field_name ] ) ) ) {
			continue;
		}

		// Field value.
		$field_value = $values[ $field_name ];

		// convert date field.
		if ( 'DATE' === $field_args['type'] ) {

			if ( is_array( $field_value ) ) {

				foreach ( $field_value as $v ) {
					$temp[] = gmw_ps_pt_date_converter( $v, $field_args['date_type'] );
				}
				$field_value = $temp;

			} else {

				$field_value = gmw_ps_pt_date_converter( $field_value, $field_args['date_type'] );
			}
		}

		if ( ! empty( $field_args['compare'] ) && 'BETWEEN' === $field_args['compare'] ) {

			if ( empty( $field_value[0] ) ) {

				// Provide defaul min value.
				$field_value[0] = '0';
			}

			if ( empty( $field_value[1] ) ) {

				// Provide default max value.
				$field_value[1] = '9999999';
			}
		}

		// create the meta query args.
		$count++;
		$meta_args[] = apply_filters(
			'gmw_ps_meta_query_field_args',
			array(
				// use the original field name rather than sanitized.
				'key'     => $field_args['name'],
				'value'   => $field_value,
				'type'    => $field_args['type'],
				'compare' => $field_args['compare'],
			),
			$field_args,
			$field_value,
			$values[ $field_name ],
			$gmw
		);
	}

	if ( 0 === $count ) {
		$meta_args = array();
	}

	return $meta_args;
}

/**
 * Modify posts locator query based on custom fields.
 *
 * @param  [type] $gmw [description].
 *
 * @return [type]      [description]
 */
function gmw_ps_pt_query_custom_fields( $gmw ) {

	if ( empty( $gmw['search_form']['custom_fields'] ) || ! isset( $gmw['form_values']['cf'] ) ) {
		return $gmw;
	}

	$meta_args = gmw_ps_pt_get_meta_query_args( $gmw['search_form']['custom_fields'], $gmw['form_values']['cf'], $gmw );

	$gmw['query_args']['meta_query'] = $meta_args; // WPCS: slow query ok.

	return $gmw;
}
add_filter( 'gmw_pt_form_before_posts_query', 'gmw_ps_pt_query_custom_fields', 10 );

/**
 * Get category map icon.
 *
 * @param  [type] $post_id [description].
 *
 * @return [type]          [description]
 */
function gmw_ps_pt_get_category_map_icon( $post_id ) {

	global $gmw_category_icons_data;

	if ( ! isset( $gmw_category_icons_data ) ) {

		$data = array();

		$icons_data = gmw_get_icons();

		if ( ! empty( $icons_data['pt_category_icons']['set_icons'] ) ) {
			$data['icons'] = $icons_data['pt_category_icons']['set_icons'];
		} else {
			$data['icons'] = false;
		}

		$cat_settings         = gmw_get_option( 'post_types_settings', 'per_category_icons', array() );
		$data['taxonomies']   = $cat_settings['taxonomies'];
		$data['default_icon'] = $cat_settings['default_icon'];
		$data['icons_url']    = $icons_data['pt_category_icons']['url'];
		$data['orderby']      = isset( $cat_settings['terms_orderby'] ) ? $cat_settings['terms_orderby'] : 'term_id';
		$data['order']        = isset( $cat_settings['terms_order'] ) ? $cat_settings['terms_order'] : 'ASC';

		$gmw_category_icons_data = $data;
	}

	if ( ! $gmw_category_icons_data['icons'] ) {
		$map_icon = $gmw_category_icons_data['default_icon'];
	}

	$terms_id = wp_get_object_terms(
		$post_id,
		$gmw_category_icons_data['taxonomies'],
		array(
			'fields'  => 'ids',
			'orderby' => $gmw_category_icons_data['orderby'],
			'order'   => $gmw_category_icons_data['order'],
		)
	);

	if ( is_wp_error( $terms_id ) ) {
		$map_icon = $gmw_category_icons_data['default_icon'];
	} else {
		$map_icon = ( ! empty( $terms_id[0] ) && ! empty( $gmw_category_icons_data['icons'][ $terms_id[0] ] ) ) ? $gmw_category_icons_data['icons'][ $terms_id[0] ] : $gmw_category_icons_data['default_icon'];
	}

	return esc_url( $gmw_category_icons_data['icons_url'] . $map_icon );
}

/**
 * Generate custom map icons
 *
 * @param  string $map_icon map icon.
 *
 * @param  object $post     post object.
 *
 * @param  array  $gmw      gmw form.
 *
 * @return [type]           [description]
 *
 * @since 1.5
 */
function gmw_ps_pt_map_icon( $map_icon, $post, $gmw ) {

	$pt_settings  = gmw_get_options_group( 'post_types_settings' );
	$icons_data   = gmw_get_icons();
	$icons_url    = $icons_data['pt_map_icons']['url'];
	$form_field   = ! empty( $gmw['map_markers'] ) ? $gmw['map_markers'] : array();
	$new_map_icon = $map_icon;
	$usage        = isset( $form_field['usage'] ) ? $form_field['usage'] : 'global';
	$default_icon = isset( $form_field['default_marker'] ) ? $form_field['default_marker'] : GMW()->default_icons['location_icon_url'];

	// if showing same global map icon.
	if ( 'global' === $usage ) {

		$new_map_icon = $default_icon;

		// per post map icon.
	} elseif ( 'per_post' === $usage ) {

		$new_map_icon = ! empty( $post->map_icon ) ? $post->map_icon : $default_icon;

		// per post type map icons.
	} elseif ( 'per_post_type' === $usage && ! empty( $pt_settings['post_types_icons'][ $post->post_type ] ) ) {

		$new_map_icon = $pt_settings['post_types_icons'][ $post->post_type ];

		// per category map icons.
	} elseif ( 'per_category' === $usage && ! empty( $pt_settings['per_category_icons']['taxonomies'] ) ) {

		return gmw_ps_pt_get_category_map_icon( $post->ID );

		// map icon as featured image.
	} elseif ( 'image' === $usage ) {

		if ( has_post_thumbnail( $post->ID ) ) {

			$thumb        = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), array( 30, 30 ) );
			$new_map_icon = $thumb[0];

		} else {
			$new_map_icon = GMW_PS_URL . '/assets/map-icons/_no_image.png';
		}
	} else {
		$new_map_icon = '_default.png';
	}

	if ( 'image' !== $usage ) {
		$map_icon = ( empty( $new_map_icon ) || '_default.png' === $new_map_icon ) ? $icons_url . $default_icon : $icons_url . $new_map_icon;
	} else {
		$map_icon = $new_map_icon;
	}

	return $map_icon;
}
add_filter( 'gmw_pt_map_icon', 'gmw_ps_pt_map_icon', 10, 3 );

/**
 * Generate custom map icons via posts loop for Posts Global Maps.
 *
 * For per cateroy and featured image icons.
 *
 * @param  string $map_icon map icon.
 *
 * @param  object $post     post object.
 *
 * @param  array  $gmw      gmw form.
 *
 * @return [type]           [description]
 */
function gmw_ps_pt_get_map_icon_via_loop( $map_icon, $post, $gmw ) {

	if ( ! isset( $gmw['map_markers']['usage'] ) ) {
		return $map_icon;
	}

	$usage = $gmw['map_markers']['usage'];

	// featured image map icon.
	if ( 'per_category' === $usage ) {
		return gmw_ps_pt_get_category_map_icon( $post->ID );
	}

	if ( 'image' === $usage ) {
		if ( has_post_thumbnail( $post->ID ) ) {
			$thumb    = wp_get_attachment_image_url( get_post_thumbnail_id( $post->ID ), array( 30, 30 ) );
			$map_icon = $thumb;
		} else {
			$map_icon = GMW_PS_URL . '/assets/map-icons/_no_image.png';
		}
	}

	return $map_icon;
}

/**
 * Set custom posts map icons.
 *
 * This function set icons for "global", "per post" and "per post type" map icons.
 *
 * everything is done during WP_Query.
 *
 * @param  [type] $clauses [description].
 *
 * @param  [type] $gmw     [description].
 *
 * @return [type]          [description]
 */
function gmw_ps_pt_set_map_icons_via_query( $clauses, $gmw ) {

	if ( ! empty( $gmw['map_markers']['usage'] ) ) {
		$usage = $gmw['map_markers']['usage'];
	} else {
		$usage = 'global';
	}

	// abort if not the right usage.
	if ( ! in_array( $usage, array( 'global', 'per_post_type', 'per_post' ), true ) ) {
		return $clauses;
	}

	global $wpdb;

	// get icons url.
	$icons     = gmw_get_icons();
	$icons_url = $icons['pt_map_icons']['url'];

	// get default marker. If no icon provided or using the _default.png,
	// we than pass blank value, to use Google's default red marker.
	if ( ! empty( $gmw['map_markers']['default_marker'] ) && '_default.png' !== $gmw['map_markers']['default_marker'] ) {
		$default_icon = $icons_url . $gmw['map_markers']['default_marker'];
	} else {
		$default_icon = GMW()->default_icons['location_icon_url'];
	}

	// if global icon.
	if ( 'global' === $usage ) {

		$clauses['fields'] .= $wpdb->prepare( ', %s as map_icon', $default_icon );

		return $clauses;
	}

	// if per post, get the icon from locations table.
	if ( 'per_post' === $usage ) {

		$clauses['fields'] .= $wpdb->prepare( ", IF ( gmw_locations.map_icon IS NOT NULL AND gmw_locations.map_icon != '_default.png', CONCAT( %s, gmw_locations.map_icon ), %s ) as map_icon", $icons_url, $default_icon );

		return $clauses;
	}

	// if per post types icon.
	if ( 'per_post_type' === $usage ) {

		// Get post types icon settings.
		$post_types_icons = gmw_get_option( 'post_types_settings', 'post_types_icons', array() );

		if ( ! empty( $post_types_icons ) ) {

			$clauses['fields'] .= ", CASE {$wpdb->posts}.post_type ";

			foreach ( $post_types_icons as $post_type => $icon ) {

				// if custom icon exists, use it.
				if ( isset( $icon ) && '_default.png' !== $icon ) {

					$icon = $icons_url . $icon;

					// otherwise, set to default icon.
				} else {

					$icon = $default_icon;
				}

				$clauses['fields'] .= $wpdb->prepare( ' WHEN %s THEN %s ', array( $post_type, $icon ) );
			}

			$clauses['fields'] .= $wpdb->prepare( ' ELSE %s END as map_icon ', $default_icon );
		}
	}

	return $clauses;
}
