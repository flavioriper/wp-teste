<?php
/**
 * WC Interest Order Item Fee class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Installments\Checkout;

use WC_Asaas\Installments\Helper\Installments_Calculator_Helper;
use WC_Asaas\Installments\Helper\Values_Formater_Helper;
use WC_Order_Item_Fee;

/**
 * Handle order fee installments.
 */
class WC_Interest_Order_Item_Fee extends WC_Order_Item_Fee {

	/**
	 * Order total.
	 *
	 * @var float
	 */
	private $order_total;

	/**
	 * Number of order installments.
	 *
	 * @var int
	 */
	private $installments;

	/**
	 * Interest value.
	 *
	 * @var float
	 */
	private $interest;

	/**
	 * Constructor.
	 *
	 * @param float $order_total The order amount.
	 * @param int $installments Number of order installments.
	 * @param float $interest Interest value.
	 */
	public function __construct( float $order_total, int $installments, float $interest ) {
        parent::__construct();

		$this->order_total  = $order_total;
        $this->installments = $installments;
        $this->interest     = $interest;
	}

    /**
     * Create a new Order Item Fee.
     *
	 * @return void
	 */
	public function new_item_fee() : void {
        $order_value_fee = $this->get_fee_amount();
        $fee_name        = $this->get_fee_name();

        $this->set_name( $fee_name );
        $this->set_amount( $order_value_fee );
        $this->set_tax_class( '' );
        $this->set_tax_status( 'taxable' );
        $this->set_total( $order_value_fee );
    }

    /**
     * Get order interest value.
     *
     * @return float
     */
	private function get_fee_amount() : float {
        $interest_value = ( new Installments_Calculator_Helper )->get_interest_value( $this->order_total, $this->interest );

        return $interest_value;
    }

    /**
     * Creates the item fee name.
     *
     * @return string
     */
	public function get_fee_name() : string {
        $fee_name = sprintf(
            __( 'Interest (%s%% per installment)', 'woo-asaas' ) . ' - ' .  _n( '%d installment', '%d installments', $this->installments, 'woo-asaas' ),
            ( new Values_Formater_Helper() )->replace_dot_with_comma( $this->interest ),
            $this->installments
        );

        return $fee_name;
    }
}
