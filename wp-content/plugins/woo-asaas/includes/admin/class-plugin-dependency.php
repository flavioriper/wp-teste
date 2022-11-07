<?php
/**
 * Plugin dependency class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Admin;

/**
 * WooCommerce Asaas
 */
class Plugin_Dependency {

	/**
	 * Instance of this class
	 *
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * The list of plugin dependencies
	 *
	 * @var array
	 */
	protected $dependencies;

	/**
	 * The list of plugin dependencies for subscription feature
	 *
	 * @var array
	 */
	protected $subscription_dependencies;	

	/**
	 * Define the dependencies
	 *
	 * Block external object instantiation.
	 */
	private function __construct() {
		$this->dependencies = apply_filters(
			'woocommerce_asaas_plugin_dependencies', array(
				'woocommerce' => array(
					'name'            => 'WooCommerce',
					'plugin_file'     => 'woocommerce/woocommerce.php',
				),
				'woocommerce-extra-checkout-fields-for-brazil' => array(
					'name'            => 'WooCommerce Extra Checkout Fields For Brazil',
					'plugin_file'     => 'woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php',
				),
			)
		);

		$this->subscription_dependencies = apply_filters(
			'woocommerce_asaas_plugin_subscription_dependencies', array(
				'woocommerce-subscriptions' => array(
					'name'            => 'WooCommerce Subscriptions',
					'plugin_file'     => 'woocommerce-subscriptions/woocommerce-subscriptions.php',
					'notice_callback' => array( $this, 'woocommerce_subscriptions_missing_notice' ),
					'min_version'     => '4.4',
				),
			)
		);
	}

	/**
	 * Return an instance of this class
	 *
	 * @return self A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get the dependencies
	 *
	 * @return array The dependencies
	 */
	public function get_dependencies() {
		return $this->dependencies;
	}

	/**
	 * Verify if the WordPress installation satisfies the plugin requirements
	 *
	 * If not, call the function to show the missing plugins in the admin.
	 */
	public function check_dependencies() {
		foreach ( $this->dependencies as $dependency ) {
			if ( ! is_plugin_active( $dependency['plugin_file'] ) ) {
				add_action( 'admin_notices', array( $this, 'woocommerce_dependencies_notice' ) );
			}
		}
	}

	/**
	 * Check if the WooCommerce plugin is active by validating that its main class has been defined.
	 * 
	 * @return boolean True, if the class exists. False, otherwise.
	 */
	public function check_woocommerce() {
		return class_exists( $this->dependencies['woocommerce']['name'] );
	}

	/**
	 * Diplay missing dependencies template
	 */
	public function woocommerce_dependencies_notice() {
		View::get_instance()->load_template_file( 'missing-dependency-plugin.php' );
	}

	/**
	 * Get the subscription dependencies
	 *
	 * @return array The subscription dependencies
	 */
	public function get_subscription_dependencies() {
		return $this->subscription_dependencies;
	}	

	/**
	 * Diplay missing subscription dependencies template
	 */
	public function subscription_dependencies_notice(array $sections) {
		ob_start();
		View::get_instance()->load_template_file( 'missing-subscription-dependency-plugin.php' );
		$message = ob_get_contents();
		ob_end_clean();
		$sections['subscriptions']['description'] = $message;

		return $sections;
	}
}
