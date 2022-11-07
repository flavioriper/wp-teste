<?php
/**
 * Premium tab settings array
 *
 * @author  YITH
 * @package YITH Infinite Scrolling
 * @version 1.0.0
 */

defined( 'YITH_INFS' ) || exit; // Exit if accessed directly.

return array(
	'premium' => array(
		'home' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_infinite_scrolling_premium',
		),
	),
);
