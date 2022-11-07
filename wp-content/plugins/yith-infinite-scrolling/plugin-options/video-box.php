<?php
/**
 * Video box settings array
 *
 * @author  YITH
 * @package YITH Infinite Scrolling
 * @version 1.0.0
 */

defined( 'YITH_INFS' ) || exit; // Exit if accessed directly.

return array(
	'name'    => __( 'Upgrade to the PREMIUM VERSION', 'yith-infinite-scrolling' ),
	'type'    => 'videobox',
	'default' => array(
		'plugin_name'               => __( 'YITH Infinite Scrolling', 'yith-infinite-scrolling' ),
		'title_first_column'        => __( 'Discover the Advanced Features', 'yith-infinite-scrolling' ),
		'description_first_column'  => __( 'Upgrade to the PREMIUM VERSION of YITH Infinite Scrolling to benefit from all features!', 'yith-infinite-scrolling' ),
		'video'                     => array(
			'video_id'          => '122518813',
			'video_image_url'   => YITH_INFS_ASSETS_URL . '/images/video-yith-infinite-scrolling.jpg',
			'video_description' => __( 'YITH Infinite Scrolling', 'yith-infinite-scrolling' ),
		),
		'title_second_column'       => __( 'Get Support and Pro Features', 'yith-infinite-scrolling' ),
		'description_second_column' => __( 'By purchasing the premium version of the plugin, you will take advantage of the advanced features of the product and you will get one year of free updates and support through our platform available 24h/24.', 'yith-infinite-scrolling' ),
		'button'                    => array(
			'href'  => YITH_INFS_Admin()->get_premium_landing_uri(),
			'title' => 'Get Support and Pro Features',
		),
	),
	'id'      => 'yith_infs_general_videobox',
);
