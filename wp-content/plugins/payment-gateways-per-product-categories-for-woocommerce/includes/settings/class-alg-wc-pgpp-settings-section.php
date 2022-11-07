<?php
/**
 * Payment Gateways per Products for WooCommerce - Section Settings
 *
 * @version 1.2.0
 * @since   1.0.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PGPP_Settings_Section' ) ) :

class Alg_WC_PGPP_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 */
	function __construct() {
		add_filter( 'woocommerce_get_sections_alg_wc_pgpp',              array( $this, 'settings_section' ) );
		add_filter( 'woocommerce_get_settings_alg_wc_pgpp_' . $this->id, array( $this, 'get_settings' ), PHP_INT_MAX );
		
		if(get_option('alg_wc_pgpp_products_section_enabled', 'no') === 'yes'){
			add_action( 'add_meta_boxes',    array( $this, 'alg_wc_pgpp_metabox' ), PHP_INT_MAX );
			add_action( 'save_post_product', array( $this, 'save_pgpp_meta_box' ), PHP_INT_MAX, 3 );
		}
	}
	
	/**
	 * alg_wc_pgpp_metabox.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_pgpp_metabox() {
		add_meta_box(
			'alg-wc-pgpp-product-pg',
			__( 'Choose Payment Gateway', 'payment-gateways-per-product-categories-for-woocommerce' ),
			array( $this, 'display_pgpp_metabox' ),
			'product',
			'side',
			'high'
		);
	}
	
	/**
	 * display_pgpp_metabox.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @todo    [dev] `placeholder` for textarea
	 * @todo    [dev] `class` for all remaining types
	 */
	function display_pgpp_metabox() {
		global $post;

		$available_gateways = WC()->payment_gateways->payment_gateways();
		$alg_wc_pgpp_pay_titles = get_option('alg_wc_pgpp_pay_titles', array());
		foreach ( $available_gateways as $gateway_id => $gateway ) {
			$gateway_title = $gateway->title;
			if(isset($alg_wc_pgpp_pay_titles[$gateway_id])){
				$gateway_title = $alg_wc_pgpp_pay_titles[$gateway_id];
			}
			
			$ischecked = false;
			$option_name = 'alg_wc_pgpp_products_include_' . $gateway_id;
			$optionvalue = get_option($option_name, array());

			if(isset($optionvalue) && !empty($optionvalue) && is_array($optionvalue)){
				if(in_array($post->ID, $optionvalue)){
					$ischecked = true;
				}
			}
		?>
			<p class="form-field">
			<input type="checkbox" id="alg_wc_pgpp_post_gateway_<?php echo $gateway_id; ?>" name="alg_wc_pgpp_post_gateway_<?php echo $gateway_id; ?>" <?php if( $ischecked == true ) { ?>checked="checked"<?php } ?> />  <?php echo $gateway_title; ?>
			</p>
		<?php
		}
		?>
		<input type="hidden" name="alg_wc_pgpp_save_post_gateway" value="alg_wc_pgpp_save_post_gateway">
		<?php 
	}
	
	/**
	 * save_pgpp_meta_box.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function save_pgpp_meta_box( $post_id, $post, $update ) {
		$the_id = get_the_ID();
		if($the_id != $post_id){
			$pid = $post_id;
		}else{
			$pid = $post_id;
		}
		// Check that we are saving with current metabox displayed.
		if ( ! isset( $_POST[ 'alg_wc_pgpp_save_post_gateway' ] ) ) {
			return;
		}
		$available_gateways = WC()->payment_gateways->payment_gateways();
		foreach ( $available_gateways as $gateway_id => $gateway ) {
			$gateway_post_key = 'alg_wc_pgpp_post_gateway_' . $gateway_id;
			$option_name = 'alg_wc_pgpp_products_include_' . $gateway_id;
			$optionvalue = get_option($option_name, array());
			
			if ( isset( $_POST[ $gateway_post_key ] )  && $_POST[ $gateway_post_key ] == 'on') {
				if(isset($optionvalue) && !empty($optionvalue) && is_array($optionvalue)){
					if(!in_array($post->ID, $optionvalue)){
						$optionvalue[] = (string) $pid;
						update_option($option_name, $optionvalue);
					}
				}else{
					$optionvalue[] = (string) $pid;
					update_option($option_name, $optionvalue);
				}
			}else{
				if(isset($optionvalue) && !empty($optionvalue) && is_array($optionvalue)){
					if(in_array($post->ID, $optionvalue)){
						if (($key = array_search($post->ID, $optionvalue)) !== false) {
							unset($optionvalue[$key]);
						}
						update_option($option_name, $optionvalue);
					}
				}
			}
		}
	}
	
	/**
	 * settings_section.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function settings_section( $sections ) {
		$sections[ $this->id ] = $this->desc;
		return $sections;
	}

	/**
	 * get_products.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 * @todo    [dev] use `'post_type' => 'product_variation'` (instead of `$_product->get_children()`) (need to unset main variable product then)
	 */
	function get_products( $products = array(), $post_status = 'any', $block_size = 512, $add_variations = false ) {
		return array();
		$offset = 0;
		while( true ) {
			$args = array(
				'post_type'      => 'product',
				'post_status'    => $post_status,
				'posts_per_page' => $block_size,
				'offset'         => $offset,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'fields'         => 'ids',
			);
			$loop = new WP_Query( $args );
			if ( ! $loop->have_posts() ) {
				break;
			}
			foreach ( $loop->posts as $post_id ) {
				$products[ $post_id ] = get_the_title( $post_id ) . ' (#' . $post_id . ')';
				if ( $add_variations ) {
					$_product = wc_get_product( $post_id );
					if ( $_product->is_type( 'variable' ) ) {
						unset( $products[ $post_id ] );
						foreach ( $_product->get_children() as $child_id ) {
							$products[ $child_id ] = get_the_title( $child_id ) . ' (#' . $child_id . ')';
						}
					}
				}
			}
			$offset += $block_size;
		}
		return $products;
	}
	
	function get_selected_product_options($option_id){
		$return = array();
		$posts = get_option($option_id, array());
		if(isset($posts) && !empty($posts)){
			foreach($posts as $postid){
				$return[ $postid ] = get_the_title( $postid ) . ' (#' . $postid . ')';
			}
		}
		return $return;
	}

	/**
	 * get_terms.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_terms( $args ) {
		if ( ! is_array( $args ) ) {
			$_taxonomy = $args;
			$args = array(
				'taxonomy'   => $_taxonomy,
				'orderby'    => 'name',
				'hide_empty' => false,
			);
		}
		global $wp_version, $sitepress;
		if (isset($sitepress)) {
			$admin_current_lang = apply_filters( 'wpml_current_language', NULL );
			$sitepress->switch_lang('all');
		}
		
		if ( version_compare( $wp_version, '4.5.0', '>=' ) ) {
			$_terms = get_terms( $args );
		} else {
			$_taxonomy = $args['taxonomy'];
			unset( $args['taxonomy'] );
			$_terms = get_terms( $_taxonomy, $args );
		}
		
		if (isset($sitepress)) {
			$sitepress->switch_lang($admin_current_lang);
		}
		
		$_terms_options = array();
		if ( ! empty( $_terms ) && ! is_wp_error( $_terms ) ){
			foreach ( $_terms as $_term ) {
				if (isset($sitepress)) {
					$tname = $_term->name . ' ('.$this->get_language_by_term_id($_term->term_id, $_taxonomy).')';
				}else{
					$tname = $_term->name;
				}
				$_terms_options[ $_term->term_id ] = $tname;
			}
		}
		return $_terms_options;
	}
	
	function get_language_by_term_id($tid, $type){
		global $wpdb;
		$type = 'tax_' . $type;
		$table = $wpdb->prefix . 'icl_translations';
		$sql = "SELECT language_code FROM $table WHERE element_id = $tid AND element_type = '$type'";
		$language_code = $wpdb->get_var( $sql );
		return $language_code;
	}

	/**
	 * get_gateways_settings.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 * @todo    [dev] add "Chosen select / Standard multiselect" option
	 * @todo    [dev] add "Select all" button
	 * @todo    [dev] add "Set as IDs" option (i.e. enter categories / tags / products by ID (i.e. as comma separated text))
	 * @todo    [dev] maybe add (i.e. duplicate) settings to "WooCommerce > Settings > Payments > Direct bank transfer" etc.
	 */
	function get_gateways_settings( $args ) {
		$available_gateways = WC()->payment_gateways->payment_gateways();
		$alg_wc_pgpp_pay_titles = get_option('alg_wc_pgpp_pay_titles', array());
		
		$gateways_settings  = array();
		foreach ( $available_gateways as $gateway_id => $gateway ) {
			
			if($args['options_id'] == 'products'){
				$options_in = $this->get_selected_product_options('alg_wc_pgpp_' . $args['options_id'] . '_include_' . $gateway_id);
				$options_ex = $this->get_selected_product_options('alg_wc_pgpp_' . $args['options_id'] . '_exclude_' . $gateway_id);
			}else{
				$options_in = $args['options'];
				$options_ex = $args['options'];
			}

			$gateway_title = $gateway->title;
			if(isset($alg_wc_pgpp_pay_titles[$gateway_id])){
				$gateway_title = $alg_wc_pgpp_pay_titles[$gateway_id];
			}
			
			$gateways_settings = array_merge( $gateways_settings, array(
				array(
					'title'    => $gateway_title,
					'type'     => 'title',
					'id'       => 'alg_wc_pgpp_' . $args['options_id'] . '_gateway_' . $gateway_id . '_options',
				),
				array(
					'title'    => __( 'Include', 'payment-gateways-per-product-categories-for-woocommerce' ),
					'desc_tip' => $args['desc_tips']['include'] . ' ' . __( 'Ignored if empty.', 'payment-gateways-per-product-categories-for-woocommerce' ),
					'id'       => 'alg_wc_pgpp_' . $args['options_id'] . '_include_' . $gateway_id,
					'default'  => '',
					'type'     => 'multiselect',
					'class'    => 'chosen_select ' . $args['options_id'] . '_select_pgpp',
					'options'  => $options_in,
				),
				array(
					'title'    => __( 'Exclude', 'payment-gateways-per-product-categories-for-woocommerce' ),
					'desc_tip' => $args['desc_tips']['exclude'] . ' ' . __( 'Ignored if empty.', 'payment-gateways-per-product-categories-for-woocommerce' ),
					'id'       => 'alg_wc_pgpp_' . $args['options_id'] . '_exclude_' . $gateway_id,
					'default'  => '',
					'type'     => 'multiselect',
					'class'    => 'chosen_select ' . $args['options_id'] . '_select_pgpp',
					'options'  => $options_ex,
				),
				array(
					'type'     => 'sectionend',
					'id'       => 'alg_wc_pgpp_' . $args['options_id'] . '_gateway_' . $gateway_id . '_options',
				),
			) );
		}
		return $gateways_settings;
	}

}

endif;
