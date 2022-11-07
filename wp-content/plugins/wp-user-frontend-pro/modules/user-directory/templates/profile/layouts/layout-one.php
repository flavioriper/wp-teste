<div class="ud-single-profile-container alignwide layout-one">
    <div class="ud-section-one-fourth short-description">
        <?php
            printf( '<a class="button btn-back wp-block-file__button" href="%s">%s</a>', get_permalink(), __( '&larr; Back', 'wpuf-pro' ) );
        ?>
        <div class="ud-profile-intro">
            <div class="user-image img-round">
                <?php echo get_avatar( $user->user_email, 120 ); ?>
            </div>
            <div class="display-name">
                <h4><?php echo esc_html( $user->display_name ); ?></h4>
            </div>
            <div class="contact-info">
                <?php echo make_clickable( $user->user_email ); ?>
                <br>
                <?php
                if ( isset( $user->user_url ) ) {
                    $user_own_url = esc_url( $user->user_url );
                    echo links_add_target( make_clickable( $user_own_url ) );
                }
                ?>
            </div>
            <?php
                $user_desc = get_user_meta( $user->ID, 'description', true );
            if ( ! empty( $user_desc ) ) {
                $desc_part_one = substr( $user_desc, 0, 100 );
                $desc_part_two = substr( $user_desc, 101, strlen( $user_desc ) - 1 );
                ?>
                <div class="biography">
                    <h5><?php esc_html_e( 'Biography', 'wpuf-pro' ); ?></h5>
                <?php
                if ( strlen( $user_desc ) > strlen( $desc_part_one ) ) {
                    ?>
                            <p>
                        <?php echo links_add_target( make_clickable( $desc_part_one ) ); ?>
                                <span class="desc-part-two" style="display: none;">
                            <?php echo links_add_target( make_clickable( $desc_part_two ) ); ?>
                                </span>
                            </p>
                            <a href="#" id="btn-view-more"><?php esc_html_e( 'View More', 'wpuf-pro' ); ?></a>
                        <?php
                } else {
                    echo '<p>' . links_add_target( make_clickable( $user_desc ) ) . '</p>';
                }
                ?>
                </div>
                <?php
            }

            $all_data['user'] = $user;
            wpuf_load_pro_template( 'social-profile.php', $all_data, WPUF_UD_TEMPLATES . '/profile/profile-template-parts/' );
            ?>
        </div>
    </div>
    <div class="ud-section-three-fourths">
        <div class="user-data">
            <?php
            $all_data['profile_tabs'] = $profile_tabs;
            $all_data['saved_tabs']   = $saved_tabs;
            $all_data['user']         = $user;
            // get the tab title to pass in specific template
            $all_data['tab_title']    = isset( $single_tab['label'] ) ? esc_html__( $single_tab['label'], 'wpuf-pro' ) : '';

            wpuf_load_pro_template( 'data-tabs.php', $all_data,  WPUF_UD_TEMPLATES . '/profile/profile-template-parts/' );
            ?>
            <div class="user-tab-content-area">
                <?php
                wpuf_load_pro_template( $current_tab . '.php', $all_data,  WPUF_UD_TEMPLATES . '/profile/profile-template-parts/' );
                ?>
            </div>
        </div>
    </div>
</div>
