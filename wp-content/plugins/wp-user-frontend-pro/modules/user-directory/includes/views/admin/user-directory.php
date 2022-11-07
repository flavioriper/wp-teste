<div class="wrap wpuf-user-directory-wrapper">
    <div class="wrap">
        <?php do_action( 'wpuf-admin-profile-builder' ); ?>
        <?php
            $builder = new WPUF\UserDirectory\Admin\Builder();
            $builder->build_form();
        ?>
    </div>
</div>

