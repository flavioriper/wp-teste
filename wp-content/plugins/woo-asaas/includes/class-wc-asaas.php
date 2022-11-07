<?php
/**
 * Plugin main class
 *
 * @package WooAsaas
 */

namespace WC_Asaas;

use WC_Asaas\Admin\Plugin_Dependency;
use WC_Asaas\Webhook\Endpoint;
use WC_Asaas\Gateway\Gateway;
use WC_Asaas\Checkout\Form_Field\Card_Expiration;
use WC_Asaas\Checkout\Form_Field\Label;
use WC_Asaas\Checkout\Form_Field\Card;
use WC_Asaas\Checkout\Form_Field\One_Click_Options;
use WC_Asaas\Checkout\Form_Field\Card_Number;
use WC_Asaas\Checkout\Form_Field\Card_Security_Code;
use WC_Asaas\Billing_Type\Billing_Type_Exception;
use WC_Asaas\Cron\Expired_Pix_Cron;
use WC_Asaas\Cron\Expired_Ticket_Cron;
use WC_Asaas\Installments\Admin\Settings\Installments_Fields;
use WC_Asaas\Installments\Gateway\Checkout_Installments;
use WC_Asaas\Installments\Gateway\Payment_Installments;
use WC_Asaas\Product\Admin\Settings\Product_Settings;
use WC_Asaas\Cart\Cart;
use WC_Asaas\Coupon\Coupon;
use WC_Asaas\Subscription\Subscription;
use WC_Asaas\Subscription\Admin\Settings\WooCommerce_Subscriptions_Settings;
use WC_Asaas\Subscription\Admin\Subscription_Admin;
use WC_Asaas\My_Account\WooCommerce_My_Account;
use WC_Asaas\Checkout\Checkout;

/**
 * Asaas Gateway for WooCommerce main class
 */
class WC_Asaas {

	/**
	 * WooCommerce version.
	 *
	 * @var string
	 */
	public $version = '2.0.1';

	/**
	 * Instance of this class
	 *
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Gateway list with id
	 *
	 * @var Gateway[string]
	 */
	protected $gateways;

	/**
	 * Initialize the plugin public actions
	 *
	 * Block external object instantiation.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		$dependency = Plugin_Dependency::get_instance();
		add_action( 'admin_init', array( $dependency, 'check_dependencies' ) );

		if ( ! $dependency->check_woocommerce() ) {
			return;
		}

		add_action( 'init', array( $this, 'init_gateways' ) );
		add_action( 'init', array( Endpoint::get_instance(), 'custom_rewrite_basic' ) );
		add_action( 'init', array( $this, 'init_form_fields' ) );
		add_action( 'init', array( Expired_Ticket_Cron::get_instance(), 'schedule_remove_expired_ticket' ) );
		add_action( 'woocommerce_new_order', array( Expired_Pix_Cron::get_instance(), 'schedule_remove_expired_pix' ) );
		add_action( 'remove_expired_ticket', array( Expired_Ticket_Cron::get_instance(), 'remove_expired_ticket' ) );
		add_action( 'remove_expired_pix_asaas', array( Expired_Pix_Cron::get_instance(), 'remove_expired_pix' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_files' ) );
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateways' ) );
		add_filter( 'woocommerce_asaas_payment_data', array( Payment_Installments::get_instance(), 'installment_payment_data' ), 10, 3 );
		add_filter( 'woocommerce_asaas_ticket_payment_fields', array( Checkout_Installments::get_instance(), 'add_ticket_installment_field' ), 10, 2 );
		add_filter( 'woocommerce_asaas_cc_payment_fields', array( Checkout_Installments::get_instance(), 'add_cc_installment_field' ), 10, 2 );
		add_filter( 'woocommerce_asaas_ticket_settings_fields', array( Installments_Fields::get_instance(), 'add_installments_fields' ), 10, 2 );
		add_filter( 'woocommerce_asaas_cc_settings_fields', array( Installments_Fields::get_instance(), 'add_installments_fields' ), 10, 2 );

		add_filter( 'wcs_is_early_renewal_enabled', array( WooCommerce_Subscriptions_Settings::get_instance(), 'disable_early_renewal' ), 10 );
		add_filter( 'wcs_is_early_renewal_via_modal_enabled', array( WooCommerce_Subscriptions_Settings::get_instance(), 'disable_early_renewal' ), 10 );
		add_filter( 'woocommerce_subscription_settings', array( WooCommerce_Subscriptions_Settings::get_instance(), 'show_notice_unsupport_early_renewal' ), 20 );
		add_filter( 'woocommerce_subscription_settings', array( WooCommerce_Subscriptions_Settings::get_instance(), 'show_notice_unsupport_manual_renewal' ), 20, 1 );
		add_filter( 'woocommerce_asaas_settings_sections', array( WooCommerce_Subscriptions_Settings::get_instance(), 'show_notice_unsupport_manual_renewal' ), 10, 2 );
		add_filter( 'woocommerce_subscription_settings', array( WooCommerce_Subscriptions_Settings::get_instance(), 'show_notice_unsupport_auto_renewal_toggle' ), 20, 1 );
		add_filter( 'woocommerce_asaas_settings_sections', array( WooCommerce_Subscriptions_Settings::get_instance(), 'show_notice_unsupport_auto_renewal_toggle' ), 10, 2 );

		add_action( 'pre_post_update', array( Subscription_Admin::get_instance(), 'validate_subscription_changes' ), 10, 2 );

		add_filter( 'woocommerce_asaas_settings_sections', array( $dependency, 'subscription_dependencies_notice' ), 10, 1 );
		add_action( 'woocommerce_product_options_general_product_data', array( Product_Settings::get_instance(), 'show_tip_supported_billing_cycles' ), 5 );
		add_filter( 'woocommerce_available_payment_gateways', array( Cart::get_instance(), 'check_available_payment_gateways' ), 10, 1 );
		add_action( 'woocommerce_coupon_options', array( Coupon::get_instance(), 'show_notice_about_supported_coupon_types' ), 10 );

		add_filter( 'woocommerce_can_subscription_be_updated_to_active', array( Subscription::get_instance(), 'can_subscription_be_updated_to_active' ), 10, 2 );
		add_action( 'woocommerce_subscription_status_changed', array( Subscription::get_instance(), 'sync_status' ), 10, 4 );

		add_action( 'woocommerce_checkout_order_created', array( Checkout::get_instance(), 'handle_woocommerce_subscriptions_checkout_usage' ), 10, 1 );
		add_filter( 'woocommerce_subscriptions_synced_first_payment_date_string', array( Checkout::get_instance(), 'hide_first_payment_date_string' ), 10, 2 );
		remove_filter( 'wcs_cart_totals_order_total_html', 'wcs_add_cart_first_renewal_payment_date', 10 );

		add_filter( 'woocommerce_my_account_my_orders_actions', array( WooCommerce_My_Account::get_instance(), 'my_orders_actions' ), 10, 2 );

		add_action( 'admin_notices', array( $this, 'check_checkout_settings' ) );
	}

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
	 * Get the plugin absolute path
	 *
	 * @return string The plugin absolute path.
	 */
	public function get_plugin_path() {
		return plugin_dir_path( dirname( __FILE__ ) );
	}

	/**
	 * Get the plugin URL
	 *
	 * @return string The plugin URL.
	 */
	public function get_plugin_url() {
		return plugin_dir_url( dirname( __FILE__ ) );
	}

	/**
	 * Get templates path
	 */
	public function get_templates_path() {
		return $this->get_plugin_path() . 'templates/';
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
				self::get_instance()->get_templates_path()
			);
		}

		wc_get_template(
			$template_name,
			$args,
			'woocommerce/asaas/',
			self::get_instance()->get_templates_path()
		);
	}

	/**
	 * Get plugin assets URL
	 */
	public function get_assets_url() {
		return $this->get_plugin_url() . 'assets/';
	}

	/**
	 * Init Asaas gateways together with WooCommerce to process the inner hooks correctly
	 */
	public function init_gateways() {
		foreach ( $this->get_gateways_classes() as $gateway ) {
			$reflection                         = new \ReflectionClass( $gateway );
			$gateway_obj                        = $reflection->newInstanceArgs();
			$this->gateways[ $gateway_obj->id ] = $gateway_obj;
		}
	}

	/**
	 * Load plugin textdomain file
	 */
	public function load_plugin_textdomain() {
		$domain                 = 'woo-asaas';
		$languages_rel_dir_path = '/../languages/';
		load_textdomain( $domain, __DIR__ . $languages_rel_dir_path . '/' . $domain . '-' . get_locale() . '.mo' );
	}

	/**
	 * Get plugin gateway classes
	 *
	 * @return string[] The fully qualified gateways classes name
	 */
	public function get_gateways_classes() {
		return apply_filters(
			'woocommerce_asaas_getways_classes', array(
				\WC_Asaas\Gateway\Ticket::class,
				\WC_Asaas\Gateway\Credit_Card::class,
				\WC_Asaas\Gateway\Pix::class,
			)
		);
	}

	/**
	 * Get plugin gateways
	 *
	 * @return Gateway[string] The plugin gateways associated with its id.
	 */
	public function get_gateways() {
		return apply_filters( 'woocommerce_asaas_getways', $this->gateways );
	}

	/**
	 * Get gateway by id
	 *
	 * @param  string $id id.
	 *
	 * @return Gateway return order gateway
	 */
	public function get_gateway_by_id( $id ) {
		return $this->gateways[ $id ];
	}

	/**
	 * Get gateway by billing type
	 *
	 * @param string $billing_type Billing type id on Asaas API.
	 * @throws Billing_Type_Exception If the type wasn't registered by a gateway.
	 * @return Gateway|NULL The gateway, if id is valid. Otherwise, null.
	 */
	public function get_gateway_by_billing_type( $billing_type ) {
		foreach ( $this->gateways as $gateway ) {
			if ( $billing_type === $gateway->get_type()->get_id() ) {
				return $gateway;
			}
		}

		/* translators: %s: billing type name  */
		throw new Billing_Type_Exception( sprintf( __( 'Billing type %s wasn\'t registered.', 'woo-asaas' ), $billing_type ) );
	}

	/**
	 * Add plugin ticket, credit and Pix card gateways to WooCommerce
	 *
	 * @param string[] $methods WooCommerce available gateways.
	 * @return string[] Gateways including Asaas ones.
	 */
	public function register_gateways( $methods ) {
		return array_merge( $methods, array_values( $this->gateways ) );
	}

	/**
	 * Init the form fields
	 *
	 * @see self::get_form_fields()
	 */
	public function init_form_fields() {
		$this->get_form_fields();
	}

	/**
	 * Get the custom checkout form fields
	 *
	 * @return \WC_Asaas\Checkout\Form_Field\Form_Field[]
	 */
	public function get_form_fields() {
		return array(
			Card::get_instance(),
			Card_Expiration::get_instance(),
			Card_Number::get_instance(),
			Card_Security_Code::get_instance(),
			Label::get_instance(),
			One_Click_Options::get_instance(),
		);
	}

	/**
	 * Get the custom checkout form field from object type
	 *
	 * @param string $type The type identification.
	 * @return \WC_Asaas\Checkout\Form_Field\Form_Field|NULL The correspondent object of the type. Null, if not found it.
	 */
	public function get_form_field_object_from_type( $type ) {
		foreach ( $this->get_form_fields() as $field_type ) {
			if ( $type === $field_type->get_type() ) {
				return $field_type;
			}
		}

		return null;
	}

	/**
	 * Check checkout settings for 'woocommerce-extra-checkout-fields-for-brazil' plugin
	 */
	public function check_checkout_settings() {
		$settings = get_option( 'wcbcf_settings' );

		if ( is_array( $settings ) ) {
			if ( $settings['person_type'] == '0' ) {
				$error = __( 'Asaas needs the CPF or CNPJ at checkout for the integration works.', 'woo-asaas' );
				$message = $error;

				if ( 'woocommerce-extra-checkout-fields-for-brazil' != $_GET['page'] ) {
					$message .= ' ' . sprintf( __( '<a href="%s">Click here</a> to change checkout fields.', 'woo-asaas' ), admin_url( 'admin.php?page=woocommerce-extra-checkout-fields-for-brazil' ) );
				}

				echo '<div class="error"><p>' . $message . '</p></div>';
			}
		}
	}

	/**
	 * Enqueue plugin style file
	 */
	public function enqueue_frontend_files() {
		wp_enqueue_style( 'woo-asaas', $this->get_plugin_url() . 'assets/woo-asaas.css', array(), $this->version );
		wp_enqueue_script( 'woo-asaas', $this->get_plugin_url() . 'assets/woo-asaas.js', array(), $this->version, true );
	}
}
