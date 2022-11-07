(function($) {
 
    $("#user_nicename").on("input", function() {
        var nicename = $(this).val();
        $("#get_user_nicename").html(nicename);
    }); 
    

    $("#update_user_nicename").on("click", function() {
        var _this = $(this);
        var data = {
            'action': 'change_user_nicename',
            'user_nicename': $("#user_nicename").val()  
        };

        _this.parents('form').css({
            'opacity': '0.4',
            'pointer-events': 'none'
        });

        $.post(_wpUtilSettings.ajax, data, function(response) {
            var condition = $.parseJSON( response );
           
            if(condition) {
                $("#get_status").removeClass("text-success"); 
                $("#get_status").addClass("text-danger");
                $("#get_status").text('Em uso');
            } else {
                $("#get_status").removeClass("text-danger");  
                $("#get_status").addClass("text-success"); 
                $("#get_status").text('Alterado com sucesso');
            }
            
            _this.parents('form').css({
                'opacity': '1',
                'pointer-events': 'auto'
            });

        });
    });
    
  
    /* ------------------------------------------------------------
     * Configura o tipo de Telefone automatizado para 8 o 9 digitos
     * ----------------------------------------------------------*/
 
    var SPMaskBehavior = function (val) {
        return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
    },
    
    spOptions = {
        onKeyPress: function(val, e, field, options) {
            field.mask(SPMaskBehavior.apply({}, arguments), options);
        }
    };

     /* ------------------------------------------------------------
     * Adiciona a mascara com a mascara automatizada de telefones
     * ----------------------------------------------------------*/

    $('#billing_phone').mask(SPMaskBehavior, spOptions);

    /* ------------------------------------------------------------
     * Adiciona o loader no envio do formulário de pesquisa
     * ----------------------------------------------------------*/
    if ($(".gmw-submit-field-wrapper")[0]){
        $('.gmw-form .gmw-submit-field-wrapper').append(`<img class="geocoder-search-loader" src="${get_stylesheet_uri}/assets/svg/spinner.svg" alt="Loader" />`);
        $('.gmw-form').on('submit', function(e){
            e.preventDefault();
            $('.gmw-submit-button').addClass('hide');

            setTimeout(() => {
                $('.geocoder-search-loader').addClass('visible');
            }, 300);

            e.currentTarget.submit();   
        });
    }

    $('.owl-carousel').owlCarousel({
        margin: 10,
        loop:true,
        nav:false,
        dots:false,
        autoplay:false,
        lazyLoad:true,
        autoplayHoverPause:true,
        responsive:{
            0:{
                items:1
            },
            600:{
                items:1
            },
            1000:{
                items:1
            }
        }
    });
    /* ------------------------------------------------------------
     * Verifica campos habilitados ou desabilitados no filtro
     * ----------------------------------------------------------*/
    if('notOferta' in options) {
        $('select.faixa_preco').html('<option value="" selected="selected">' + options.notOferta + '</option>');
    } else {

        $('select.faixa_preco').html('<option value="" selected="selected">Todas as faixas de preço</option>');

        $.each(options.faixa_preco, function( index, value ) {
            var is = value.slug.split('-');
            
            if(options.oferta.slug == 'aluguel' || options.oferta.slug == 'temporada') {
                if(is[0] != options.oferta.slug) {
                    delete options.faixa_preco[index]
                } 
            } else {
                if(is[0] == 'aluguel' || is[0] == 'temporada') {
                    delete options.faixa_preco[index]
                } 
            }        
        });

        $.each(options.faixa_preco, function( index, value ) {
            if (typeof value !== 'undefined') {
                $('select.faixa_preco').append( '<option class="level-0" value="'+ value.term_id +'">'+ value.name +'</option>' );
            }
        });
        
    }

    $("#oferta-taxonomy-wrapper input").on("click", function(){
        $(this).parents('form').submit();
    }); 


})(jQuery);