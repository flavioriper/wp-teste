jQuery( function ( $ ) {

    load_billing = function(){

        data = {
            user_id:      $( '#customer_user' ).val(),
            type_to_load: 'billing',
            action:       'woocommerce_get_customer_details',
            security:     woocommerce_admin_meta_boxes.get_customer_details_nonce
        };

        $.ajax({
            url: woocommerce_admin_meta_boxes.ajax_url,
            data: data,
            type: 'POST',
            success: function( response ) {
                var info = response;
                if ( info ) {
                    $( '#_billing_persontype' ).val( info.billing_persontype ).change();
                    $( 'input#_billing_cpf' ).val( info.billing_cpf ).change();
                    $( 'input#_billing_cnpj' ).val( info.billing_cnpj ).change();
                    $( 'input#_billing_ie' ).val( info.billing_ie ).change();
                    $( 'input#_billing_birthdate' ).val( '' ).change();
                    $( 'input#_billing_sex' ).val( '' ).change();
                    $( 'input#_billing_number' ).val( info.billing_number ).change();
                    $( 'input#_billing_neighborhood' ).val( info.billing_neighborhood ).change();
                    $( 'input#_billing_address_1' ).val( info.billing_address_1 ).change();
                    $( 'input#_billing_address_2' ).val( info.billing_address_2 ).change();
                    $( 'input#_billing_first_name' ).val( info.billing_first_name ).change();
                    $( 'input#_billing_last_name' ).val( info.billing_last_name ).change();
                    $( 'input#_billing_city' ).val( info.billing_city ).change();
                    $( 'input#_billing_postcode' ).val( info.billing_postcode ).change();
                    $( '#_billing_country' ).val( info.billing_country ).change();
                    $( '#_billing_state' ).val( info.billing_state ).change();
                    $( 'input#_billing_email' ).val( info.billing_email ).change();
                    $( 'input#_billing_phone' ).val( info.billing_phone ).change();
                    $( 'input#_billing_company' ).val( info.billing_company ).change();
                }
            }
        });

    };

    load_shipping = function(){

        data = {
            user_id:      $( '#customer_user' ).val(),
            type_to_load: 'shipping',
            action:       'woocommerce_get_customer_details',
            security:     woocommerce_admin_meta_boxes.get_customer_details_nonce
        };

        $.ajax({
            url: woocommerce_admin_meta_boxes.ajax_url,
            data: data,
            type: 'POST',
            success: function( response ) {
                var info = response;
                if ( info ) {
                    $( 'input#_shipping_number' ).val( info.shipping_number ).change();
                    $( 'input#_shipping_neighborhood' ).val( info.shipping_neighborhood ).change();
                    $( 'input#_shipping_first_name' ).val( info.shipping_first_name ).change();
                    $( 'input#_shipping_last_name' ).val( info.shipping_last_name ).change();
                    $( 'input#_shipping_company' ).val( info.shipping_company ).change();
                    $( 'input#_shipping_address_1' ).val( info.shipping_address_1 ).change();
                    $( 'input#_shipping_address_2' ).val( info.shipping_address_2 ).change();
                    $( 'input#_shipping_city' ).val( info.shipping_city ).change();
                    $( 'input#_shipping_postcode' ).val( info.shipping_postcode ).change();
                    $( '#_shipping_country' ).val( info.shipping_country ).change();
                    $( '#_shipping_state' ).val( info.shipping_state ).change();
                }

            }
        });

    };

    change_customer_user = function(){

        load_billing();
        load_shipping();

    };

    add_shipping_info = function(){

      var $table = $('.nfe-shipping-table:not(.payment-info)');
      var $table_body = $table.find('.nfe-table-body');

      var html = $table.find('.entry:first-child').html();

      var element = $('<div class="entry"></div>').html(html);
      element.appendTo($table_body);

      update_indexes();
      increment_count();

    };

    remove_shipping_info = function(){

      var $entry = $(this).closest('.entry');
      $entry.remove();

      update_indexes();
      decrement_count();

    };

    update_indexes = function(){

      var $rows = $('.nfe-table-body').find('.entry');
      var index = 0;
      $rows.each(function(){

        $(this).find('input[type="text"]').each(function(){
          var input_name = $(this).attr('name').replace(/[0-9]/g, '');
          $(this).attr('name', input_name+index);
        });

        $(this).find('select').each(function(){
          var select_name = $(this).attr('name').replace(/[0-9]/g, '');
          $(this).attr('name', select_name+index);
        });

        index++;

      });
    };

    increment_count = function(){

      var $input = get_count_element();
      var count = parseInt($input.val());
      count++;
      $input.val(count);

    };

    decrement_count = function(){

      var $input = get_count_element();
      var count = parseInt($input.val());
      count--;
      $input.val(count);

    };

    get_count_element = function(){

      return $('input[name="shipping-info-count"]');

    };

    show_hide_fields = function(){

      if( this.value == 2 ) {
        $("._billing_cnpj_field").show();
        $("._billing_ie_field").show();
        $("._billing_company_field").show();

        $("._billing_cpf_field").hide();
      } else {
        $("._billing_cpf_field").show();

        $("._billing_cnpj_field").hide();
        $("._billing_ie_field").hide();
        $("._billing_company_field").hide();
      }
    }

    load_fields = function(){
      if(  $('#_billing_persontype').val() == 2 ) {
        $("._billing_cnpj_field").show();
        $("._billing_ie_field").show();
        $("._billing_company_field").show();

        $("._billing_cpf_field").hide();
      } else {
        $("._billing_cpf_field").show();

        $("._billing_cnpj_field").hide();
        $("._billing_ie_field").hide();
        $("._billing_company_field").hide();
      }
    }

    load_fields_shipping = function(){

      if(  $('#_shipping_persontype').val() == 2 ) {
        $("._shipping_cnpj_field").show();
        $("._shipping_ie_field").show();

        $("._shipping_cpf_field").hide();
      } else if( $('#_shipping_persontype').val() == 1 ) {
        $("._shipping_cpf_field").show();

        $("._shipping_cnpj_field").hide();
        $("._shipping_ie_field").hide();
      } else {
        $("._shipping_cpf_field").hide();
        $("._shipping_cnpj_field").hide();
        $("._shipping_ie_field").hide();
      }

    }

    update_payment_desc_label = function() {
      
      var descs_active = $('.nfe-payment-desc').filter(function() { 
        return $(this).css('display') !== 'none'; 
      }).size();

      if (descs_active > 0) {
        $('.payment-desc-title').show();
      }
      else {
        $('.payment-desc-title').hide();
      } 

    }

    format_field_tax_class = function(element) {

      var new_value;

      new_value = 'REF' + element.target.value.replace(/\D|[REF]/g, '');
    
      $(element.target).val(new_value);

    }

    $( 'a.load_customer_billing' ).on( 'click', load_billing );
    $( 'a.load_customer_shipping' ).on( 'click', load_shipping );
    $( '#customer_user' ).on( 'change', change_customer_user );

    $('#wmbr-add-shipping-info').on('click', add_shipping_info);
    $('.nfe-table-body').on('click', '.wmbr-remove-shipping-info', remove_shipping_info);

    $('#_billing_persontype').on('change', show_hide_fields);
    $('.edit_address').on('click', load_fields);

    $('#_shipping_persontype').on('change', load_fields_shipping);
    $('.edit_address').on('click', load_fields_shipping);

    // Volume
    $('input[name="nfe_volume_weight"]').on('change', function(){
      if ($(this).is(':checked')){
        $('.transporte').show();
      } else {
        $('.transporte').hide();
      }
    });

    $('input[name="transporte_peso_bruto"]').on('keyup', function(){
      $('input[name="transporte_peso_liquido"]').val($(this).val());
    });

    // Installments
    $('input[name="nfe_installments"]').on('change', function(){
      if ($(this).is(':checked')){
        $('.nfe_installments').show();
      } else {
        $('.nfe_installments').hide();
      }
    });
    $('input[name="nfe_installments_n"]').on('change', function(){

      value = $(this).val();
      div = $('.nfe_installments.row-first');
      total = $('.nfe_installments.row').length + 1;

      if (value > total){

        diff = value - total;
        if (diff > 0){
          for (var i = 0; i < diff; i++) {
            div.clone().appendTo(".nfe_installments.block");
            $('.nfe_installments.row-first:last').addClass('row').removeClass('row-first');
          }
        }

      } else if (value < total){

        diff = total - value;
        if (diff > 0){
          for (var i = 0; i < diff; i++) {
            $('.nfe_installments.row:last').remove();
          }
        }

      }

    });

    // Additional Information
    $('input[name="nfe_additional_info"]').on('change', function(){
      if ($(this).is(':checked')){
        $('.nfe_additional_info_text').show();
      } else {
        $('.nfe_additional_info_text').hide();
      }
    });

    // Intermediador info
    $('input[name="nfe_info_intermediador_cnpj"], input[name="wc_settings_woocommercenfe_cnpj_intermediador"]').mask('99.999.999/9999-99');
    $('input[name="nfe_info_intermediador"]').on('change', function(){
      if ($(this).is(':checked')){
        $('.nfe_info_intermediador').show();
      } else {
        $('.nfe_info_intermediador').hide();
      }
    });

    //Show or hide "Descrição do pagamento" label
    update_payment_desc_label();

    // Show payment desc field if payment method is 99
    $('.nfe-payment-methods-sel').change(function(element) {
			
			var payment_desc = $(event.target).parent().parent().find('.nfe-payment-desc');
			
			if (element.target.value == 99) {
				$(payment_desc).show();
        $('.payment-desc-title').show();
			}
			else {
				$(payment_desc).val('');
				$(payment_desc).hide();
			}

      update_payment_desc_label();
			
		});

    //Format field tax class to avoid wrong values
    $('#wc_settings_woocommercenfe_imposto').on('input', format_field_tax_class);

});
