<?php
/**
 * Credit card status after checkout
 *
 * @package WooAsaas
 */

use WC_Asaas\Helper\Checkout_Helper;

$checkout_helper = new Checkout_Helper();
$data            = $order->get_meta_data();

?>
<section class="woocommerce-order-details">
	<h2 class="woocommerce-order-details__title"><?php esc_html_e( 'Payment details', 'woo-asaas' ); ?></h2>

	<ul class="order_details">
		<li>
			<?php
				/* translators: %s: the order status  */
				echo wp_kses_post( sprintf( __( 'Status: <strong>%s</strong>', 'woo-asaas' ), $checkout_helper->convert_status( $data->status ) ) );
			?>
		</li>
	</ul>
</section>
