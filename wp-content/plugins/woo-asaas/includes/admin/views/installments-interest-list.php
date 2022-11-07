<?php
/**
 * Show installments interest form table.
 *
 * @package WooAsaas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="<?php echo esc_attr( $args['field_key'] . '_1' ); ?>">
		<?php echo esc_attr( $args['data']['title'] ); ?>
		</label>
	</th>
	<td class="forminp">
		<table class="wc_gateways widefat" cellspacing="0">
			<thead>
				<tr>
					<th class="sort"><?php esc_attr_e( 'Installments', 'woo-asaas' ); ?></th>
					<th class="name"><?php esc_attr_e( 'Interest', 'woo-asaas' ); ?></th>
				</tr>
			</thead>
			<tbody class="installments-interest__list">
				<?php
				for( $installment = 1; $installment <= $args['max_installments']; $installment++ ) :
					$value = true === isset( $args['interest_installment'][$installment] ) ? $args['interest_installment'][ $installment ] : 0;
				?>
				<tr>
					<td width="20%"><?php echo esc_attr( $installment ); ?></td>
					<td>
						<input class="small-input" type="text" placeholder="0"
						name="<?php echo esc_attr( $field_key ); ?>[<?php echo esc_attr( $installment ); ?>]"
						id="<?php echo esc_attr( $field_key . '_' . $installment ); ?>" value="<?php echo esc_attr( $value ); ?>" /> %
					</td>
				</tr>
				<?php endfor; ?>
			</tbody>
		</table>
	</td>
</tr>