<?php
/**
 * Phone Field Class
 *
 * @since 3.4.11
 **/
class WPUF_Form_Field_Phone extends WPUF_Field_Contract {

    public function __construct() {
        $this->name       = __( 'Phone Field', 'wpuf-pro' );
        $this->input_type = 'phone_field';
        $this->icon       = 'phone';
    }

    /**
     * Render the Phone field in the frontend
     *
     * @since 3.4.11
     *
     * @param array   $field_settings
     * @param integer $form_id
     * @param string  $type
     * @param integer $post_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id, $type = 'post', $post_id = null ) {
        $attr = $field_settings;
        $value = '';

        if ( ! empty( $post_id ) ) {
            if ( $this->is_meta( $attr ) ) {
                $value = $this->get_meta( $post_id, $attr['name'], $type );
            }
        } else {
            $value = $attr['default'];
        }

        $this->field_print_label( $attr, $form_id ); ?>

        <style>
            .iti--allow-dropdown.has-error .iti__selected-flag {
                height: 70%;
                padding-left: 16px;
            }
            // field specific style to override our default padding
            .iti--allow-dropdown input, .iti--allow-dropdown input[type=text], .iti--allow-dropdown input[type=tel], .iti--separate-dial-code input, .iti--separate-dial-code input[type=text], .iti--separate-dial-code input[type=tel] {
                padding-right: 6px !important;
                padding-left: 52px !important;
            }

        </style>
        <div class="wpuf-fields">
            <input
                class="wpuf_telephone text <?php echo 'wpuf_' . $attr['name'] . '_' . $form_id; ?>"
                id="<?php echo $attr['name'] . '_' . $form_id; ?>"
                type="text"
                data-required="<?php echo $attr['required']; ?>"
                data-label="<?php echo $attr['label']; ?>"
                data-show-list="<?php echo $attr['show_country_list']; ?>"
                data-type="text"
                name="<?php echo esc_attr( $attr['name'] ); ?>"
                placeholder="<?php echo esc_attr( $attr['placeholder'] ); ?>"
                value="<?php echo esc_attr( $value ); ?>"
                size="<?php echo esc_attr( $attr['size'] ); ?>" />
            <?php $this->help_text( $attr ); ?>
        </div>

        <?php
        $show_list = 'no';

        if ( ! empty( $attr['show_country_list'] ) && 'yes' === $attr['show_country_list'] ) {
            $show_list = $attr['show_country_list'];
        }
        ?>
        <script>
            window.addEventListener('DOMContentLoaded', (event) => {
                ;(function ($) {
                    let showList = "<?php echo $show_list; ?>";
                    let defaultCountry = "<?php echo ! empty( $attr['country_list']['name'] ) ? $attr['country_list']['name'] : ''; ?>";
                    let onlyCountries = <?php echo ! empty( $attr['country_list']['country_select_show_list'] ) ? wp_json_encode( $attr['country_list']['country_select_show_list'] ) : "''"; ?>;
                    let excludeCountries = <?php echo ! empty( $attr['country_list']['country_select_hide_list'] ) ? wp_json_encode( $attr['country_list']['country_select_hide_list'] ) : "''"; ?>;
                    let autoPlaceholder = "<?php echo ! empty( $attr['auto_placeholder'] ) ? $attr['auto_placeholder'] : ''; ?>";

                    let utilsScript = "<?php echo WPUF_PRO_ROOT_URI . '/includes/libs/intl-tel-input/js/utils.js'; ?>";

                    if ( 'yes' === showList ) {
                        let tempTelObj = {
                            utilsScript: utilsScript
                        };

                        if ( '' !== defaultCountry ) {
                            tempTelObj.initialCountry = defaultCountry;
                        }

                        if ( '' !== onlyCountries ) {
                            tempTelObj.onlyCountries = onlyCountries;
                        }

                        if ( '' !== excludeCountries ) {
                            tempTelObj.excludeCountries = excludeCountries;
                        }

                        if ( 'no' === autoPlaceholder ) {
                            tempTelObj.autoPlaceholder = 'off';
                        }

                        let fieldId = "<?php echo $field_settings['name'] . '_' . $form_id; ?>";
                        let input = document.getElementById(fieldId); // intlTelInput not works properly if we use jQuery selector
                        window.intlTelInput(input, tempTelObj);
                    }
                })(jQuery);
            });
        </script>

        <?php
        $this->after_field_print_label();
    }

    /**
     * Get field options setting
     *
     * @since 3.4.11
     *
     * @return array
     */
    public function get_options_settings() {
        $default_options = $this->get_default_option_settings();
        $text_options    = $this->get_default_text_option_settings();

        $settings = [
            [
                'name'      => 'show_country_list',
                'title'     => __( 'Show Country List', 'wpuf-pro' ),
                'type'      => 'radio',
                'options'   => [
                    'yes' => __( 'Yes', 'wpuf-pro' ),
                    'no'  => __( 'No', 'wpuf-pro' ),
                ],
                'inline'    => true,
                'default'   => 'yes',
                'section'   => 'advanced',
                'priority'  => 23,
                'help_text' => __( 'Select yes to show the country selection dropdown.', 'wpuf-pro' ),
            ],
            [
                'name'      => 'auto_placeholder',
                'title'     => __( 'Auto Placeholder', 'wpuf-pro' ),
                'type'      => 'radio',
                'options'   => [
                    'yes' => __( 'Yes', 'wpuf-pro' ),
                    'no'  => __( 'No', 'wpuf-pro' ),
                ],
                'inline'    => true,
                'default'   => 'yes',
                'section'   => 'advanced',
                'priority'  => 23.1,
                'help_text' => __( 'Set the input\'s placeholder to an example number for the selected country, and update it if the country changes.', 'wpuf-pro' ),
                'dependencies'  => [
                    'show_country_list' => 'yes',
                ],
            ],
            [
                'name'          => 'country_list',
                'title'         => '',
                'type'          => 'country-list',
                'section'       => 'advanced',
                'priority'      => 23.2,
                'dependencies'  => [
                    'show_country_list' => 'yes',
                ],
                'help_text' => __( 'You must include the Default Country if you choose "Only Show These" option', 'wpuf-pro' ),
            ],
        ];

        return array_merge( $default_options, $text_options, $settings );
    }

    /**
     * Get the field props for this Vue Component
     *
     * @since 3.4.11
     *
     * @return array
     */
    public function get_field_props() {
        $defaults = $this->default_attributes();

        $props = [
            'show_country_list' => 'yes',
            'auto_placeholder'  => 'yes',
            'input_type'        => 'country_list',
            'country_list'  => [
                'name'                              => '',
                'country_list_visibility_opt_name'  => 'all', // all, hide, show
                'country_select_show_list'          => [],
                'country_select_hide_list'          => [],
            ],
            'show_in_post'      => 'yes',
            'hide_field_label'  => 'no',
            'is_meta'           => 'yes',
            'id'                => 0,
            'is_new'            => true,
        ];

        return array_merge( $defaults, $props );
    }
}
