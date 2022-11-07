<?php
$all_data['profile_header_template'] = $profile_header_template;
$all_data['avatar_size'] = $avatar_size;
$all_data['profile_fields'] = $profile_fields;
$all_data['profile_tabs'] = $profile_tabs;
$all_data['saved_tabs'] = $saved_tabs;
$all_data['user'] = $user;
?>
<div class="wpuf-ud-user-profile layout-two">
    <div class="profile">
        <?php
            printf( '<a class="button btn-back" href="%s">%s</a>', get_permalink(), __( '&larr; Back', 'wpuf-pro' ) );
        ?>
        <div class="profile-basic">
            <div class="user-image img-round">
                <?php echo get_avatar( $user->user_email, 120 ); ?>
            </div>
            <h3><?php echo esc_html( $user->display_name ); ?></h3>
        </div>
        <div class="user-contact">
            <?php
            $phone_no = get_user_meta( $user->ID, 'phone', true );
            if ( ! empty( $phone_no ) ) {
                ?>
            <div class="user-phone field">
                <div class="phone-icon icon">
                    <img src="<?php echo WPUF_UD_ASSET_URI . '/images/phone.svg'; ?>" alt="">
                </div>
                <div class="phone-number">
                    <p class="label"><?php esc_html_e( 'Phone No', 'wpuf-pro' ); ?></p>
                    <p class="value">
                        <a class="gray" href="tel:<?php echo esc_attr( $phone_no ); ?>">
                            <?php echo esc_html( $phone_no ); ?>
                        </a>
                    </p>
                </div>
            </div>
                <?php
            }
            if ( ! empty( $user->user_email ) ) {
                ?>
            <div class="user-email field">
                <div class="email-icon icon">
                    <img src="<?php echo WPUF_UD_ASSET_URI . '/images/email.svg'; ?>" alt="">
                </div>
                <div class="email">
                    <p class="label"><?php esc_html_e( 'Email', 'wpuf-pro' ); ?></p>
                    <p class="value"><?php echo make_clickable( $user->user_email ); ?></p>
                </div>
            </div>
                <?php
            }
            if ( ! empty( $user->user_url ) ) {
                ?>
            <div class="user-website field">
                <div class="website-icon icon">
                    <img src="<?php echo WPUF_UD_ASSET_URI . '/images/website.svg'; ?>" alt="">
                </div>
                <div class="website">
                    <p class="label"><?php esc_html_e( 'Website', 'wpuf-pro' ); ?></p>
                    <p class="value"><?php echo make_clickable( esc_url( $user->user_url ) ); ?></p>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php
        $all_data['user'] = $user;
        wpuf_load_pro_template( 'user-bio.php', $all_data, WPUF_UD_TEMPLATES . '/profile/profile-template-parts/' );
        ?>
    </div>
    <div class="user-data">
        <?php
        $profile_tabs = [
            'comments' => __( 'Comments', 'wpuf-pro' ),
            'posts'    => __( 'Posts', 'wpuf-pro' ),
            'file'     => __( 'File/Image', 'wpuf-pro' ),
            'about'    => __( 'About', 'wpuf-pro' ),
            'activity' => __( 'Activity', 'wpuf-pro' ),
        ];

        // if profile tabs are set from userlisting builder
        if ( count( $saved_tabs ) ) {
            foreach ( $saved_tabs as $key => $single_tab ) {
                // get the tab title to pass in specific template
                $all_data['tab_title'] = isset( $single_tab['label'] ) ? esc_html__( $single_tab['label'], 'wpuf-pro' ) : '';

                // show activity, if user activity module is on
                if ( 'activity' === $key && ! class_exists( 'WPUF_User_Activity' ) ) {
                    continue;
                }

                wpuf_load_pro_template( $key . '.php', $all_data, WPUF_UD_TEMPLATES . '/profile/profile-template-parts/' );
            }
        } else {
            foreach ( $profile_tabs as $key => $single_tab ) {
                if ( 'activity' === $key && ! class_exists( 'WPUF_User_Activity' ) ) {
                    continue;
                }
                $active = ( $current_tab === $key ) ? 'active' : '';
            }
        }
        ?>
    </div>
</div>
