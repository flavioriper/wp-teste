<div class="wpuf-fields">
    <div :id="'wpuf-img_label-' + field.id + '-upload-container'">
        <div class="wpuf-attachment-upload-filelist" data-type="file" data-required="yes">
            <a class="button file-selector wpuf_img_label_148" href="#">
                <?php _e( 'Select Files', 'wpuf-pro' ); ?>
            </a>
        </div>
    </div>

    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
