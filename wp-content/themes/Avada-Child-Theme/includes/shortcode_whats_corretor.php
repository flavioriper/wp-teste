<?php
function pf_whatscorretor(){
    $author_id = get_the_author_meta('ID');
    //$whats     = get_field('numero_do_whatsapp', 'user_'. $author_id );
    $whats     = get_user_meta( $author_id, 'billing_phone', true );
    $permalink = get_permalink(number_format($id));
    return('https://api.whatsapp.com/send?phone=55' . $whats . '&text=Olá,%20venho%20da%20Yuppins,%20gostaria%20de%20mais%20informações%20sobre%20o%20seguinte%20imóvel:%20' . $permalink);  
}

add_shortcode('whatscorretor', 'pf_whatscorretor');