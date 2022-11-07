<?php

/**
 *  Ajax functionality Class
 */
class WPUF_Ajax_QR_Code {

    /**
     * Constructor for the WPUF_QR_Code class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses add_action()
     */
    public function __construct() {
        // Load ajax script
        add_action( 'wp_ajax_build_qr_type_field', [ $this, 'get_qr_type_param_form' ] );
        add_action( 'wp_ajax_nopriv_build_qr_type_field', [ $this, 'get_qr_type_param_form' ] );
    }

    /**
     * Render custom type param field when user change type in frontend
     * @return json
     */
    public function get_qr_type_param_form() {
        $field_type = ! empty( $_REQUEST['type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['type'] ) ) : '';
        $postid     = ! empty( $_REQUEST['postid'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['postid'] ) ) : '';
        $formfield  = ! empty( $_REQUEST['formfield'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['formfield'] ) ) : '';
        $post_type  = ! empty( $_REQUEST['posttype'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['posttype'] ) ) : '';

        if ( empty( $field_type ) ) {
            return;
        }

        if ( ! empty( $postid ) && ! empty ( $post_type ) ) {
            $selected = trim( $post_type ) === 'user' ? get_user_meta( $postid, $formfield, true ) : get_post_meta( $postid, $formfield, true );
            $value    = ! empty( $selected['type_param'] ) ? $selected['type_param'] : '';
        }

        ob_start();

        switch ( $field_type ) {
            case 'url':
                ?>
                <p><input type="url" data-type="url" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][url]" value="<?php echo ( isset( $value['url'] ) ) ? $value['url'] : ''; ?>" placeholder="<?php esc_attr_e( 'Enter Url', 'wpuf-pro' ); ?>" size="40"></p>
                <?php
                break;
            case 'text':
                ?>
                <p><input type="text" data-type="text" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][text]" value="<?php echo ( isset( $value['text'] ) ) ? $value['text'] : ''; ?>" placeholder="<?php esc_attr_e( 'Enter Text', 'wpuf-pro' ); ?>" size="40"></p>
                <?php
                break;

            case 'geo':
                ?>
                <p><input type="text" data-type="text" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][geo_lat]" value="<?php echo ( isset( $value['geo_lat'] ) ) ? $value['geo_lat'] : ''; ?>" placeholder="<?php esc_attr_e( 'Enter Geo Latitude', 'wpuf-pro' ); ?>" size="40"></p>
                <p><input type="text" data-type="text" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][geo_long]" value="<?php echo ( isset( $value['geo_long'] ) ) ? $value['geo_long'] : ''; ?>" placeholder="<?php esc_attr_e( 'Enter Geo longitude', 'wpuf-pro' ); ?>" size="40"></p>
                <?php
                break;

            case 'sms':
                ?>
                <p><input type="text" data-type="text" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][sms_tel]" value="<?php echo ( isset( $value['sms_tel'] ) ) ? $value['sms_tel'] : ''; ?>" placeholder="<?php esc_attr_e( 'Enter Phone number', 'wpuf-pro' ); ?>" size="40"></p>
                <p><textarea data-type="textarea" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][sms_message]" id="" cols="30" rows="6" placeholder="<?php esc_attr_e( 'Enter Text message', 'wpuf-pro' ); ?>">
                    <?php echo ( isset( $value['sms_message'] ) ) ? $value['url'] : ''; ?></textarea></p>
                <?php
                break;

            case 'wifi':
                ?>
                <p><input type="text" data-type="text" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][wifi_type]" value="<?php echo ( isset( $value['wifi_type'] ) ) ? $value['wifi_type'] : ''; ?>" placeholder="<?php esc_attr_e( 'WPA, WEP or nopass', 'wpuf-pro' ); ?>" size="40"></p>
                <p><input type="text" data-type="text" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][wifi_ssid]" value="<?php echo ( isset( $value['wifi_ssid'] ) ) ? $value['wifi_ssid'] : ''; ?>" placeholder="<?php esc_attr_e( 'SSID of the Wi-Fi network', 'wpuf-pro' ); ?>" size="40"></p>
                <p><input type="text" data-type="text" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][wifi_password]" value="<?php echo ( isset( $value['wifi_password'] ) ) ? $value['wifi_password'] : ''; ?>" placeholder="<?php esc_attr_e( 'Password of the network', 'wpuf-pro' ); ?>" size="40"></p>
                <?php
                break;

            case 'card':
                ?>
                <p><input type="text" data-type="text" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][card_name]" value="<?php echo ( isset( $value['card_name'] ) ) ? $value['card_name'] : ''; ?>" placeholder="<?php esc_attr_e( 'Full name', 'wpuf-pro' ); ?>" size="40"></p>
                <p><input type="text" data-type="text" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][card_firm]" value="<?php echo ( isset( $value['card_firm'] ) ) ? $value['card_firm'] : ''; ?>" placeholder="<?php esc_attr_e( 'Firm name', 'wpuf-pro' ); ?>" size="40"></p>
                <p><input type="phone" data-type="text" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][card_tel]" value="<?php echo ( isset( $value['card_tel'] ) ) ? $value['card_tel'] : ''; ?>" placeholder="<?php esc_attr_e( 'Phone number', 'wpuf-pro' ); ?>" size="40"></p>
                <p><input type="email" data-type="email" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][card_email]" value="<?php echo ( isset( $value['card_email'] ) ) ? $value['card_email'] : ''; ?>" placeholder="<?php esc_attr_e( 'Email', 'wpuf-pro' ); ?>" size="40"></p>
                <p><input type="text" data-type="text" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][card_address]" value="<?php echo ( isset( $value['card_address'] ) ) ? $value['card_address'] : ''; ?>" placeholder="<?php esc_attr_e( 'Postal address', 'wpuf-pro' ); ?>" size="40"></p>
                <p><input type="url" data-type="url" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][card_url]" value="<?php echo ( isset( $value['card_url'] ) ) ? $value['card_url'] : ''; ?>" placeholder="<?php esc_attr_e( 'Website URL', 'wpuf-pro' ); ?>" size="40"></p>
                <p><input type="text" data-type="text" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][card_memo]" value="<?php echo ( isset( $value['card_memo'] ) ) ? $value['card_memo'] : ''; ?>" placeholder="<?php esc_attr_e( 'More infos about the contact', 'wpuf-pro' ); ?>" size="40"></p>
                <?php
                break;

            case 'email':
                ?>
                <p><input type="email" data-type="email" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][email_address]" value="<?php echo ( isset( $value['email_address'] ) ) ? $value['email_address'] : ''; ?>" placeholder="<?php esc_attr_e( 'Email Address', 'wpuf-pro' ); ?>" size="40"></p>
                <p><input type="text" data-type="text" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][email_subject]" value="<?php echo ( isset( $value['email_subject'] ) ) ? $value['email_subject'] : ''; ?>" placeholder="<?php esc_attr_e( 'Subject of the email to send', 'wpuf-pro' ); ?>" size="40"></p>
                <p><textarea data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" data-type="text" name="<?php echo $formfield; ?>[type_param][email_message]" placeholder="Content of the email" >
                    <?php echo ( isset( $value['email_message'] ) ) ? $value['email_message'] : ''; ?></textarea>
                </p>
                <?php
                break;

            case 'calendar':
                ?>
                <p><input type="text" data-type="text" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][calendar_title]" value="<?php echo ( isset( $value['calendar_title'] ) ) ? $value['calendar_title'] : ''; ?>" placeholder="<?php esc_attr_e( 'Name of the event', 'wpuf-pro' ); ?>" size="40"></p>
                <p><input type="text" data-type="text" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][calendar_place]" value="<?php echo ( isset( $value['calendar_place'] ) ) ? $value['calendar_place'] : ''; ?>" placeholder="<?php esc_attr_e( 'Place of the event', 'wpuf-pro' ); ?>" size="40"></p>
                <p><input type="text" data-type="text" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][calendar_begin]" value="<?php echo ( isset( $value['calendar_begin'] ) ) ? $value['calendar_begin'] : ''; ?>" placeholder="<?php esc_attr_e( 'Beginning event(dd/mm/yyyy hh:mm)', 'wpuf-pro' ); ?>" size="40"></p>
                <p><input type="text" data-type="text" data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>" name="<?php echo $formfield; ?>[type_param][calendar_end]" value="<?php echo ( isset( $value['calendar_end'] ) ) ? $value['calendar_end'] : ''; ?>" placeholder="<?php esc_attr_e( 'End event(dd/mm/yyyy hh:mm)', 'wpuf-pro' ); ?>" size="40"></p>
                <?php
                break;

            case 'phone':
                ?>
                <p><input type="phone" data-type="text"
                    data-required="<?php echo isset( $form_field['required'] ) ? $form_field['required'] : ''; ?>"
                    name="<?php echo $formfield; ?>[type_param][phone]"
                    value="<?php echo isset( $value['phone'] ) ? $value['phone'] : ''; ?>"
                    placeholder="<?php esc_attr_e( 'Phone number to call', 'wpuf-pro' ); ?>"
                    size="40"></p>
                <?php
                break;

            default:
                break;
        }

        wp_send_json_success( array( 'appned_data' => ob_get_clean() ) );
    }
}
