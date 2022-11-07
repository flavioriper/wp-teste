<?php
/**
 * Installments Calculator Helper class
 *
 * @package WooAsaas
 */

namespace WC_Asaas\Installments\Helper;

/**
 * Handle installments calculations.
 */
class Installments_Calculator_Helper {

	/**
	 * Applies interest to an amount.
	 * 
	 * @param float $value          Amount value.
	 * @param float $interest_value Interest percentage value.
	 * @return float
	 */
	public function get_value_with_interest( float $value, float $interest_value ) : float {
		$installment_value = $value * ( ( $interest_value / 100 ) + 1 );
		$installment_value = $this->round_up( $installment_value );
		
		return $installment_value;
	}
	
	/**
	 * Returns the interest amount.
	 * 
	 * @param float $value          Amount value.
	 * @param float $interest_value Interest percentage value
	 * @return float
	 */
	public function get_interest_value( float $value, float $interest_value ) : float {
		$installment_value = $value * ( ( $interest_value / 100 ) );
		$installment_value = $this->round_up( $installment_value );
		
		return $installment_value;
	}

	/**
	 * Round up a value.
	 * 
	 * @param float $value value to round.
	 * @return float
	 */
	private function round_up( float $value ) : float {
		$precision     = 2;
		$pow           = pow( 10, $precision );
		$rounded_value = ( ceil( $value * $pow ) / $pow );
		return $rounded_value;
	}

	/**
	 * Calculate if the mininimum value of an installment reduce the quantity
	 * of installments
	 *
	 * @param float $total The total to be calculated.
	 * @param int   $max_installments The number maximum of installments.
	 * @param float $min_installment_value The minimum value of an installment.
	 * @return float The maximum possible installments for the total.
	 */
	public function calculate_max_installment_qty( $total, $max_installments, $min_installment_value ) : float {
		if ( 0 === $min_installment_value ) {
			return $max_installments;
		}

		return min( floor( $total / $min_installment_value ), $max_installments );
	}

	/**
	 * Calculate the installment value
	 *
	 * @param float $total The total to be calculated.
	 * @param int   $intalments_qty The number of installments.
	 * @return float The value of each installment.
	 */
	public function calculate_installment( $total, $intalments_qty ) : float {
		return $total / $intalments_qty;
	}
}
