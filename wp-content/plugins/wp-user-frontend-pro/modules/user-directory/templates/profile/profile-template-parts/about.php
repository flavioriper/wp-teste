<?php
$user_id     = $user->ID;
$user_status = WPUF_User_Listing()->shortcode->is_approved( $user_id );

if ( ! $user_status ) {
    return;
}

$tab_title = ! empty( $tab_title ) ? esc_html( $tab_title ) : '';

$current_user      = $user;
$wpuf_current_user = wp_get_current_user();
$profile_fields    = get_option( 'wpuf_userlisting', [] );
// $this->settings    = isset( $profile_fields['settings'] ) ? $profile_fields['settings'] : [];
$profile_role      = isset( $current_user->roles[0] ) ? $current_user->roles[0] : '';
$current_user_role = is_user_logged_in() ? $wpuf_current_user->roles[0] : 'guest';

do_action( 'wpuf_user_profile_before_content' );
?>
<div class="wpuf-profile-section">
<h3 class="profile-tab-heading"><?php echo $tab_title; ?></h3>

<?php
if ( ! isset( $profile_fields['fields'] ) ) {
    return;
}
foreach ( $profile_fields['fields'] as $key => $field ) {
    if ( ! \WPUF\UserDirectory\ShortCode::can_user_see( $profile_role, $field, $current_user_role ) ) {
        continue;
    }

    switch ( $field['type'] ) {
        case 'meta':
            $meta_key = $field['meta'];
            do_action( 'wpuf_user_about_meta', $meta_key );
            $value = '';

            $repeat_field = get_user_meta( $user_id, $meta_key );

            if ( is_array( $repeat_field ) ) {
                $value = $repeat_field;
            }

            if ( ! empty( $current_user->data->$meta_key ) ) {
                $value = $current_user->data->$meta_key;
            }

            if ( is_array( $value ) ) {
                if ( isset( $value[0] ) && is_array( $value[0] ) ) {
                    $value = implode( ', ', $value[0] );
                } else {
                    $value = implode( ', ', $value );
                }
            } elseif ( ! empty( $current_user->data->$meta_key ) ) {
                $value = trim( $current_user->data->$meta_key );
            }
            ?>
            <?php if ( ! empty( $value ) && '-1' !== $value ) { ?>
                <div class="wpuf-profile-value">
                    <label class="wpuf-ud-profile-label"><?php echo $field['label']; ?>: </label>
                    <?php echo links_add_target( make_clickable( $value ) ); ?>
                </div>
				<?php
            }
            break;

        case 'section':
            ?>
            <div class="wpuf-profile-section">
                <h4 class="builder-section-heading"><?php echo $field['label']; ?></h4>
            </div>
            <?php
            break;

        case 'post':
            ?>
            <label><?php echo $field['label']; ?>:</label>

            <div class="wpuf-profile-value">
                <?php WPUF_User_Listing()->shortcode->user_post( $user_id, $field['post_type'], $field['count'] ); ?>
            </div>
            <?php
            break;

        case 'comment':
            ?>
            <label><?php echo $field['label']; ?>:</label>

            <div class="wpuf-profile-value"><?php // $this->user_comments( $user_id, $field['post_type'], $field['count'] ); ?></div>
            <?php
            break;

        case 'social':
            echo '<label>' . __( 'Social Section', 'wpuf-pro' ) . '</label>';
            WPUF_User_Listing()->shortcode->social_list( $field, get_user_by( 'id', $user_id ) );
            break;

        case 'file':
            ?>
            <label><?php echo $field['label']; ?>:</label>
            <?php
            WPUF_User_Listing()->shortcode->user_file( $field, $user_id );
            break;
    }
}
?>
</div>
<?php
do_action( 'wpuf_user_profile_after_content' );
