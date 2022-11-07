<?php
/**
 * Payment Gateways per Products for WooCommerce - Tags Section Settings
 *
 * @version 1.1.0
 * @since   1.1.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PGPP_Settings_Tags' ) ) :

class Alg_WC_PGPP_Settings_Tags extends Alg_WC_PGPP_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	function __construct() {
		$this->id   = 'tags';
		$this->desc = __( 'Per Product Tags', 'payment-gateways-per-product-categories-for-woocommerce' );
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
				'title'    => __( 'Per Product Tags', 'payment-gateways-per-product-categories-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pgpp_tags_options',
			),
			array(
				'title'    => __( 'Enable/Disable', 'payment-gateways-per-product-categories-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable section', 'payment-gateways-per-product-categories-for-woocommerce' ) . '</strong>',
				'id'       => 'alg_wc_pgpp_product_tag_section_enabled',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pgpp_tags_options',
			),
		), parent::get_gateways_settings( array(
			'options'      => $this->get_terms( 'product_tag' ),
			'options_id'   => 'tags',
			'desc_tips'    => array(
				'include' => __( 'Show gateway only if there is product of selected tag in cart.', 'payment-gateways-per-product-categories-for-woocommerce' ),
				'exclude' => __( 'Hide gateway if there is product of selected tag in cart.', 'payment-gateways-per-product-categories-for-woocommerce' ),
			),
		) ) );
	}

}

endif;

return new Alg_WC_PGPP_Settings_Tags();
