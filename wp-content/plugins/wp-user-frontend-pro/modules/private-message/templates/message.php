<div id="wpuf-private-message">
    <router-view></router-view>
</div><!-- wpuf-private-message -->
<?php 
    // Require inbox template
    require_once dirname( __FILE__ ) . '/inbox.php';

    // Require single chat conversation template
    require_once dirname( __FILE__ ) . '/single-conversation.php';
?>

