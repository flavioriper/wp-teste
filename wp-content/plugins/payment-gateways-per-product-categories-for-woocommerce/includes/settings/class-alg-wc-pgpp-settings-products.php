<?php
/**
 * Payment Gateways per Products for WooCommerce - Products Section Settings
 *
 * @version 1.1.0
 * @since   1.1.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PGPP_Settings_Products' ) ) :

class Alg_WC_PGPP_Settings_Products extends Alg_WC_PGPP_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	function __construct() {
		$this->id   = 'products';
		$this->desc = __( 'Per Products', 'payment-gateways-per-product-categories-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 * @todo    [dev] "Add variations": maybe add option to use main product and variations *simultaneously*
	 */
	function get_settings() {
		return array_merge( array(
			array(
				'title'    => __( 'Per Products', 'payment-gateways-per-product-categories-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pgpp_products_options',
			),
			array(
				'title'    => __( 'Enable/Disable', 'payment-gateways-per-product-categories-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable section', 'payment-gateways-per-product-categories-for-woocommerce' ) . '</strong>',
				'desc_tip' => apply_filters( 'alg_wc_pgpp', sprintf(
					'To enable this section you need <a href="%s" target="_blank">Payment Gateways per Products for WooCommerce Pro</a> plugin.',
					'https://wpfactory.com/item/payment-gateways-per-product-for-woocommerce/' ), 'settings' ),
				'id'       => 'alg_wc_pgpp_products_section_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_pgpp', array( 'disabled' => 'disabled' ), 'settings' ),
			),
			array(
				'title'    => __( 'Add variations', 'payment-gateways-per-product-categories-for-woocommerce' ),
				'desc'     => __( 'Add', 'payment-gateways-per-product-categories-for-woocommerce' ),
				'desc_tip' => __( 'Will use variations instead of main product for variable products.', 'payment-gateways-per-product-categories-for-woocommerce' ),
				'id'       => 'alg_wc_pgpp_products_add_variations',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pgpp_products_options',
			),
		), parent::get_gateways_settings( array(
			'options'      => $this->get_products( array(), 'any', 512, ( 'yes' === get_option( 'alg_wc_pgpp_products_add_variations', 'no' ) ) ),
			'options_id'   => 'products',
			'desc_tips'    => array(
				'include' => __( 'Show gateway only if there are selected products in cart.', 'payment-gateways-per-product-categories-for-woocommerce' ),
				'exclude' => __( 'Hide gateway if there are selected products in cart.', 'payment-gateways-per-product-categories-for-woocommerce' ),
			),
		) ) );
	}

}

endif;

return new Alg_WC_PGPP_Settings_Products();
