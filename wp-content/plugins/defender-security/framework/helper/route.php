<?php

namespace Calotes\Helper;

/**
 * This is the helper to enqueue ajax route, also generate the nonces.
 *
 * Class Route
 *
 * @package Calotes\Helper
 */
class Route {
	/**
	 * @var array
	 */
	protected static $routes = [];

	/**
	 * @var array
	 */
	protected static $nonces = [];

	/**
	 * @param $name
	 * @param $category
	 * @param $route
	 * @param $callback
	 * @param $nopriv
	 */
	public static function register_route( $name, $category, $route, $callback, $nopriv = false ) {
		$namespace = self::get_namespace( $category );
		$route = $namespace . '/' . $route;
		add_action( 'wp_ajax_' . $route, $callback );
		if ( true === $nopriv ) {
			add_action( 'wp_ajax_nopriv_' . $route, $callback );
		}
		if ( ! isset( self::$routes[ $category ] ) ) {
			self::$routes[ $category ] = [];
			self::$nonces[ $category ] = [];
		}
		self::$routes[ $category ][ $name ] = $route;
		self::$nonces[ $category ][ $name ] = wp_create_nonce( $name . $category );
	}

	/**
	 * @param $categories
	 *
	 * @return array
	 */
	public static function export_routes( $categories ) {
		$routes = self::$routes[ $categories ] ?? [];
		$nonces = self::$nonces[ $categories ] ?? [];

		return [ $routes, $nonces ];
	}

	/**
	 * @return string
	 */
	public static function get_namespace( $module ) {
		return 'wp-defender/v1/' . $module;
	}
}
