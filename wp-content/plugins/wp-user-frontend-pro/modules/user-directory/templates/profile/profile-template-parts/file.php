<?php
$user_id   = $user->ID;
$files     = WPUF_User_Listing()->shortcode->get_user_uploaded_files( $user_id );
$tab_title = ! empty( $tab_title ) ? $tab_title : __( 'File/Image', 'wpuf-pro' );
$saved_image_size = wpuf_get_option( 'pro_img_size', 'user_directory', 78 );
$images_sizes = wpuf_get_image_sizes_array();
$gallery_image_size = isset( $images_sizes[ $saved_image_size ]['width'] ) ? $images_sizes[ $saved_image_size ]['width'] : 'auto';

/**
 * Filters the returned current gallery image width for user profile file tab section
 *
 * @since 3.4.11
 *
 * @param string        $gallery_image_size The current image width
 */
$gallery_image_size = apply_filters( 'wpuf_profile_gallery_image_size', $gallery_image_size );
?>
<div class="wpuf-profile-section wpuf-ud-files-area">
    <h3 class="profile-section-heading"><?php echo esc_html( $tab_title ); ?></h3>
    <?php if ( isset( $files ) && $files ) : ?>
        <div class="file-container">
            <?php
            foreach ( $files as $file ) :
                $ext = explode( '.', $file );
                $ext = end( $ext );
                // $ext = 'doc';

                $icon = '';
                $image = false;
                switch ( $ext ) {
                    case 'pdf':
                        $icon = 'pdf.svg';
                        break;
                    case 'xls':
                        $icon = 'xls.svg';
                        break;
                    case 'zip':
                        $icon = 'zip.svg';
                        break;
                    case 'doc':
                        $icon = 'doc.svg';
                        break;
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                        $icon  = $file;
                        $image = true;
                        break;
                    default:
                        $icon = 'file.svg';
                        break;
                }
                ?>
                <?php if ( $image ) : ?>
                    <div>
                        <a href="<?php echo $file; ?>" target="_blank">
                            <img style="width: <?php echo $gallery_image_size; ?>px;" src="<?php echo $file; ?>" class="preview-image">
                        </a>
                    </div>
                <?php else : ?>
                    <div class="single-file">
                        <a href="<?php echo $file; ?>" target="_blank">
                            <img style="width: <?php echo $gallery_image_size; ?>px;" src="<?php echo WPUF_UD_ASSET_URI . '/images/' . $icon; ?>" >
                        </a>
                    </div>
                <?php endif; ?>

            <?php endforeach; ?>
            </div>

    <?php else : ?>
        <p><?php esc_attr_e( 'No file found', 'wpuf-pro' ); ?></p>
    <?php endif; ?>
</div>
