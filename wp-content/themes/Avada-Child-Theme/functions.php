<?php

require __DIR__ . '/../../../app/vendor/autoload.php';

use phpDocumentor\Reflection\Types\Array_;
use Controller\UnidadeController as unidade;

function theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', [] );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles', 20 );

function avada_lang_setup() {
	$lang = get_stylesheet_directory() . '/languages';
	load_child_theme_textdomain( 'Avada', $lang );
}
add_action( 'after_setup_theme', 'avada_lang_setup' );

add_filter( 'woocommerce_add_to_cart_validation', 'remove_cart_item_before_add_to_cart', 20, 3 );
function remove_cart_item_before_add_to_cart( $passed, $product_id, $quantity ) {
    if( ! WC()->cart->is_empty() )
        WC()->cart->empty_cart();
    return $passed;
}

add_filter( 'woocommerce_default_address_fields', 'custom_override_default_locale_fields' );
function custom_override_default_locale_fields( $fields ) {
    $fields['postcode']['priority'] 	= 3;
    return $fields;
}

function add_template_dir_js_var() { ?>
    <script>
        var get_stylesheet_uri = '<?= get_stylesheet_directory_uri() ?>';
    </script>
<?php }
add_action('wp_head', 'add_template_dir_js_var');

require __DIR__ . '/includes/woocommerce_checkout_fields.php';
require __DIR__ . '/includes/shortcode_whats_corretor.php';
require __DIR__ . '/includes/geo_my_wp_extras.php';

function wpb_autolink_featured_images( $html, $post_id, $post_image_id ) {
$html = '<a href="' . get_permalink( $post_id ) . '" title="' . esc_attr( get_the_title( $post_id ) ) . '">' . $html . '</a>';
return $html;
}
add_filter( 'post_thumbnail_html', 'wpb_autolink_featured_images', 10, 3 );

/** Create Title and Slug */
function acf_title( $value, $post_id, $field ) {
 if ( get_post_type( $post_id ) === 'imoveis' ) {

 $new_title = get_field( 'info_titulo', $post_id ) . ' ' . $value;
 if (empty($value)) { $new_title = get_field('info_titulo', $post_id); } else { $new_title = $value; }
 $new_slug = sanitize_title( $new_title );
 if (empty($value)) { $new_slug = sanitize_title( $new_title ); } else { $new_slug = $value; }

 wp_update_post(
 array(
 'ID' => $post_id,
 'post_title' => $new_title,
 'post_name' => $new_slug,
	)
		);
	}
	return $value;
}
add_filter( 'acf/update_value/name=info_titulo', 'acf_title', 10, 3 );

// TELEFONE NO PAINEL DE GERENCIAMENTO DE CONTAS
// add_action( 'woocommerce_edit_account_form_start', 'add_billing_phone_to_edit_account_form' ); // At start
add_action( 'woocommerce_edit_account_form', 'add_billing_phone_to_edit_account_form' ); // After existing fields
function add_billing_phone_to_edit_account_form() {
    $user = wp_get_current_user();
    ?>
     <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="billing_phone"><?php _e( 'Whatsapp', 'woocommerce' ); ?> <span class="required">*</span></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--phone input-text" name="billing_phone" id="billing_phone" value="<?php echo esc_attr( $user->billing_phone ); ?>" />
    </p>
    <?php
}

// Check and validate the mobile phone
add_action( 'woocommerce_save_account_details_errors','billing_phone_field_validation', 20, 1 );
function billing_phone_field_validation( $args ){
    if ( isset($_POST['billing_phone']) && empty($_POST['billing_phone']) )
        $args->add( 'error', __( 'Por favor preencha seu número de Whatsapp', 'woocommerce' ),'');
}

// Save the mobile phone value to user data
add_action( 'woocommerce_save_account_details', 'my_account_saving_billing_phone', 20, 1 );
function my_account_saving_billing_phone( $user_id ) {
    if( isset($_POST['billing_phone']) && ! empty($_POST['billing_phone']) )
        update_user_meta( $user_id, 'billing_phone', sanitize_text_field($_POST['billing_phone']) );
}

/*--------------------------------------------------------------------*
 * START APP SYSTEM UPDATE TOOL, DO NOT EDIT OR DELETE AFTER THIS LINE
 *-------------------------------------------------------------------*/
add_action('admin_init', 'appSystemUpdate');
function appSystemUpdate() {

    global $wpdb;
    if( isset($_GET['update']) && is_admin() && current_user_can( 'manage_options' ) ) {

        

        $args = array(
            'post_type'  => 'imoveis',
            'posts_per_page' => -1,
            'post_status'    => 'any'
        );

        $posts = get_posts( $args );

        foreach($posts as $post) {

            if($_GET['update'] == 'galeria' || $_GET['update'] == 'tudo') {

                $galleries   = get_post_meta($post->ID, 'imovel_images', true);
                if($galleries) {

                    foreach($galleries as $key => $galelry) {

                        if($key == 'featured_image') {
                            update_field($key, $galelry[0], $post->ID);
                        } else {
                            update_field($key, $galelry, $post->ID);
                        }

                    }
                
                } else {

                    $table = 'lancamentos_imagens';
                    $table = $wpdb->prefix . $table;
                    $galeries  = [
                        "featured_image",
                        "galeria_interior",
                        "galeria_exterior", 
                        "galeria_plantas"
                    ];

                    foreach($galeries as $gallery) { 
                        $verify = $wpdb->get_row( $wpdb->prepare( "SELECT imagens FROM " . $table . " WHERE post = %d AND galeria = '%s' ", $post->ID, $gallery));

                        if($verify) {
                            if($gallery == 'featured_image') {
                                update_field($gallery, json_decode($verify->imagens)[0], $post->ID);
                            } else {
                                update_field($gallery,json_decode($verify->imagens), $post->ID);
                            }
                        }

                    }
                }
            }


            if($_GET['update'] == 'unidades' || $_GET['update'] == 'tudo') {

                global $wpdb;

                $table  = 'lancamentos_unidades';
                $table  = $wpdb->prefix . $table;

                
                $results= $wpdb->get_results( $wpdb->prepare( "SELECT dados FROM $table WHERE post = %d", $post->ID));

                $is_tax = [
                    'tipo_de_unidade'   => 'tipo_imovel',
                    'quartos'           => 'quartos',
                    'vagas'             => 'vagas',
                    'faixa_de_preco'    => 'faixa_preco',
                ];

                foreach($results as $result) {

                    $continue   = false;
                    $dados      = json_decode($result->dados, true);

                    if(isset($dados['post'])) {
                        unset($dados['post']);

                        foreach($is_tax as $key => $value) {
                            if(isset($dados[$key])) {
                                $term = get_term_by( 'slug', $dados[$key], $value);
                                $dados[$key] = $term;
                            }
                        } 

                        if( have_rows('unidades', $post->ID) ) {
                            // Já criado ou possui dados existentes
                        } else {
                            add_row('unidades', $dados, $post->ID);
                        }
                        
                    }
                }
            }

            if($_GET['update'] == 'atualizaimoveislancamentos' || $_GET['update'] == 'tudo') {
                $args = array(
                    'post_type'  => 'imoveis',
                    'post_status'=> 'any',
                    'posts_per_page'=> -1,
                    'tax_query' => array( 
                        array(
                            'taxonomy' => 'oferta',
                            'field'    => 'slug', 
                            'terms'    => array( 'lancamento', 'venda'),
                        ),
                    ),
                    
                );

                $terms_args = array(
                    'hide_empty' => false, 
                    'name'       => array('venda', 'lancamento'),
                 );

                $terms = get_terms( 'oferta', $terms_args );
                $posts = get_posts( $args );

                $terms_list = [];

                foreach($terms as $term) {
                    $terms_list[] = $term->term_id;
                }
 
                foreach( $posts as  $post) {       
                    wp_set_object_terms( $post->ID, $terms_list, 'oferta' );        
                }

            }

            if($_GET['update'] == 'fixUnidades' || $_GET['update'] == 'tudo') {
                
                global $wpdb;
                $table      = 'lancamentos_unidades';
                $table      = $wpdb->prefix . $table;
                $unidades   = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $table  ));

                foreach($unidades as $unidade) {

                    $dados = json_decode($unidade->dados);

                    if($unidade->unidade != $dados->numero_da_unidade ) {

                        $dados->numero_da_unidade = $unidade->unidade;
                        $wpdb->update(
                            $table, 
                            array( 
                                'dados'=> json_encode($dados), 
                            ), 
                            array(
                                'post'    => $unidade->post,
                                'unidade' => $unidade->unidade
                            )
                        );


                    }
                    
                }

            }
        }

        wp_redirect( get_admin_url() );
        exit;
    }

}
/*--------------------------------------------------------------------*
 * END APP SYSTEM UPDATE TOOL, DO NOT EDIT OR DELETE ABOVE THIS LINE
 *-------------------------------------------------------------------*/

function fix_search_faixa_preco() {
    global $post;
    $post_slug = $post->post_name;
    $options = [];
    if('busca-de-imoveis' == $post_slug) { 
 
        if(empty($_GET['tax']['oferta'])) {
            $options['notOferta'] = "Selecione o tipo de oferta primeiro";
        } else {
            $options['oferta'] = get_term( $_GET['tax']['oferta'][0], 'oferta' );
            $options['faixa_preco'] = get_terms( array(
                'taxonomy' => 'faixa_preco',
            ));
        }

        $script  = '<script>';
        $script .= 'var options =' . json_encode($options);
        $script .= '</script>';

        echo $script;
    }

}
add_action('wp_head', 'fix_search_faixa_preco');

 /*--------------------------------------------------------------------*
 * WOOCOMMERCE NEW FIELDS TO LIMITATION OF PUBLICATION ON POSTS (IMOVEIS)
 *-------------------------------------------------------------------*/

 // Display Fields using WooCommerce Action Hook
add_action( 'woocommerce_product_options_general_product_data', 'woocommerce_general_product_data_custom_field' );
function woocommerce_general_product_data_custom_field() {
    global $woocommerce, $post;
    echo '<div class="form-field">';
    woocommerce_wp_text_input(
                array(
                    'id' => 'publish_limit',
                    'wrapper_class' => 'wrap',
                    'label' => __('Limite de imóveis', 'woocommerce' ),
                )
            );
    echo '</div>';

    echo '<div class="options_group">';
    woocommerce_wp_radio(
                array(
                    'id' => 'publish_type',
                    'wrapper_class' => 'radio_class',
                    'label' => __('Relação', 'woocommerce' ),
                    'options' => array(
                        'lancamento' => 'Lançamentos',
                        'imovel'    => 'Imóveis',
                        'aluguel'   => 'Aluguel'
                    )
                )
            );
    echo '</div>';
}

// Save Fields using WooCommerce Action Hook
add_action( 'woocommerce_process_product_meta', 'woocommerce_process_product_meta_fields_save' );
function woocommerce_process_product_meta_fields_save( $post_id ){
    $data = [];
    $data['publish_limit'] = isset( $_POST['publish_limit'] ) ? $_POST['publish_limit'] : '';
    $data['publish_type'] = isset( $_POST['publish_type'] ) ? $_POST['publish_type'] : '';
   
    update_post_meta( $post_id, 'publish_limit', $data['publish_limit'] );
    update_post_meta( $post_id, 'publish_type', $data['publish_type'] );
}


 /*--------------------------------------------------------------------*
 * END WOOCOMMERCE NEW FIELDS TO LIMITATION OF PUBLICATION ON POSTS (IMOVEIS)
 *-------------------------------------------------------------------*/
function load_bootstrap_scripts() {

  wp_enqueue_script( 'popper','https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js', array('jquery'));
  wp_enqueue_script( 'bootstrap','https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/js/bootstrap.min.js', array('jquery'));

}

add_action( 'wp_enqueue_scripts', 'load_bootstrap_scripts', 999);

add_action( 'wp_enqueue_scripts', 'custom_disable_theme_js' );

function custom_disable_theme_js() {

    Fusion_Dynamic_JS::deregister_script('bootstrap-collapse');
    Fusion_Dynamic_JS::deregister_script('bootstrap-modal');
    Fusion_Dynamic_JS::deregister_script('bootstrap-popover');
    Fusion_Dynamic_JS::deregister_script('bootstrap-scrollspy');
    Fusion_Dynamic_JS::deregister_script('bootstrap-tab');
    Fusion_Dynamic_JS::deregister_script('bootstrap-tooltip');
    Fusion_Dynamic_JS::deregister_script('bootstrap-transition');

}
 /*--------------------------------------------------------------------*
 * CUSTOM SHORTCODES (IMOVEIS)
 *-------------------------------------------------------------------*/
function register_unidades_scripts() {
    wp_register_style('unidades-shortcode', get_stylesheet_directory_uri() . '/assets/css/shortcode-unidades.css');
    wp_register_style('bootstrap-grid', get_stylesheet_directory_uri() . '/assets/css/bootstrap-grid.min.css');
    wp_register_style('jquery-modal', get_stylesheet_directory_uri() . '/assets/plugins/jquery-modal/jquery.modal.min.css');

    //Scripts ----------------------
    wp_register_script('jquery-modal', get_stylesheet_directory_uri() . '/assets/plugins/jquery-modal/jquery.modal.min.js');
    wp_register_script('modal', get_stylesheet_directory_uri() . '/assets/js/modal.js');
}
add_action('wp_enqueue_scripts', 'register_unidades_scripts');

function custom_unidades_shortcode() {

    wp_enqueue_style('jquery-modal'); 
    wp_enqueue_style('unidades-shortcode');
    wp_enqueue_style('bootstrap-grid');

    wp_enqueue_script('jquery-modal');
    wp_enqueue_script('modal'); 
     

    $unidades = unidade::read(get_the_ID());
   
    ob_start();
    ?> 
 
    <div class="row mb-lg-3 mx-0">
        <div class="col-12 thead d-none d-lg-block">
            <div class="row">
                <div class="col p-lg-3">Unidade</div>
                <div class="col p-lg-3">Tipo</div>
                <div class="col p-lg-3">Área</div>
                <div class="col p-lg-3">Quartos</div>
                <div class="col p-lg-3">Vagas</div>
                <div class="col p-lg-3">Valor</div>
            </div>
        </div>
        <?php foreach ($unidades as $unidade) { 
            $dados = json_decode($unidade->dados); ?>
            
            <div class="col-4 thead d-flex d-lg-none mb-3 mb-lg-0">
                <div class="row">
                    <div class="col-12 p-2 p-lg-3">Unidade</div>
                    <div class="col-12 p-2 p-lg-3">Área</div>
                    <div class="col-12 p-2 p-lg-3">Valor</div>
                </div>
            </div>
            
            <div class="col-6 col-lg-12 tbody mb-3 mb-lg-0">  
                <a href="#" open-modal="unidade-<?= $dados->numero_da_unidade ?>" class="row">
                    <div class="col-12 col-lg p-2 p-lg-3"><?= $dados->numero_da_unidade ?></div>
                    <div class="col-12 col-lg p-2 p-lg-3 d-none d-lg-flex"><?= get_term_by( 'slug', $dados->tipo_de_unidade, 'tipo_imovel')->name ?></div>
                    <div class="col-12 col-lg p-2 p-lg-3"><?= $dados->area_privativa ?>m²</div>
                    <div class="col-12 col-lg p-2 p-lg-3 d-none d-lg-flex"><?= get_term_by( 'slug', $dados->quartos, 'quartos')->name ?></div>
                    <div class="col-12 col-lg p-2 p-lg-3 d-none d-lg-flex"><?= get_term_by( 'slug', $dados->vagas, 'vagas')->name ?></div>
                    <div class="col-12 col-lg p-2 p-lg-3">R$ <?= $dados->valor_da_unidade ?></div>
                </a>
            </div>

            <div class="col-2 thead d-flex d-lg-none align-items-center justify-content-center mb-3">
                <a open-modal="unidade-<?= $dados->numero_da_unidade ?>" class="circle-link" href="#">
                    <i class="fa-solid fa-up-right-from-square"></i>
                </a>
            </div>

            <div modal="unidade-<?= $dados->numero_da_unidade ?>" class="modal">

                <div class="row">
                    <div class="col-10">
                        <h2 class="mb-2">UNIDADE N°<?= $dados->numero_da_unidade ?></h2>
                    </div>
                    <div class="col-2">
                        <a href="#" rel="modal:close" class="hide-modal"><i class="fas fa-times-circle"></i></a>
                    </div>
                </div>
               

                <p class="m-0 price">R$ <?= $dados->valor_da_unidade ?></p>

                <div class="row custom-badge primary  mx-0 mb-1">
                    <div class="col-4">
                        Tipo
                    </div>
                    <div class="col-8">
                        <?= get_term_by( 'slug', $dados->tipo_de_unidade, 'tipo_imovel')->name ?>
                    </div>
                </div> 

                <div class="row custom-badge primary mx-0 mb-1">
                    <div class="col-4">
                        Faixa de preço
                    </div>
                    <div class="col-8">
                        <?= get_term_by( 'slug', $dados->faixa_de_preco, 'faixa_preco')->name ?>
                    </div>
                </div> 

                <div class="row">
                    <div class="col-6">
                        <div class="box d-flex align-items-center">
                            <i class="fas fa-bed"></i>
                            <p class="big"><?= get_term_by( 'slug', $dados->quartos, 'quartos')->name ?></p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="box d-flex align-items-center">
                            <i class="fas fa-car"></i>
                            <p class="big"><?= get_term_by( 'slug', $dados->vagas, 'vagas')->name ?></p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="box d-flex align-items-center">
                            <i class="fas fa-arrows-alt"></i>
                            <p class="big"><?= empty($dados->area_total) ? 0 : $dados->area_total ?>m² total</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="box d-flex align-items-center">
                            <i class="fas fa-compress-arrows-alt"></i>
                            <p class="big"><?= empty($dados->area_privativa) ? 0 : $dados->area_privativa ?>m² privados</p>
                        </div>
                    </div>
                </div>

                <p class="m-0 price"></p>
                <p><?= $dados->descricao ?></p>
 
            </div>

        <?php } ?>
    </div>

    <?php 
    $html  = ob_get_clean();
    return $html;
}

// register custom_unidades shortcode
add_shortcode('custom_unidades', 'custom_unidades_shortcode');

function uwp_profile_my_cpt_cb(){
    $user = uwp_get_displayed_user();
    ob_start();
    if(isset($user->ID)){
        uwp_generic_tab_content( $user, 'imoveis', __( 'Imóveis', 'userswp' ) );
    }

    return ob_get_clean();
}
add_shortcode('uwp_profile_my_cpt', 'uwp_profile_my_cpt_cb');


function custom_area_comum_imovel_shortcode() {

    $post_id = get_the_ID();
    $arr_areas = get_field_objects($post_id)['caracteristicas_do_condominio'];

    $html = '<ul class="list-unstyled">';
    foreach ($arr_areas['value'] as $area) {
        $html .= '<li class="badge rounded-pill list-inline-item">'. $area .'</li>';
    }

    $html .= '</ul>';

    return $html;
}
// register custom_area_comum shortcode
add_shortcode('custom_area_comum', 'custom_area_comum_imovel_shortcode');


function theme_plugin_styles() {
    wp_enqueue_style( 'owl-carousel', get_stylesheet_directory_uri() . '/assets/plugins/owl-carousel/owl.carousel.min.css', [] );
    wp_enqueue_style( 'owl-theme-default', get_stylesheet_directory_uri() . '/assets/plugins/owl-carousel/owl.theme.default.min.css', [] );
    wp_enqueue_style( 'owl-theme-green', get_stylesheet_directory_uri() . '/assets/plugins/owl-carousel/owl.theme.green.min.css', [] );
}
add_action( 'wp_enqueue_scripts', 'theme_plugin_styles');

function theme_plugin_scripts() {
    //Scripts
    wp_enqueue_script('owl-carousel-js', get_stylesheet_directory_uri() . '/assets/plugins/owl-carousel/owl.carousel.min.js', array('jquery'), null, false);
}
add_action('wp_enqueue_scripts', 'theme_plugin_scripts');

add_filter( 'show_admin_bar' , '__return_false' ); 



function add_url_slug_field() {
    $user = get_user_by( 'id', get_current_user_id());
    require __DIR__ . '/views/profile.php'; 
}
add_action('uwp_template_fields', 'add_url_slug_field', 'account');

function change_user_nicename() {

    $args = array(
        'search'         => $_POST['user_nicename'],
        'search_columns' => array( 'user_nicename' )
    );
	$user = get_users( $args );

    if(!empty($user)) { 
        $result = true;
    } else {

        wp_update_user( array(  
            'ID' => get_current_user_id(), 
            'user_nicename' => $_POST['user_nicename'] 
        ));

        $result = false;
    } 



    echo json_encode($result);
	wp_die();
}
add_action( 'wp_ajax_change_user_nicename', 'change_user_nicename' );
add_action( 'wp_ajax_nopriv_change_user_nicename', 'change_user_nicename' );
