<?php
/**
 * subscription feature is ready notice.
 *
 * @package WooAsaas
 */

use WC_Asaas\Helper\Subscriptions_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$subscriptions_helper = new Subscriptions_Helper();
?>
<div class="subscription-feature-is-ready">
	<p>
		<?php esc_html_e('The plugin is able to process subscription payments.', 'woo-asaas'); ?>
	</p>
	<code>
		<span class="dashicons-before dashicons-warning"></span>
		<?php
			/* translators: %s: billing cycles */
			printf( esc_html__('We currently support the following billing cycles: %s.', 'woo-asaas'), $subscriptions_helper->get_supported_billing_periods_string());
		?>
	</code>
</div>