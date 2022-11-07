<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="wpbe-button" onclick="wpbe_act_popupeditor(this, <?php echo intval($post['post_parent']) ?>)" data-post_id="<?php esc_html_e($post['ID']) ?>" id="popup_val_<?php echo $field_key ?>_<?php echo $post['ID'] ?>" data-key="<?php esc_html_e($field_key) ?>" data-terms_ids="" data-name="<?php esc_html_e(sprintf(esc_html('Post: %s', 'bulk-editor'), $post['post_title'])) ?>">
    <?php esc_html_e('Content', 'bulk-editor') ?>
</div>
