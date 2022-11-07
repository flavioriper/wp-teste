<div class="wpuf-fields">
    <?php if ( ! empty( wpuf_get_option( 'gmap_api_key', 'wpuf_general' ) ) ): ?>
    <div :class="['wpuf-form-google-map-container', 'yes' === field.address ? 'show-search-box': 'hide-search-box']">
        <input class="wpuf-google-map-search" type="text" placeholder="<?php _e( 'Search address', 'wpuf-pro' ); ?>">
        <div class="wpuf-form-google-map"></div>
    </div>
    <div :class="['wpuf-fields clearfix', field.directions ? 'has-directions-checkbox' : '']">
        <span v-if="field.directions" class="wpuf-directions-checkbox">
            <a class="btn btn-brand btn-sm" href="#" ><i class="fa fa-map-marker" aria-hidden="true"></i><?php _e( 'Directions »', 'wpuf-pro' ); ?></a>
        </span>
    </div>
    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
    <?php else: ?>
        <p><?php esc_html_e( 'Please set Google Map API Key first in WPUF admin settings to use this Google Map field.', 'wpuf-pro' ); ?></p>
    <?php endif; ?>
</div>
