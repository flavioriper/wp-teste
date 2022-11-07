<?php
if ( $user->description ) {
	$user_desc = wp_kses( $user->description, wp_kses_allowed_html( 'user_description' ) );
	?>
<div class="user-biography">
    <div class="biogrophy-title">
        <h3><?php esc_html_e( 'Biography', 'wpuf-pro' ); ?></h3>
    </div>
    <div class="biography-description">
        <p><?php echo links_add_target( make_clickable( $user_desc ) ); ?></p>
    </div>
</div>
<?php } ?>
