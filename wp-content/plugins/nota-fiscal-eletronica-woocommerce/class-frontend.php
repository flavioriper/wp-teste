<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WooCommerceNFeFrontend extends WooCommerceNFe {

  function __construct(){

    global $pagenow;

		// Compatibility with WooCommerce Admin 0.20.0 or higher
		if (
				self::wmbr_is_plugin_active('woocommerce-admin/woocommerce-admin.php') &&
				$pagenow != 'admin.php' &&
				( isset($_GET['page']) && $_GET['page'] != 'wc-admin' )
			) {
			remove_action( 'admin_notices', array( 'Automattic\WooCommerce\Admin\Loader', 'inject_before_notices' ), -9999 );
			remove_action( 'admin_notices', array( 'Automattic\WooCommerce\Admin\Loader', 'inject_after_notices' ), PHP_INT_MAX );
		}

		/**
		 * Plugin: Brazilian Market on WooCommerce (Customized)
		 * @author Claudio Sanches
		 * @link https://github.com/claudiosmweb/woocommerce-extra-checkout-fields-for-brazil
		**/
		if (
      !WooCommerceNFe::is_extra_checkout_fields_activated() &&
      get_option('wc_settings_woocommercenfe_tipo_pessoa') == 'yes'
		){

			add_action( 'wp_enqueue_scripts', array($this, 'scripts') );
			add_filter( 'woocommerce_billing_fields', array($this, 'billing_fields') );
			add_filter( 'woocommerce_shipping_fields', array($this, 'shipping_fields') );
			add_action( 'woocommerce_checkout_process', array($this, 'valide_checkout_fields') );
			add_filter( 'woocommerce_localisation_address_formats', array( $this, 'localisation_address_formats' ) );
			add_filter( 'woocommerce_formatted_address_replacements', array( $this, 'formatted_address_replacements' ), 1, 2 );
			add_filter( 'woocommerce_order_formatted_billing_address', array( $this, 'order_formatted_billing_address' ), 1, 2 );
			add_filter( 'woocommerce_order_formatted_shipping_address', array( $this, 'order_formatted_shipping_address' ), 1, 2 );
      add_filter( 'woocommerce_my_account_my_address_formatted_address', array($this, 'my_account_my_address_formatted_address' ), 1, 3 );
      add_filter( 'woocommerce_form_field', array($this, 'remove_checkout_optional_fields_label'), 10, 4 );

		}

  }
  
  /**
   * Remove optional label of required fields in checkout
   */
  function remove_checkout_optional_fields_label( $field, $key, $args, $value ) {
    
    // Only on checkout page
    if( is_checkout() && !is_wc_endpoint_url() ) {
      if (preg_match('/billing_persontype|billing_cpf|billing_cnpj|billing_company/i', $field)) {
        $optional = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>';
        $required = '&nbsp;<abbr class="required" title="' . esc_html__( 'required', 'woocommerce' ) . '">*</abbr>';
        $field = str_replace( $optional, $required, $field );
      }
    }

    return $field;

  }

  public static function scripts(){

      global $version_woonfe;

      $version = $version_woonfe;
      $array = array();

      $tipo_pessoa = get_option('wc_settings_woocommercenfe_tipo_pessoa');
      $mascara_campos = get_option('wc_settings_woocommercenfe_mascara_campos');
      $cep = get_option('wc_settings_woocommercenfe_cep');

      wp_register_script( 'woocommercenfe_maskedinput', '//cdnjs.cloudflare.com/ajax/libs/jquery.maskedinput/1.4.1/jquery.maskedinput.js', array('jquery'), $version, true );
      wp_register_script( 'woocommercenfe_correios', apply_filters( 'woocommercenfe_plugins_url', plugins_url( 'assets/js/correios.min.js', __FILE__ ) ), array('jquery'), $version, true );
      wp_register_script( 'woocommercenfe_scripts', apply_filters( 'woocommercenfe_plugins_url', plugins_url( 'assets/js/scripts.js', __FILE__ ) ), array('jquery'), $version, true );

      if ($mascara_campos == 'yes') $array['maskedinput'] = 1;
      if ($cep == 'yes') $array['cep'] = 1;
      if ($tipo_pessoa == 'yes') $array['person_type'] = 1;

      if ($mascara_campos == 'yes') wp_enqueue_script( 'woocommercenfe_maskedinput' );
      if ($cep == 'yes') wp_enqueue_script( 'woocommercenfe_correios' );
      if ($array) wp_localize_script( 'woocommercenfe_scripts', 'WooCommerceNFe', $array);
      if ($cep == 'yes' || $mascara_campos == 'yes') wp_enqueue_script( 'woocommercenfe_scripts' );

  }

  function billing_fields( $fields ){

    global $domain;

    $new_fields = array(
      'billing_persontype' => array(
        'type'     => 'select',
        'label'    => __( 'Tipo Pessoa', $domain ),
        'class'    => array( 'form-row-wide', 'person-type-field' ),
        'required' => false,
        'options'  => array(
            '1' => __( 'Pessoa Física', $domain ),
            '2' => __( 'Pessoa Jurídica', $domain )
        )
      ),
      'billing_cpf' => array(
        'label'       => __( 'CPF', $domain ),
        'placeholder' => _x( 'CPF', 'placeholder', $domain ),
        'class'       => array( 'form-row-wide', 'person-type-field' ),
        'required'    => false
      ),
      'billing_cnpj' => array(
        'label'       => __( 'CNPJ', $domain ),
        'placeholder' => _x( 'CNPJ', 'placeholder', $domain ),
        'class'       => array( 'form-row-first', 'person-type-field' ),
        'required'    => false
      ),
      'billing_ie' => array(
        'label'       => __( 'Inscrição Estadual', $domain ),
        'placeholder' => _x( 'Inscrição Estadual', 'placeholder', $domain ),
        'class'       => array( 'form-row-last', 'person-type-field' ),
        'required'    => false
      ),
      'billing_company' => array(
        'label'       => __( 'Razão Social', $domain ),
        'placeholder' => _x( 'Razão Social', 'placeholder', $domain ),
        'class'       => array( 'form-row-wide', 'person-type-field' ),
        'required'    => false
      ),
      'billing_first_name' => array(
        'label'       => __( 'Nome', $domain ),
        'placeholder' => _x( 'Nome', 'placeholder', $domain ),
        'class'       => array( 'form-row-first' ),
        'required'    => true
      ),
      'billing_last_name' => array(
        'label'       => __( 'Sobrenome', $domain ),
        'placeholder' => _x( 'Sobrenome', 'placeholder', $domain ),
        'class'       => array( 'form-row-last' ),
        'required'    => true,
        'clear'       => true,
      ),
      'billing_birthdate' => array(
        'label'       => __( 'Nascimento', $domain ),
        'placeholder' => _x( 'Nascimento', 'placeholder', $domain ),
        'class'       => array( 'form-row-first' ),
        'required'    => false
      ),
      'billing_sex' => array(
        'type'        => 'select',
        'label'       => __( 'Sexo', $domain ),
        'class'       => array( 'form-row-last' ),
        'clear'       => true,
        'required'    => true,
        'options'     => array(
          __( 'Feminino', $domain ) => __( 'Feminino', $domain ),
          __( 'Masculino', $domain )   => __( 'Masculino', $domain )
        )
      ),
      'billing_postcode' => array(
        'label'       => __( 'CEP', $domain ),
        'placeholder' => _x( 'CEP', 'placeholder', $domain ),
        'class'       => array( 'form-row-first', 'update_totals_on_change', 'address-field' ),
        'required'    => true
      ),
      'billing_state' => array(
        'type'        => 'state',
        'label'       => __( 'Estado', $domain ),
        'placeholder' => _x( 'Estado', 'placeholder', $domain ),
        'class'       => array( 'form-row-last', 'address-field' ),
        'clear'       => true,
        'required'    => true
      ),
      'billing_city' => array(
        'label'       => __( 'Cidade', $domain ),
        'placeholder' => _x( 'Cidade', 'placeholder', $domain ),
        'class'       => array( 'form-row-first', 'address-field' ),
        'required'    => true
      ),
      'billing_neighborhood' => array(
        'label'       => __( 'Bairro', $domain ),
        'placeholder' => _x( 'Bairro', 'placeholder', $domain ),
        'class'       => array( 'form-row-last', 'address-field' ),
        'clear'       => true,
      ),
      'billing_address_1' => array(
        'label'       => __( 'Endereço', $domain ),
        'placeholder' => _x( 'Endereço', 'placeholder', $domain ),
        'class'       => array( 'form-row-wide', 'address-field' ),
        'required'    => true
      ),
      'billing_number' => array(
        'label'       => __( 'Número', $domain ),
        'placeholder' => _x( 'Número', 'placeholder', $domain ),
        'class'       => array( 'form-row-first', 'address-field' ),
        'required'    => true
      ),
      'billing_address_2' => array(
        'label'       => __( 'Complemento', $domain ),
        'placeholder' => _x( 'Complemento', 'placeholder', $domain ),
        'class'       => array( 'form-row-last', 'address-field' ),
        'clear'       => true,
      ),
      'billing_phone' => array(
        'label'       => __( 'Telefone Fixo', $domain ),
        'placeholder' => _x( 'Telefone Fixo', 'placeholder', $domain ),
        'class'       => array( 'form-row-first' ),
        'required'    => true
      ),
      'billing_cellphone' => array(
        'label'       => __( 'Celular', $domain ),
        'placeholder' => _x( 'Celular', 'placeholder', $domain ),
        'class'       => array( 'form-row-last' ),
        'clear'       => true
      ),
      'billing_email' => array(
        'label'       => __( 'E-mail', $domain ),
        'placeholder' => _x( 'E-mail', 'placeholder', $domain ),
        'class'       => array( 'form-row-wide' ),
        'validate'    => array( 'email' ),
        'clear'       => true,
        'required'    => true
      )
    );

    return $new_fields;

  }

  function shipping_fields( $fields ){

    global $domain;

    $new_fields = array(
      'shipping_first_name' => array(
          'label'       => __( 'Nome', $domain ),
          'placeholder' => _x( 'Nome', 'placeholder', $domain ),
          'class'       => array( 'form-row-first' ),
          'required'    => true
      ),
      'shipping_last_name' => array(
          'label'       => __( 'Sobrenome', $domain ),
          'placeholder' => _x( 'Sobrenome', 'placeholder', $domain ),
          'class'       => array( 'form-row-last' ),
          'clear'       => true,
          'required'    => true
      ),
      'shipping_postcode' => array(
          'label'       => __( 'CEP', $domain ),
          'placeholder' => _x( 'CEP', 'placeholder', $domain ),
          'class'       => array( 'form-row-first', 'update_totals_on_change', 'address-field' ),
          'required'    => true
      ),
      'shipping_state' => array(
          'type'        => 'state',
          'label'       => __( 'Estado', $domain ),
          'placeholder' => _x( 'Estado', 'placeholder', $domain ),
          'class'       => array( 'form-row-last', 'address-field' ),
          'clear'       => true,
          'required'    => true
      ),
      'shipping_city' => array(
          'label'       => __( 'Cidade', $domain ),
          'placeholder' => _x( 'Cidade', 'placeholder', $domain ),
          'class'       => array( 'form-row-first', 'address-field' ),
          'required'    => true
      ),
      'shipping_neighborhood' => array(
          'label'       => __( 'Bairro', $domain ),
          'placeholder' => _x( 'Bairro', 'placeholder', $domain ),
          'class'       => array( 'form-row-last', 'address-field' ),
          'clear'       => true,
      ),
      'shipping_address_1' => array(
          'label'       => __( 'Endereço', $domain ),
          'placeholder' => _x( 'Endereço', 'placeholder', $domain ),
          'class'       => array( 'form-row-wide', 'address-field' ),
          'required'    => true
      ),
      'shipping_number' => array(
          'label'       => __( 'Número', $domain ),
          'placeholder' => _x( 'Número', 'placeholder', $domain ),
          'class'       => array( 'form-row-first', 'address-field' ),
          'required'    => true
      ),
      'shipping_address_2' => array(
          'label'       => __( 'Complemento', $domain ),
          'placeholder' => _x( 'Complemento', 'placeholder', $domain ),
          'class'       => array( 'form-row-last', 'address-field' ),
          'clear'       => true,
      )
    );

    return $new_fields;

  }

  function valide_checkout_fields(){

    $billing_persontype = isset( $_POST['billing_persontype'] ) ? $_POST['billing_persontype'] : 0;

    if ($billing_persontype == 1){

      if (empty( $_POST['billing_cpf'] )){

          wc_add_notice( sprintf( '<strong>%s</strong> %s.', __( 'CPF', $domain ), __( 'é um campo obrigatório', $domain ) ), 'error' );

      }

      if (!empty( $_POST['billing_cpf'] ) && !WooCommerceNFeFormat::is_cpf( $_POST['billing_cpf'] )){

          wc_add_notice( sprintf( '<strong>%s</strong> %s.', __( 'CPF', $domain ), __( 'informado não é válido', $domain ) ), 'error' );

      }

    }

    if ($billing_persontype == 2){

      if (empty( $_POST['billing_cnpj'] )){

          wc_add_notice( sprintf( '<strong>%s</strong> %s.', __( 'CNPJ', $domain ), __( 'é um campo obrigatório', $domain ) ), 'error' );

      }

      if (empty( $_POST['billing_company'] )){

          wc_add_notice( sprintf( '<strong>%s</strong> %s.', __( 'Razão Social', $domain ), __( 'é um campo obrigatório', $domain ) ), 'error' );

      }

      if (!empty( $_POST['billing_cnpj'] ) && !WooCommerceNFeFormat::is_cnpj( $_POST['billing_cnpj'] )){

          wc_add_notice( sprintf( '<strong>%s</strong> %s.', __( 'CNPJ', $domain ), __( 'informado não é válido', $domain ) ), 'error' );

      }

    }

  }

  function localisation_address_formats( $formats ){

    $formats['BR'] = "Nome: {name}\nEndereço: {address_1}, {number}\nComplemento: {address_2}\nBairro: {neighborhood}\nCidade: {city}\nEstado: {state}\nCEP: {postcode}";
    $formats['default'] = "Nome: {name}\nEndereço: {address_1}, {number}\nComplemento: {address_2}\nBairro: {neighborhood}\nCidade: {city}\nEstado: {state}\nCEP: {postcode}";

    return $formats;

  }

  function formatted_address_replacements( $replacements, $args ) {

		$replacements['{number}']       = (isset($args['number'])) ? $args['number'] : '';
		$replacements['{neighborhood}'] = (isset($args['neighborhood'])) ? $args['neighborhood'] : '';

    return $replacements;

	}

  function order_formatted_billing_address( $address, $order ) {

    $address['number']       = get_post_meta( $order->get_id(), '_billing_number', true );
		$address['neighborhood'] = get_post_meta( $order->get_id(), '_billing_neighborhood', true );

    return $address;

	}

  function order_formatted_shipping_address( $address, $order ) {

    $address['number']       = get_post_meta( $order->get_id(), '_shipping_number', true );
		$address['neighborhood'] = get_post_meta( $order->get_id(), '_shipping_neighborhood', true );

    return $address;

	}

  function my_account_my_address_formatted_address( $address, $customer_id, $name ) {

    $address['number']       = get_user_meta( $customer_id, $name . '_number', true );
		$address['neighborhood'] = get_user_meta( $customer_id, $name . '_neighborhood', true );

    return $address;

	}

}

new WooCommerceNFeFrontend;
