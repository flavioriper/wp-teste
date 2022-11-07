<div class="wpuf-ud-user-profile layout-three">
    <?php
        printf( '<a class="btn-back" href="%s">%s</a>', get_permalink(), __( '&larr; Back', 'wpuf-pro' ) );
    ?>
    <div class="profile">
        <div class="profile-header" style="background-image: url(<?php echo WPUF_UD_ASSET_URI . '/images/profile-banner.png'; ?>); background-size: cover;">
        </div>
        <div class="profile-bottom">
            <div class="profile-image">
                <?php echo get_avatar( $user->user_email, 100 ); ?>
            </div>
            <div class="profile-details">
                <div class="user-name">
                    <h3><?php echo $user->display_name; ?></h3>
                </div>
                <div class="user-details">
                    <?php echo make_clickable( $user->user_email ); ?><br>
                    <?php echo links_add_target( make_clickable( esc_url( $user->user_url ) ) ); ?>
                </div>
            </div>
            <?php
                $all_data['user'] = $user;
                wpuf_load_pro_template( 'social-profile.php', $all_data, WPUF_UD_TEMPLATES . '/profile/profile-template-parts/' );
            ?>
        </div>
    </div>
    <div class="user-data">
        <div class="data-tabs">
            <?php
                global $wp;
                $profile_tabs = [
                    'comments' => __( 'Comments', 'wpuf-pro' ),
                    'posts'    => __( 'Posts', 'wpuf-pro' ),
                    'file'     => __( 'File/Image', 'wpuf-pro' ),
                    'about'    => __( 'About', 'wpuf-pro' ),
                    'activity' => __( 'Activity', 'wpuf-pro' ),
                ];
                ?>
            <ul>
                <?php
                if ( count( $saved_tabs ) ) {
                    foreach ( $saved_tabs as $key => $profile_tab ) {
                        // show activity, if user activity module is on
                        if ( 'activity' === $key && ! class_exists( 'WPUF_User_Activity' ) ) {
                            continue;
                        }

                        $active = ( $current_tab === $key ) ? 'active' : '';
                        ?>
                <li>
                    <?php
                    $query_args = [
                        'tab'     => $key,
                        'user_id' => $user->ID,
                    ];
                    ?>
                    <a class="<?php echo $active; ?>" href="<?php echo add_query_arg( $query_args, home_url( $wp->request ) ); ?>">
                        <?php echo $profile_tab['label']; ?>
                    </a>
                </li>
                        <?php
                    }
                } else {
                    foreach ( $profile_tabs as $key => $single_tab ) {
                        if ( 'activity' === $key && ! class_exists( 'WPUF_User_Activity' ) ) {
                            continue;
                        }

                        $active = ( $current_tab === $key ) ? 'active' : '';
                        ?>
                    <li>
                        <?php
                        $query_args = [
                            'tab'     => $key,
                            'user_id' => $user->ID,
                        ];
                        ?>
                        <a class="<?php echo $active; ?>" href="<?php echo add_query_arg( $query_args, home_url( $wp->request ) ); ?>">
                            <?php echo $single_tab; ?>
                        </a>
                    </li>
                    <?php }
                }
                ?>
            </ul>
        </div>
        <?php
        $all_data['profile_tabs'] = $profile_tabs;
        $all_data['saved_tabs']   = $saved_tabs;
        $all_data['user']         = $user;

        wpuf_load_pro_template( $current_tab . '.php', $all_data, WPUF_UD_TEMPLATES . '/profile/profile-template-parts/' );
        ?>
    </div>
</div>
