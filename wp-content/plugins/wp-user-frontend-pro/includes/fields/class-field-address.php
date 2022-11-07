<?php
/**
 * Address Field Class
 *
 * @since 3.1.0
 **/
class WPUF_Form_Field_Address extends WPUF_Field_Contract {

    public function __construct() {
        $this->name       = __( 'Address Field', 'wpuf-pro' );
        $this->input_type = 'address_field';
        $this->icon       = 'address-card-o';
    }

    /**
     * Render the Address field
     *
     * @param  array  $field_settings
     *
     * @param  integer  $form_id
     *
     * @param  string  $type
     *
     * @param  integer  $post_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id, $type = 'post', $post_id = null ) {
        $value               = '';
        $address_fields_meta = array();

        if ( isset( $post_id ) && $post_id !== '0' && $this->is_meta( $field_settings ) ) {
            $value               = $this->get_meta( $post_id, $field_settings['name'], $type );
            $address_fields_meta = is_array( $value ) ? $value : array();
        }

        $country_select_hide_list = isset( $field_settings['address']['country_select']['country_select_hide_list'] ) ? $field_settings['address']['country_select']['country_select_hide_list'] : array();
        $country_select_show_list = isset( $field_settings['address']['country_select']['country_select_show_list'] ) ? $field_settings['address']['country_select']['country_select_show_list'] : array();
        $list_visibility_option   = $field_settings['address']['country_select']['country_list_visibility_opt_name'];
        $this->field_print_label( $field_settings, $form_id );

        ?>

        <div class="wpuf-fields <?php echo ' wpuf_' . $field_settings['name'] . '_' . $form_id; ?>">
            <?php
            foreach ( $field_settings['address'] as $each_field => $field_array ) {
                switch ( $each_field ) {
                    case 'street_address':
                        $autocomplete = 'street-address address-line1';
                        $data_type = 'text';
                        break;

                    case 'street_address2':
                        $autocomplete = 'street-address address-line2';
                        $data_type = 'text';
                        break;

                    case 'city_name':
                        $autocomplete = 'street-address address-level2';
                        $data_type = 'text';
                        break;

                    case 'state':
                        $autocomplete = 'state state-name';
                        $data_type = 'select';
                        break;

                    case 'zip':
                        $autocomplete = 'postal-code';
                        $data_type = 'text';
                        break;

                    case 'country_select':
                        $autocomplete = 'country country-name';
                        $data_type = 'select';
                        break;

                    default:
                        $autocomplete = $each_field;
                        $data_type = 'text';
                        break;
                }
                ?>

                <div class="wpuf-address-field <?php echo $each_field; ?>">
                    <?php if ( isset( $field_array['checked'] ) && ! empty( $field_array['checked'] ) ) { ?>

                        <div class="wpuf-sub-fields">
                            <?php
                                $data_required = ! empty( $field_array['required'] ) ? 'yes' : 'no';
                            if ( in_array( $field_array['type'], [ 'text', 'hidden', 'email', 'password' ], true ) ) {
                                ?>
                            <input
                                type="<?php echo $field_array['type']; ?>"
                                name="<?php echo $field_settings['name'] . '[' . $each_field . ']'; ?>"
                                value="<?php echo isset( $address_fields_meta[ $each_field ] ) ? esc_attr( $address_fields_meta[ $each_field ] ) : $field_array['value']; ?>"
                                placeholder="<?php echo $field_array['placeholder']; ?>"
                                class="textfield"
                                size="40"
                                autocomplete='<?php echo $autocomplete; ?>'
                                data-required='<?php echo $data_required; ?>'
                                data-type='<?php echo $data_type; ?>'
                                data-label='<?php echo $field_array['label']; ?>' />

                                <?php
                            } elseif ( $each_field === 'country_select' ) {
                                $data_label = $field_array['label'];
                                echo '<' . $field_array['type'] . ' name="' . $field_settings['name'] . '[' . $each_field . ']' . '" autocomplete="' . $autocomplete . '" data-required="' . $data_required . '" data-type="' . $data_type . '" data-label="' . $data_label . '">';
                                echo '</' . $field_array['type'] . '>';

                                $address_fields_meta['country_select'] = isset( $address_fields_meta['country_select'] ) ? $address_fields_meta['country_select'] : $field_array['value'];
                                ?>
                                <script>
                                    var field_name             = '<?php echo $field_settings['name'] . '[' . $each_field . ']'; ?>';
                                    var countries              = <?php echo wpuf_get_countries( 'json' ); ?>;
                                    var banned_countries       = JSON.parse('<?php echo wp_json_encode( $country_select_hide_list ); ?>');
                                    var allowed_countries      = JSON.parse('<?php echo wp_json_encode( $country_select_show_list ); ?>');
                                    var list_visibility_option = '<?php echo $list_visibility_option; ?>';
                                    var option_string          = '<option value=""><?php esc_html_e( 'Select Country', 'wpuf-pro' ); ?></option>';
                                    var sel_country            = '<?php echo isset( $address_fields_meta['country_select'] ) ? $address_fields_meta['country_select'] : ''; ?>';

                                    if ( list_visibility_option == 'hide' ) {
                                        for (country in countries){
                                            if ( jQuery.inArray(countries[country].code,banned_countries) != -1 ){
                                                continue;
                                            }
                                            option_string = option_string + '<option value="'+ countries[country].code +'" ' + ( sel_country == countries[country].code ? 'selected':'' ) + ' >'+ countries[country].name +'</option>';
                                        }
                                    } else if( list_visibility_option == 'show' ) {
                                        for (country in countries){
                                            if ( jQuery.inArray(countries[country].code,allowed_countries) != -1 ) {
                                                option_string = option_string + '<option value="'+ countries[country].code +'" ' + ( sel_country == countries[country].code ? 'selected':'' ) + ' >'+ countries[country].name +'</option>';
                                            }
                                        }
                                    } else {
                                        for (country in countries){
                                            option_string = option_string + '<option value="'+ countries[country].code +'" ' + ( sel_country == countries[country].code ? 'selected':'' ) + ' >'+ countries[country].name +'</option>';
                                        }
                                    }

                                    jQuery('select[name="'+ field_name +'"]').html(option_string);
                                </script>
                                <?php
                            } elseif ( 'state' === $each_field ) {
                                $data_label = $field_array['label'];
                                echo '<' . $field_array['type'] . ' name="' . $field_settings['name'] . '[' . $each_field . ']' . '" autocomplete="' . $autocomplete . '" data-required="' . $data_required . '" data-type="' . $data_type . '" data-label="' . $data_label . '">';
                                echo '<option value="">' . esc_html_e( 'Select State', 'wpuf-pro' ) . '</option>';
                                echo '</' . $field_array['type'] . '>';

                                $states = include WPUF_PRO_INCLUDES . '/states.php';
                                //fill for missing country & states
                                $missing_country = wpuf_get_countries();
                                foreach ( $missing_country as $country ) {
                                    if ( ! array_key_exists( $country['code'], $states ) ) {
                                        $states[ $country['code'] ] = [];
                                    }
                                }

                                ksort( $states );

                                foreach ( $states as $country => $state ) {
                                    if ( ! $state ) {
                                        $country_state = ( new CountryState() )->getCountry( $country );
                                        $country_state = $country_state && ! empty( $country_state[6] ) ? $country_state[6] : [];
                                        foreach ( $country_state as $state_key => &$state_name ) {
                                            if ( stripos( $state_key, '\xfc' ) !== false ) { //json parse issue, for german state
                                                $key = ( str_replace( '\xfc', 'W', $state_key ) );
                                                $val = ( str_replace( '\xfc', 'W', $state_name ) );
                                                unset( $country_state[ $state_key ] );
                                                $country_state += [ $key => $val ];
                                            }
                                            $state_name = __( ucfirst( $state_name ), 'wpuf-pro' );
                                        }

                                        $states[ $country ] += count( $country_state ) >= 1 ? $country_state : [];
                                    }
                                }
                                ?>

                                <script>
                                    (function( $ ) {
                                        var states = JSON.parse('<?php echo wp_json_encode( $states, JSON_HEX_APOS ); ?>');
                                        var country_field = $('.country_select').find('select');
                                        setStateOptions( country_field.val() );

                                        country_field.on('change', function(e) {
                                            var country_code = $(this).val();
                                            setStateOptions( country_code );
                                        });

                                        function setStateOptions( country_code ) {
                                            var state_option = '<option value=""><?php esc_html_e( 'Select State', 'wpuf-pro' ); ?></option>';
                                            var select_state = '<?php echo isset( $address_fields_meta['state'] ) ? $address_fields_meta['state'] : ''; ?>';
                                            var field_name   = '<?php echo $field_settings['name'] . '[' . $each_field . ']'; ?>';

                                            for ( state in states[country_code] ) {
                                                state_option = state_option + '<option value="'+ state +'" ' + ( select_state == state ? 'selected':'' ) + ' >'+ states[country_code][state] +'</option>';
                                            }

                                            $('select[name="'+ field_name +'"]').html(state_option);
                                        }
                                    })(jQuery)
                                </script
                            <?php } ?>
                        </div>

                        <label class="wpuf-form-sub-label">
                            <?php echo $field_array['label']; ?>
                            <span class="required"><?php echo ( isset( $field_array['required'] ) && ! empty( $field_array['required'] ) ) ? '*' : ''; ?></span>
                        </label>
                    <?php } ?>
                </div>

            <?php } ?>

            <div style="clear: both"><?php $this->help_text( $field_settings ); ?></div>
        </div>

        <?php
        $this->after_field_print_label();
    }

    /**
     * It's a full width block
     *
     * @return boolean
     */
    public function is_full_width() {
        return true;
    }

    /**
     * Get field options setting
     *
     * @return array
     */
    public function get_options_settings() {
        $default_options = $this->get_default_option_settings();

        $settings = array(
            array(
                'name'          => 'address',
                'title'         => __( 'Address Fields', 'wpuf-pro' ),
                'type'          => 'address',
                'section'       => 'advanced',
                'priority'      => 21,
                'help_text'     => '',
            ),
        );

        return array_merge( $default_options, $settings );
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {
        $defaults = $this->default_attributes();

        $props = array(
            'input_type'        => 'address',
            'address_desc'  => '',
            'address'       => array(
                'street_address'    => array(
                    'checked'       => 'checked',
                    'type'          => 'text',
                    'required'      => 'checked',
                    'label'         => __( 'Address Line 1', 'wpuf-pro' ),
                    'value'         => '',
                    'placeholder'   => '',
                ),

                'street_address2'   => array(
                    'checked'       => 'checked',
                    'type'          => 'text',
                    'required'      => '',
                    'label'         => __( 'Address Line 2', 'wpuf-pro' ),
                    'value'         => '',
                    'placeholder'   => '',
                ),

                'city_name'         => array(
                    'checked'       => 'checked',
                    'type'          => 'text',
                    'required'      => 'checked',
                    'label'         => __( 'City', 'wpuf-pro' ),
                    'value'         => '',
                    'placeholder'   => '',
                ),

                'zip'               => array(
                    'checked'       => 'checked',
                    'type'          => 'text',
                    'required'      => 'checked',
                    'label'         => __( 'Zip Code', 'wpuf-pro' ),
                    'value'         => '',
                    'placeholder'   => '',
                ),

                'country_select'    => array(
                    'checked'                           => 'checked',
                    'type'                              => 'select',
                    'required'                          => 'checked',
                    'label'                             => __( 'Country', 'wpuf-pro' ),
                    'value'                             => '',
                    'country_list_visibility_opt_name'  => 'all',
                    'country_select_hide_list'          => array(),
                    'country_select_show_list'          => array(),
                ),

                'state'             => array(
                    'checked'       => 'checked',
                    'type'          => 'select',
                    'required'      => 'checked',
                    'label'         => __( 'State', 'wpuf-pro' ),
                    'value'         => '',
                    'placeholder'   => '',
                ),
            ),
            'show_in_post'      => 'yes',
            'hide_field_label'  => 'no',
        );

        return array_merge( $defaults, $props );
    }

    /**
     * Prepare entry
     *
     * @param $field
     *
     * @return mixed
     */
    public function prepare_entry( $field ) {
        $entry_value = array();

        if ( isset( $_POST[ $field['name'] ] ) && is_array( $_POST[ $field['name'] ] ) ) {
            foreach ( $_POST[ $field['name'] ] as $address_field => $field_value ) {
                $entry_value[ $address_field ] = sanitize_text_field( $field_value );
            }
        }

        return $entry_value;
    }
}
