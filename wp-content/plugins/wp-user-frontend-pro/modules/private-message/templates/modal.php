<div id="wpuf-form-template-modal">
    <div class="wpuf-form-template-modal">

        <!-- <span id="modal-label" class="screen-reader-text"><?php _e( 'Modal window. Press escape to close.',  'wpuf-pro'  ); ?></span> -->
        <a href="#" class="close">× <!-- <span class="screen-reader-text"><?php _e( 'Close modal window',  'wpuf-pro'  ); ?></span> --></a>
        <div class="content-container">
            <div class="content">
                <h2 class="wpuf-modal-title">
                    <?php _e( 'Select User', 'wpuf-pro' ); ?>
                </h2>
                <div class="user-search">
                    <input 
                        type="text" 
                        placeholder="<?php _e( 'Search User',  'wpuf-pro'  ); ?>" 
                        name="search"
                        class="user-search wpuf-user-search-input"
                    >
                    <svg 
                        width="16" 
                        height="16" 
                        viewBox="0 0 16 16" 
                        fill="none" 
                        xmlns="http://www.w3.org/2000/svg"
                        class="wpuf-search-user-icon"
                    >
                        <path 
                            d="M15.7222 14.3814L12.6465 11.3064C13.5786 10.0864 14.0899 8.60662 14.0901 7.04511C14.0901 5.16334 13.357 3.39408 12.0259 2.06349C10.695 0.732892 8.92563 0 7.04323 0C5.16107 0 3.39143 0.732892 2.06056 2.06349C-0.686854 4.81056 -0.686854 9.28013 2.06056 12.0267C3.39143 13.3576 5.16107 14.0905 7.04323 14.0905C8.60506 14.0903 10.0852 13.5791 11.3054 12.6472L14.3811 15.7222C14.5661 15.9074 14.809 16 15.0516 16C15.2943 16 15.5372 15.9074 15.7222 15.7222C16.0926 15.3521 16.0926 14.7516 15.7222 14.3814ZM3.40162 10.686C1.39374 8.67849 1.39397 5.41196 3.40162 3.40427C4.37431 2.43201 5.66767 1.89635 7.04323 1.89635C8.41902 1.89635 9.71216 2.43201 10.6848 3.40427C11.6575 4.37675 12.1933 5.66984 12.1933 7.04511C12.1933 8.42061 11.6575 9.71348 10.6848 10.686C9.71216 11.6584 8.41902 12.1941 7.04323 12.1941C5.66767 12.1941 4.37431 11.6584 3.40162 10.686Z" 
                            fill="#CED3DA"
                        />
                    </svg>
                </div>
                <?php $users = get_users( array() ); ?>
                <?php if ( $users ): ?>
                <ul class="pm-user-list">
                    <?php 
                        foreach ( $users as $user ) { 
                            $user_info = get_userdata( $user->data->ID );
                            $full_name = ( $user_info->first_name && $user_info->last_name ) ? $user_info->first_name . ' ' . $user_info->last_name : '';
                    ?>
                        
                        <li class="user" @click="selectUser">
                            <a href="<?php echo '#/user/'.$user->data->ID; ?>">
                                <?php echo get_avatar($user->data->ID, 80) ?>
                            </a>
                            <a 
                                href="<?php echo '#/user/'.$user->data->ID; ?>"
                                class="wpuf-private-message-username"
                            >
                                <?php echo $full_name ? ucwords( strtolower( $full_name ) ) : ucwords( strtolower( $user->data->user_login ) ); ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
                <p class="no-more-users"><?php esc_html_e( 'No more users', 'wpuf-pro' ); ?></p>
                <button class="wpuf-user-list-load-more"><?php esc_html_e( 'Load User', 'wpuf-pro' ); ?></button>
                <?php else: ?>
                    <p class="user-not-found"><?php esc_html_e( 'No user found.', 'wpuf-pro' ); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="wpuf-form-template-modal-backdrop"></div>
</div>