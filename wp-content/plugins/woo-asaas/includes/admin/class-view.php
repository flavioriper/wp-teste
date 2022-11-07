<?php
/**
 * Admin view class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Admin;

/**
 * Admin view class
 */
class View {

	/**
	 * Instance of this class
	 *
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Block external object instantiation
	 */
	private function __construct() {}

	/**
	 * Return an instance of this class
	 *
	 * @return self A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get the admin views directory path
	 */
	public function get_template_path() {
		return __DIR__ . '/views/';
	}

	/**
	 * Load a template file
	 *
	 * @param string $file Template file name.
	 */
	public function load_template_file( $file ) {
		require $this->get_template_path() . $file;
	}

	/**
	 * Get a template file
	 *
	 * A template can be overwritten by the theme. Add the templates file in `woocommerce/asaas` in your theme.
	 *
	 * @param string  $template_name The template file name.
	 * @param array   $args The template variables.
	 * @param boolean $return Return the template HTML if is true. Otherwise, print the template.
	 * @return string
	 */
	public function get_template_file( $template_name, $args = array(), $return = false ) {
		if ( $return ) {
			return wc_get_template_html(
				$template_name,
				$args,
				'woocommerce/asaas/',
				self::get_instance()->get_template_path()
			);
		}

		wc_get_template(
			$template_name,
			$args,
			'woocommerce/asaas/',
			self::get_instance()->get_template_path()
		);
	}
}
