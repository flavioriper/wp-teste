<div class="data-tabs">
    <?php
    global $wp;
    $current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'posts'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $predefined_tabs = [
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
            foreach ( $saved_tabs as $key => $single_tab ) {
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
                    <a class="wp-block-file__button <?php echo $active; ?>" href="<?php echo add_query_arg( $query_args, home_url( $wp->request ) ); ?>">
                        <?php echo $single_tab['label']; ?>
                    </a>
                </li>
                <?php
            }
        } else {
            ?>
            <?php
            foreach ( $predefined_tabs as $key => $single_tab ) {
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
                    <a class="button <?php echo $active; ?>" href="<?php echo add_query_arg( $query_args, home_url( $wp->request ) ); ?>">
                        <?php echo $single_tab; ?>
                    </a>
                </li>
                <?php
            }
        }
        ?>
    </ul>
</div>
