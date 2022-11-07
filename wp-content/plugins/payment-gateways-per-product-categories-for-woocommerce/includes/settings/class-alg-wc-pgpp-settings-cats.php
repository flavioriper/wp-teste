<?php
/**
 * Payment Gateways per Products for WooCommerce - Categories Section Settings
 *
 * @version 1.1.0
 * @since   1.1.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PGPP_Settings_Cats' ) ) :

class Alg_WC_PGPP_Settings_Cats extends Alg_WC_PGPP_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	function __construct() {
		$this->id   = 'cats';
		$this->desc = __( 'Per Product Categories', 'payment-gateways-per-product-categories-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	function get_settings() {
		return array_merge( array(
			array(
				'title'    => __( 'Per Product Categories', 'payment-gateways-per-product-categories-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pgpp_categories_options',
			),
			array(
				'title'    => __( 'Enable/Disable', 'payment-gateways-per-product-categories-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable section', 'payment-gateways-per-product-categories-for-woocommerce' ) . '</strong>',
				'id'       => 'alg_wc_pgpp_product_cat_section_enabled',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pgpp_categories_options',
			),
		), parent::get_gateways_settings( array(
			'options'      => $this->get_terms( 'product_cat' ),
			'options_id'   => 'categories',
			'desc_tips'    => array(
				'include' => __( 'Show gateway only if there is product of selected category in cart.', 'payment-gateways-per-product-categories-for-woocommerce' ),
				'exclude' => __( 'Hide gateway if there is product of selected category in cart.', 'payment-gateways-per-product-categories-for-woocommerce' ),
			),
		) ) );
	}

}

endif;

return new Alg_WC_PGPP_Settings_Cats();
