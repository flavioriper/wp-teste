<?php
/**
 * Shortcode Field Class
 *
 * @since 3.1.0
 **/
class WPUF_Form_Field_Shortcode extends WPUF_Field_Contract {

    public function __construct() {
        $this->name       = __( 'Shortcode', 'wpuf-pro' );
        $this->input_type = 'shortcode';
        $this->icon       = 'file-code-o';
    }

    /**
     * Render the Shortcode field
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
        $hide_label = isset( $field_settings['hide_field_label'] ) && wpuf_validate_boolean( $field_settings['hide_field_label'] );
        ?>
            <li <?php $this->print_list_attributes( $field_settings ); ?>>

                <?php
                if ( ! $hide_label ) {
                    $this->field_print_label( $field_settings, $form_id );
                }
                ?>

                <div class="wpuf-fields <?php echo ' wpuf_' . $field_settings['name'] . '_' . $form_id; ?>">
                    <?php echo do_shortcode( $field_settings['shortcode'] ); ?>
                </div>
            </li>
        <?php
    }

    /**
     * Get field options setting
     *
     * @return array
     */
    public function get_options_settings() {
        $settings = [
            [
                'name'      => 'label',
                'title'     => __( 'Field Label', 'wpuf-pro' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 10,
                'help_text' => __( 'Enter a title of this field', 'wpuf-pro' ),
            ],
            [
                'name'      => 'shortcode',
                'title'     => __( 'Shortcode', 'wpuf-pro' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 10,
                'help_text' => __( 'Input your shortcode here', 'wpuf-pro' ),
            ],
            [
                'name'      => 'hide_field_label',
                'title'     => __( 'Hide Field Label in Post', 'wpuf-pro' ),
                'type'      => 'radio',
                'options'   => [
                    'yes'   => __( 'Yes', 'wpuf-pro' ),
                    'no'    => __( 'No', 'wpuf-pro' ),
                ],
                'section'   => 'advanced',
                'priority'  => 24,
                'default'   => 'no',
                'inline'    => true,
                'help_text' => __( 'Select Yes if you want to hide the field label in single post.', 'wpuf-pro' ),
            ],
        ];

        return $settings;
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {
        return array(
            'input_type'        => 'shortcode',
            'template'          => $this->get_type(),
            'label'             => $this->get_name(),
            'shortcode'         => '[your_shortcode]',
            'id'                => 0,
            'is_new'            => true,
            'is_meta'           => 'yes',
            'wpuf_cond'         => null,
            'hide_field_label'  => 'no',
        );
    }
}
