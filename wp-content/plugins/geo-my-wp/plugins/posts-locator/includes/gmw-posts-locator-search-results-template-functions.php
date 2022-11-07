<?php
/**
 * GEO my WP - Posts Locator search results tempalte functions.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Generate tax_query args for taxonomy terms query.
 *
 * To be used with posts locator WP_Query.
 *
 * @since 3.1
 *
 * @param  array  $tax_args [description].
 *
 * @param  [type] $gmw      [description].
 *
 * @return [type]           [description]
 */
function gmw_pt_get_tax_query_args($gmw,  $tax_args = array()) {#ORDEM ALTERADA SEGUINDO PHP 8.0

	$tax_value = false;
	$output    = array( 'relation' => 'AND' );

	foreach ( $tax_args as $taxonomy => $values ) {

		if ( array_filter( $values ) ) {
			$output[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'id',
				'terms'    => $values,
				'operator' => 'IN',
			);
		}

		// extend the taxonomy query.
		$output = apply_filters( 'gmw_' . $gmw['prefix'] . '_query_taxonomy', $output, $taxonomy, $values, $gmw );
	}

	// verify that there is at least one query to performe.
	if ( empty( $output[0] ) ) {
		$output = array();
	}

	return $output;
}

/**
 * Get posts featured image.
 *
 * @param  object|integer $post the post object or post ID.
 *
 * @param  array          $gmw  gmw form.
 *
 * @param  array|string   $size image size in pixels. Will override the size provided in $gmw if any.
 *
 * @param  array          $attr image attributes.
 *
 * @return HTML element.
 */
function gmw_get_post_featured_image( $post = 0, $gmw = array(), $size = '', $attr = array() ) {#ORDEM ALTERADA SEGUINDO PHP 8.0

	$output = '';

	// If image size was not provided we are going to use
	// the size from the form settings if exists.
	if ( empty( $size ) ) {

		$size = 'post-thumbnail';

		// If form provide image size.
		if ( ! empty( $gmw['search_results']['image']['width'] ) && ! empty( $gmw['search_results']['image']['height'] ) ) {

			$size = array(
				$gmw['search_results']['image']['width'],
				$gmw['search_results']['image']['height'],
			);
		}
	}

	// Make sure the class gmw-image is added to all images.
	if ( isset( $attr['class'] ) ) {
		$attr['class'] .= ' gmw-image';
	} else {
		$attr['class'] = 'gmw-image';
	}

	// filter the image args.
	$args = apply_filters(
		'gmw_pt_post_featured_image_args',
		array(
			'size' => $size,
			'attr' => $attr,
		),
		$post,
		$gmw
	);

	// Look for post thumbnail.
	if ( has_post_thumbnail() ) {

		$output .= '<div class="post-thumbnail">';
		$output .= get_the_post_thumbnail(
			$post,
			$args['size'],
			$args['attr']
		);
		$output .= '</div>';

		// Otherise, use the default "No image".
	} else {

		if ( ! is_array( $args['size'] ) ) {
			$args['size'] = array( 200, 200 );
		}

		$output .= '<div class="post-thumbnail no-image">';
		$output .= '<img class="gmw-image"';
		$output .= 'src="' . GMW_IMAGES . '/no-image.jpg" ';
		$output .= 'width=" ' . esc_attr( $args['size'][0] ) . '" ';
		$output .= 'height=" ' . esc_attr( $args['size'][1] ) . '" ';
		$output .= '/>';
		$output .= '</div>';
	}

	return apply_filters( 'gmw_pt_post_feature_image', $output, $post, $gmw, $size, $attr );
}

/**
 * Get posts carousel image.
 *
 * @param  object|integer $post the post object or post ID.
 *
 * @param  array          $gmw  gmw form.
 *
 * @param  array|string   $size image size in pixels. Will override the size provided in $gmw if any.
 *
 * @param  array          $attr image attributes.
 *
 * @return HTML element.
 */
function gmw_get_post_carousel_image( $post = 0, $gmw = array(), $size = '', $attr = array() ) {

	$output = '';
    $images = [];
	// If image size was not provided we are going to use
	// the size from the form settings if exists.
	if ( empty( $size ) ) {

		$size = 'post-thumbnail';

		// If form provide image size.
		if ( ! empty( $gmw['search_results']['image']['width'] ) && ! empty( $gmw['search_results']['image']['height'] ) ) {

			$size = array(
				$gmw['search_results']['image']['width'],
				$gmw['search_results']['image']['height'],
			);
		}
	}

	// Make sure the class gmw-image is added to all images.
	if ( isset( $attr['class'] ) ) {
		$attr['class'] .= ' gmw-carousel-image';
	} else {
		$attr['class'] = 'gmw-carousel-image';
	}

	// filter the image args.
	$args = apply_filters(
		'gmw_pt_post_carousel_image_args',
		array(
			'size' => $size,
			'attr' => $attr,
		),
		$post,
		$gmw
	);
//    get_field_object('valor_do_imovel', $post->ID)

    $image_featured_image   = get_field('featured_image', $post->ID);
    $image_galeria_interior = get_field('galeria_interior', $post->ID);
    $image_galeria_exterior = get_field('galeria_exterior', $post->ID);

    if (isset($image_featured_image) && !empty($image_featured_image)) {
        $images[] = ['title' => $image_featured_image['title'], 'url' => $image_featured_image['url']];
    }

    if (isset($image_galeria_interior) && !empty($image_galeria_interior)) {
        shuffle($image_galeria_interior);
        foreach (array_slice($image_galeria_interior, 0, 2) as $interior) {
            $images[] = ['title' => $interior['title'], 'url' => $interior['url']];
        }
    }

    if (isset($image_galeria_exterior) && !empty($image_galeria_exterior)) {
        shuffle($image_galeria_exterior);
        foreach (array_slice($image_galeria_exterior, 0, 2) as $exterior) {
            $images[] = ['title' => $exterior['title'], 'url' => $exterior['url']];
        }
    }

    // Look for post thumbnail.
	if (!empty($images)) {
        $output .= '<div class="post-thumbnail">';
        $output .= '<div class="owl-carousel">';
        foreach ($images as $img){
            $output .= '<img class="gmw-image owl-lazy"';
            $output .= 'data-src="' . $img['url'] . '"';
            $output .= 'alt="' . $img['title'] . '"';
            $output .= 'widht="150px"';
            $output .= 'height="150px"';
            $output .= '/>';
        }
       
        $output .= '</div>';
        $output .= '</div>';

		// Otherise, use the default "No image".
	} else {
        if ( ! is_array( $args['size'] ) ) {
            $args['size'] = array( 200, 200 );
        }

        $output .= '<div class="post-thumbnail no-image">';
        $output .= '<img class="gmw-image"';
        $output .= 'src="' . GMW_IMAGES . '/no-image.jpg" ';
        $output .= 'width=" ' . esc_attr( $args['size'][0] ) . '" ';
        $output .= 'height=" ' . esc_attr( $args['size'][1] ) . '" ';
        $output .= '/>';
        $output .= '</div>';
	}

	return apply_filters( 'gmw_pt_post_carousel_image', $output, $post, $gmw, $size, $attr );
}

/**
 * Display featured image in search results
 *
 * @param  [type] $post [description].
 *
 * @param  array  $gmw  [description].
 *
 * @return [type]       [description]
 */
function gmw_search_results_featured_image( $post, $gmw = array() ) {

	if ( ! $gmw['search_results']['image']['enabled'] ) {
		return;
	}

	echo gmw_get_post_featured_image( $post, $gmw ); // WPCS: XSS ok.
}
/**
 * Display carousel image in search results
 *
 * @param  [type] $post [description].
 *
 * @param  array  $gmw  [description].
 *
 * @return [type]       [description]
 */
function gmw_search_results_carousel_image( $post, $gmw = array() ) {

	if ( ! $gmw['search_results']['carousel_image'] ) {
		return;
	}

	echo gmw_get_post_carousel_image( $post, $gmw ); // WPCS: XSS ok.
}

/**
 * Get taxonomies in search results
 *
 * @param  object $post Post object.
 *
 * @param  array  $gmw  gmw form.
 *
 * @return [type]       [description]
 */
function gmw_search_results_taxonomies( $post, $gmw = array() ) {

	if ( ! isset( $gmw['search_results']['taxonomies'] ) || '' === $gmw['search_results']['taxonomies'] ) {
		return;
	}

	$args = array(
		'id' => $gmw['ID'],
	);

	echo '<div class="taxonomies-list-wrapper">' . gmw_get_post_taxonomies_terms_list( $post, $args ) . '</div>'; // WPCS: XSS ok.
}

/**
 * Display excerpt in search results
 *
 * @param  object $post post object.
 *
 * @param  array  $gmw  gmw form.
 *
 * @return [type]       [description]
 */
function gmw_search_results_post_excerpt( $post, $gmw = array() ) {

	if ( empty( $gmw['search_results']['excerpt']['enabled'] ) ) {
		return;
	}

	// verify usage value.
	$usage = isset( $gmw['search_results']['excerpt']['usage'] ) ? $gmw['search_results']['excerpt']['usage'] : 'post_content';

	if ( empty( $post->$usage ) ) {
		return;
	}

	$args = array(
		'id'                 => $gmw['ID'],
		'content'            => $post->$usage,
		'words_count'        => isset( $gmw['search_results']['excerpt']['count'] ) ? $gmw['search_results']['excerpt']['count'] : '',
		'link'               => get_the_permalink( $post->ID ),
		'link_text'          => isset( $gmw['search_results']['excerpt']['link'] ) ? $gmw['search_results']['excerpt']['link'] : '',
		'enable_shortcodes'  => 1,
		'the_content_filter' => 1,
	);

	$excerpt = GMW_Template_Functions_Helper::get_excerpt( $args );

	echo apply_filters( 'gmw_search_results_post_excerpt_output', '<div class="gmw-excerpt excerpt">' . $excerpt . '</div>', $excerpt, $args, $post, $gmw ); // WPCS: XSS ok.
}
