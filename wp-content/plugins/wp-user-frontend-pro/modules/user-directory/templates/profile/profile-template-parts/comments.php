<?php
$tab_title  = ! empty( $tab_title ) ? $tab_title : __( 'Comments', 'wpuf-pro' );
$comments   = WPUF_User_Listing()->shortcode->get_comments( $user->ID );
$comments_per_page = 10;
$comments_count    = get_comments(
    [
        'count'   => true,
        'user_id' => $user->ID,
    ]
);
?>
<div class="wpuf-profile-section wpuf-ud-post-list">
    <h3 class="profile-section-heading"><?php echo esc_html( $tab_title ); ?></h3>

    <?php if ( isset( $comments ) && $comments ) : ?>
    <table class="user-post-list-table">
        <thead>
            <tr>
                <th><?php esc_attr_e( 'Comment', 'wpuf-pro' ); ?></th>
                <th><?php esc_attr_e( 'Commented On', 'wpuf-pro' ); ?></th>
                <th><?php esc_attr_e( 'Comment Date', 'wpuf-pro' ); ?></th>
                <th><?php esc_attr_e( 'Details', 'wpuf-pro' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $comments as $current_comment ) : ?>
            <tr>
                <td class="avatar-column">
                    <div class="post-description">
                        <p>
                        <?php echo wp_trim_words( $current_comment->comment_content, 10 ); ?>
                        </p>
                    </div>
                </td>
                <td>
                    <?php echo get_the_title( $current_comment->comment_post_ID ); ?>
                </td>
                <td>
                    <?php echo esc_attr( $current_comment->comment_date ); ?>
                </td>
                <td>
                    <a href="<?php echo get_comment_link( $current_comment ); ?>"><?php esc_html_e( 'Read More', 'wpuf-pro' ); ?></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
		<?php
        echo wpuf_pagination( $comments_count, $comments_per_page );
		?>
    <?php else : ?>
        <p><?php esc_html_e( 'No comment found', 'wpuf-pro' ); ?></p>
    <?php endif; ?>
</div>
