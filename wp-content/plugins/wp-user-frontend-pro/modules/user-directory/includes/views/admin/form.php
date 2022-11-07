<h2><?php esc_html_e( 'WP User Frontend Pro: User Listing', 'wpuf-pro' ); ?></h2>

<ul class="wpuf-menu-field">
    <li class="header">
        <?php esc_html_e( 'Click to Add Items', 'wpuf-pro' ); ?>
    </li>
    <li>
        <a 
            href="#" 
            data-field_type="#wpuf-userlisting-meta" 
            class="wpuf-add-field button"
        >
            <?php esc_html_e( 'Add Meta field', 'wpuf-pro' ); ?>
        </a>
    </li>
    <li>
        <a 
            href="#" 
            data-field_type="#wpuf-userlistion-section" 
            class="wpuf-section-field button"
        >
            <?php esc_html_e( 'Add Section', 'wpuf-pro' ); ?>
        </a>
    </li>
    <li>
        <a 
            href="#" 
            data-field_type="#wpuf-userlistion-postype" 
            class="wpuf-postype-field button"
        >
        <?php esc_html_e( 'Add Post Type', 'wpuf-pro' ); ?>
        </a>
    </li>
    <li>
        <a 
            href="#" 
            data-field_type="#wpuf-userlistion-comment" 
            class="wpuf-comment-field button"
        >
        <?php esc_html_e( 'Add Comment', 'wpuf-pro' ); ?>
        </a>
    </li>
    <li>
        <a 
            href="#" 
            data-field_type="#wpuf-userlisting-social" 
            class="wpuf-social-field button"
        >
            <?php esc_html_e( 'Social', 'wpuf-pro' ); ?>
        </a>
    </li>
    <li>
        <a 
            href="#" 
            data-field_type="#wpuf-userlisting-file" 
            class="wpuf-file-field button"
        >
            <?php esc_html_e( 'Image / File', 'wpuf-pro' ); ?>
        </a>
    </li>
    <?php do_action( 'wpuf-userlisting-itemlist' ); ?>
</ul>

<div class="wpuf-form">
    <form method="post" action="">
        <?php
            wp_nonce_field( 'wpuf_userlisting', 'wpuf_userlinstin_nonce' );
            $this->print_profile_tab_settings();
        ?>
        
        <div class="wpuf-avatar">
            <span class="wpuf-userlisting-toggle button" ><?php _e( 'Toggle Fields', 'wpuf-pro' ); ?></span>
            <label>
                <p>
                    <label>
                        <input type="checkbox" <?php checked( $show_avatar, true ); ?> value="yes" name="wpuf_pf_field[settings][show_avatar]">
                        <?php _e( 'Show Avatar', 'wpuf-pro' ); ?>
                    </label>
                </p>
            </label>

        </div>
        <ul id="wpuf-all-field">
            <?php $this->show_form(); ?>
        </ul>

        <input type="submit" name="wpuf_save_button" value="<?php _e( 'Save Changes', 'wpuf-pro' ); ?>" class="button button-primary">
    </form>
</div>


<!--for post_type-->
<script type="text/template" id="wpuf-userlistion-postype">

<?php $this->form->li_wrap_open( array( 'label' => __( 'Post Listing', 'wpuf-pro' ) ) ); ?>

<?php $this->form->print_post( '<%= count %>', '' ); ?>
<?php $this->form->li_wrap_close(); ?>

</script>

<!--for comment-->
<script type="text/template" id="wpuf-userlistion-comment">

<?php $this->form->li_wrap_open( array( 'label' => 'Comment' ) ); ?>

<?php $this->form->print_comment( '<%= count %>', '' ); ?>
<?php $this->form->li_wrap_close(); ?>
</script>

<!--for section-->
<script type="text/template" id="wpuf-userlistion-section">

<?php $this->form->li_wrap_open( array( 'label' => __( 'Section', 'wpuf-pro' ) ) ); ?>

<?php $this->form->print_section( '<%= count %>', '' ); ?>
<?php $this->form->li_wrap_close(); ?>
</script>

<!--for meta -->
<script type="text/template" id="wpuf-userlisting-meta">

<?php $this->form->li_wrap_open( array( 'label' => __( 'Meta Key', 'wpuf-pro' ) ) ); ?>
<?php $this->form->print_meta( '<%= count %>', '' ); ?>
<?php
$this->form->li_wrap_close();
?>
</script>

<!--for file -->
<script type="text/template" id="wpuf-userlisting-file">

<?php $this->form->li_wrap_open( array( 'label' => 'Image / File' ) ); ?>
<?php $this->form->print_file( '<%= count %>', '' ); ?>
<?php
$this->form->li_wrap_close();
?>
</script>

<script type="text/template" id="wpuf-userlisting-social">

<?php $this->form->li_wrap_open( array( 'label' => 'Social Profiles' ) ); ?>
<?php $this->form->print_social( '<%= count %>', array( 'social_icon' => array( 'wpuf_userlisting' => 'wpuf_userlisting' ) ) ); ?>


<?php $this->form->li_wrap_close(); ?>
</script>

<script type="text/template" id="wpuf-extr-social-field">
<?php $this->form->social_icon_row_template(); ?>
</script>
