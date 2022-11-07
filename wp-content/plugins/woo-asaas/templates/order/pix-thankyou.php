<?php
/**
 * Pix print link
 *
 * @package WooAsaas
 */

use WC_Asaas\Helper\Checkout_Helper;
use WC_Asaas\WC_Asaas;

$checkout_helper = new Checkout_Helper();
$data            = $order->get_meta_data();

?>
<section class="woocommerce-order-details">
	<h2 class="woocommerce-order-details__title"><?php esc_html_e( 'Payment details', 'woo-asaas' ); ?></h2>

	<ul class="order_details">
		<li>
			<?php esc_html_e( 'Pay with Pix.', 'woo-asaas' ); ?>
		</li>
		<li class="asaas-pix-instructions">
			<img class="js-pix-qr-code" height="250px" width="250px" src="data:image/jpeg;base64, <?php echo esc_attr( $data->encodedImage ); ?>" alt="QR Code Pix">
			
			<?php WC_Asaas::get_instance()->get_template_file( 'order/pix-thankyou-instructions.php', array( 'show_copy_and_paste' => $show_copy_and_paste, 'expiration_time' => $expiration_time, 'expiration_period' => $expiration_period ) ); ?>
		</li>
		<?php if ( true === $show_copy_and_paste ) : ?>
		<li>
			<div>
				<p class="woocommerce-order-details__asaas-pix-payload"><?php echo esc_attr( $data->payload ); ?></p>
				<button class="button woocommerce-order-details__asaas-pix-button" data-success-copy="<?php esc_html_e( 'Code copied to clipboard', 'woo-asaas' ); ?>">
					<?php esc_html_e( 'Click here to copy the Pix code', 'woo-asaas' ); ?>
				</button>
			</div>
		</li>
		<?php endif; ?>
	</ul>
</section>
