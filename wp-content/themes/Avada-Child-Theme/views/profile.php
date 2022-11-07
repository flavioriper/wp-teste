<div data-argument="user_nicename" class="form-group">
    <div class="d-flex">
        <input type="text" name="user_nicename" id="user_nicename" placeholder="URL" title="URL" value="<?= $user->data->user_nicename ?>" class="form-control">
        <input class="btn btn-primary btn-block text-uppercase" id="update_user_nicename" value="Alterar" type="button">
    </div>
    <p class="small"><?= get_permalink(uwp_get_page_id( 'profile_page' )) ?>
        <span id="get_user_nicename"><?= $user->data->user_nicename ?></span>
        <span id="get_status" class="text-danger"></span> 
    </p>
</div>