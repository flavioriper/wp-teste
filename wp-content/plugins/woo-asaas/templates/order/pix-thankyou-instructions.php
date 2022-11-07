<?php
/**
 * Pix payment instructions
 *
 * @package WooAsaas
 */

if( 'minute' === $expiration_period ) {
    /* translators: %d: expiration time */
    $expiration_period = _n( 'minute', 'minutes', $expiration_time, 'woo-asaas' );
}

if( 'hour' === $expiration_period ) {
    $expiration_period = _n( 'hour', 'hours', $expiration_time, 'woo-asaas' );
}

if( 'day' === $expiration_period ) {
    $expiration_period = _n( 'day', 'days', $expiration_time, 'woo-asaas' );
}
?>

<ol class="asaas-pix-instructions__list">
    <li><?php esc_html_e( 'Open the app or Internet Banking to pay.', 'woo-asaas' ); ?></li>
    <li><?php esc_html_e( 'In the Pix option, choose "Read QR Code".', 'woo-asaas' ); ?></li>
    <?php if ( true === $show_copy_and_paste ) : ?>
        <li>
            <?php echo esc_html_e( 'Scan the QR Code or, if you prefer, copy the code to Pix Copy and Paste.', 'woo-asaas' );?>
        </li>
    <?php endif; ?>
    <li><?php esc_html_e( 'Review the information and confirm payment. Ready! The order status will be updated immediately.', 'woo-asaas' ); ?></li>
    <li>
    <?php 
    echo esc_html(
        sprintf(
            /* translators: 1: expiration time setting 2: expiration period */
            __( 'You have %1$d %2$s to pay. After that time, your order will be cancelled.', 'woo-asaas' ),
            $expiration_time,
            $expiration_period
        )
    ); 
    ?>
    </li>
</ol>