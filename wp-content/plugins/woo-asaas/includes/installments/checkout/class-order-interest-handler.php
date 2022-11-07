<?php
/**
 * Order Interest Handler class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Installments\Checkout;

use Exception;
use WC_Order;
use WC_Order_Item_Fee;

/**
 * Handle installments insterest.
 */
class Order_Interest_Handler {

	/**
	 * Current order.
	 *
	 * @var WC_Order
	 */
	private $wc_order;

	/**
	 * Constructor.
	 *
	 * @param WC_Order $wc_order The current order.
	 */
	public function __construct( WC_Order $wc_order ) {
		$this->wc_order = $wc_order;
	}

	/**
	 * Add interest on the installments if the order meets the requirements.
	 * 
	 * @param int $installments Number of installments.
	 * @param array $interest Interest installments values.
	 */
	public function add_interest_on_order( int $installments, array $interest_installment ) {
		$interest    = $interest_installment[ $installments ];
		$order_total = $this->wc_order->get_total();

		$order_item_fee = new WC_Interest_Order_Item_Fee( $order_total, $installments, $interest );
        $order_item_fee->new_item_fee();

		$this->add_item_fee_to_order( $order_item_fee );
	}

	/**
     * Add the interest amount as item fee to the order.
	 * 
	 * @param WC_Order_Item_Fee $order_item_fee Order Item Fee object.
	 * @throws \Exception If fail to add order item fee.
	 * @return void
	 */
	private function add_item_fee_to_order( WC_Order_Item_Fee $order_item_fee ) : void {
        $wc_order_item = $this->wc_order->add_item( $order_item_fee );
        
        if ( false !== $wc_order_item ) {
            $this->wc_order->calculate_totals();
            return;
        }

        throw new Exception( __( 'Fail to add installment interest fee.', 'woo-asaas' ) );
    }
}