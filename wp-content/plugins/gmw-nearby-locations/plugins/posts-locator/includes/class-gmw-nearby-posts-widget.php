<?php
/**
 * Nearby Posts widget.
 *
 * @package gmw-nearby-locations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GEO my WP Nearby Posts Widget
 *
 * @since 2.0.0
 */
class GMW_Nearby_Posts_Widget extends GMW_Widget {

	/**
	 * Widget ID
	 *
	 * @var string
	 */
	public $widget_id = 'gmw_nearby_posts_widget';

	/**
	 * Widget class
	 *
	 * @var string
	 */
	public $widget_class = 'geo-my-wp widget-nearby-posts';

	/**
	 * Help page link
	 *
	 * @var string
	 */
	//public $help_link = 'http://docs.geomywp.com/single-location-widget/';

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->widget_description = __( 'Display nearby posts.', 'gmw-nearby-locations' );
		$this->widget_name        = __( 'GEO my WP Nearby Posts', 'gmw-nearby-locations' );
		$this->settings           = gmw_nbl_get_widget_settings();

		// Widget settings.
		$settings = array(
			'post_types'    => array(
				'type'        => 'multicheckbox',
				'default'     => array( 'post' ),
				'label'       => __( 'Post types', 'gmw-nearby-locations' ),
				'options'     => array(),
				'description' => __( 'Choose of the post types that you would like to include.', 'gmw-nearby-locations' ),
			),
			'include_terms' => array(
				'type'        => 'text',
				'default'     => '',
				'label'       => __( 'Include terms', 'gmw-nearby-locations' ),
				'description' => __( 'Filter posts by including taxonomy terms ID. Enter terms ID, comma separated, of any existing taxonomy.', 'gmw-nearby-locations' ),
			),
			'exclude_terms' => array(
				'type'        => 'text',
				'default'     => '',
				'label'       => __( 'Exclude terms', 'gmw-nearby-locations' ),
				'description' => __( 'Filter posts by excluding taxonomy terms ID. Enter terms ID, comma separated, of any existing taxonomy.', 'gmw-nearby-locations' ),
			),
		);

		$this->settings = array_merge( array_slice( $this->settings, 0, 2 ), $settings, array_slice( $this->settings, 2 ) );

		$this->settings['orderby'] = array(
			'type'    => 'select',
			'default' => 'distance',
			'label'   => __( 'Order by', 'gmw-nearby-locations' ),
			'options' => array(
				'distance'   => __( 'Distance', 'gmw-nearby-locations' ),
				'post_title' => __( 'Post title', 'gmw-nearby-locations' ),
				'ID'         => __( 'Post ID', 'gmw-nearby-locations' ),
			),
		);

		$this->register();
	}

	/**
	 * Form.
	 *
	 * We use this function istead of the parent function to be able to
	 *
	 * get all custom post types, which not yet exists in the __construct() function.
	 *
	 * @param  [type] $instance [description].
	 */
	public function form( $instance ) {

		global $wp_post_types;

		$post_types = array();

		foreach ( $wp_post_types as $post_type ) {
			$post_types[ $post_type->name ] = $post_type->label;
		}

		$this->settings['post_types']['options'] = $post_types;

		parent::form( $instance );
	}

	/**
	 * Update widget settings.
	 *
	 * We use this function istead of the parent function to be able to
	 *
	 * get all custom post types, which not yet exists in the __construct() function.
	 *
	 * @param  [type] $new_instance [description].
	 *
	 * @param  [type] $old_instance [description].
	 *
	 * @return [type]               [description]
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		global $wp_post_types;

		$post_types = array();

		foreach ( $wp_post_types as $post_type ) {
			$post_types[ $post_type->name ] = $post_type->label;
		}

		$this->settings['post_types']['options'] = $post_types;

		if ( ! $this->settings ) {
			return $instance;
		}

		foreach ( $this->settings as $key => $setting ) {

			if ( ! isset( $new_instance[ $key ] ) ) {
				$new_instance[ $key ] = ( 'checkbox' !== $setting['type'] ) ? $setting['default'] : '';
			}

			if ( ! is_array( $new_instance[ $key ] ) ) {
				$instance[ $key ] = sanitize_text_field( $new_instance[ $key ] );
			} else {
				$instance[ $key ] = array_map( 'sanitize_text_field', $new_instance[ $key ] );
			}
		}

		return $instance;
	}

	/**
	 * Echoes the widget content.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args widget arguments.
	 *
	 * @param array $instance instance.
	 */
	public function widget( $args, $instance ) {

		extract( $args );

		echo $before_widget; // WPCS: XSS ok.

		$instance['address_fields'] = ! empty( $instance['address_fields'] ) ? implode( ',', $instance['address_fields'] ) : '';
		$instance['post_types']     = ! empty( $instance['post_types'] ) ? implode( ',', $instance['post_types'] ) : '';
		$instance['widget_title']   = ! empty( $instance['widget_title'] ) ? htmlentities( $args['before_title'] . $instance['widget_title'] . $args['after_title'], ENT_QUOTES ) : 0;

		$output_string = '';

		$nearby_posts = new GMW_Nearby_Posts( $instance );

		$nearby_posts->display();

		echo $after_widget; // WPCS: XSS ok.
	}
}
register_widget( 'GMW_Nearby_Posts_Widget' );
