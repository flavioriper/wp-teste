<?php
/**
 * Common functions
 *
 * @author  YITH
 * @package YITH Infinite Scrolling
 * @version 1.0.0
 */

defined( 'YITH_INFS' ) || exit;  // Exit if accessed directly.

if ( ! function_exists( 'yinfs_get_option' ) ) {
	/**
	 * Get plugin options
	 *
	 * @since  1.0.6
	 * @author Francesco Licandro
	 * @param string $option The requested option key.
	 * @param mixed  $default The default value.
	 * @return mixed
	 */
	function yinfs_get_option( $option, $default = false ) {
		// Get all options.
		$options = get_option( YITH_INFS_OPTION_NAME );

		if ( isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}

		return $default;
	}
}
