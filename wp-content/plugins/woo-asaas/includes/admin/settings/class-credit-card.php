<?php
/**
 * Credit Card settings class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Admin\Settings;

/**
 * Credit Card settings
 */
class Credit_Card extends Settings {

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Asaas\Admin\Settings\Settings::get_fields()
	 */
	public function get_fields() {
		$fields                           = parent::get_fields();
		$fields['description']['default'] = __( 'Pay your purchase with credit card.', 'woo-asaas' );
		
		return apply_filters(
			'woocommerce_asaas_cc_settings_fields',
			array_merge(
				$fields,
				array(
					'min_total'             => array(
						'title'       => __( 'Order minimum total', 'woo-asaas' ),
						'type'        => 'text',
						'description' => __( 'The order minimum total to allow use this payment method. Use <code>0</code> to disable this option.', 'woo-asaas' ),
						'default'     => '0',
						'section'     => 'gateway',
						'priority'    => 10,
					),
					'one_click_buy'         => array(
						'title'       => __( 'One-Click Buy', 'woo-asaas' ),
						'type'        => 'checkbox',
						'label'       => __( 'Enable one-click buy', 'woo-asaas' ),
						'description' => __( 'To enable one-click buy please contact your Asaas manager.', 'woo-asaas' ),
						'default'     => 'no',
						'shared'      => true,
						'section'     => 'advanced',
						'priority'    => 20,
					),
				)
			),
			$this
		);
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \WC_Asaas\Admin\Settings\Settings::get_sections()
	 */
	public function get_sections() {
		$sections                     = parent::get_sections();
		$sections['gateway']['title'] = __( 'Credit Card', 'woo-asaas' );
		return apply_filters( 'woocommerce_asaas_cc_settings_sections', $sections );
	}
}
