<?php
/**
 * Time Field Class
 *
 * @since 3.4.11
 **/
class WPUF_Form_Field_Time extends WPUF_Field_Contract {
    const DEFAULT_TIME_FORMAT = 'g:i a';

    public function __construct() {
        $this->name       = __( 'Time Field', 'wpuf-pro' );
        $this->input_type = 'time_field';
        $this->icon       = 'clock-o';
    }

    /**
     * Render the Time field in the frontend
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
        $time_format = ! empty( $field_settings['time_format'] ) ? esc_html( $field_settings['time_format'] ) : self::DEFAULT_TIME_FORMAT;

        if ( 'custom' === $time_format ) {
            $time_format = ! empty( $field_settings['custom_time_format'] ) ? esc_html( $field_settings['custom_time_format'] ) : self::DEFAULT_TIME_FORMAT;
        }

        if ( ! empty( $post_id ) && 0 !== absint( $post_id ) ) {
            $selected = $this->get_meta( $post_id, $field_settings['name'], $type );
        } else {
            $selected = isset( $field_settings['selected'] ) ? $field_settings['selected'] : '';
        }

        if ( ! empty( $selected ) ) {
            try {
                $selected = ( new DateTimeImmutable( $selected ) )->format( $time_format );
            } catch ( Exception $e ) {
                $selected = ( new DateTimeImmutable() )->format( $time_format );
            }
        }

        $name = $field_settings['name'];

        $this->field_print_label( $field_settings, $form_id ); ?>

        <div class="wpuf-fields">
            <select
                class="<?php echo 'wpuf_' . esc_attr( $name ) . '_' . esc_attr( $form_id ); ?>"
                id="<?php echo esc_attr( $name ) . '_' . esc_attr( $form_id ); ?>"
                name="<?php echo esc_attr( $name ); ?>"
                data-required="<?php echo esc_attr( $field_settings['required'] ); ?>"
                data-type="select">

                <?php if ( ! empty( $field_settings['first_option'] ) ) { ?>
                    <option value="-1"><?php echo esc_html( $field_settings['first_option'] ); ?></option>
                <?php } ?>

                <?php
                $times = $this->generate_times_by_interval( $field_settings );

                if ( ! empty( $times ) ) {
                    foreach ( $times as $key => $value ) {
                        ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $selected, $key ); ?>>
                            <?php echo esc_html( $value ); ?>
                        </option>
                        <?php
                    }
                }
                ?>
            </select>
            <?php $this->help_text( $field_settings ); ?>
        </div>

        <?php
        $this->after_field_print_label();
    }

    /**
     * Get field options setting
     * @since 3.4.11
     *
     * @return array
     */
    public function get_options_settings() {
        $default_options = $this->get_default_option_settings( true, [ 'width' ] );

        $settings = [
            [
                'name'      => 'time_format',
                'title'     => __( 'Time Format', 'wpuf-pro' ),
                'type'      => 'radio',
                'options'   => [
                    'g:i a'  => '3:20 pm (g:i a)',
                    'H:i:s'  => '15:20 (H:i:s)',
                    'custom' => __( 'Custom (PHP time format)', 'wpuf-pro' ),
                ],
                'section'   => 'advanced',
                'priority'  => 23,
                'inline'    => false,
                'help_text' => __( 'Check this option to set the time format.', 'wpuf-pro' ),
            ],
            [
                'name'          => 'custom_time_format',
                'type'          => 'text',
                'section'       => 'advanced',
                'priority'      => 23,
                'dependencies'  => [
                    'time_format' => 'custom',
                ],
            ],
            [
                'name'      => 'time_intervals',
                'title'     => __( 'Time Intervals (in minutes)', 'wpuf-pro' ),
                'type'      => 'text',
                'variation' => 'number',
                'section'   => 'advanced',
                'priority'  => 23,
                'help_text' => __( 'The interval between times in minutes. Default is 60', 'wpuf-pro' ),
            ],
            [
                'name'          => 'first_option',
                'title'         => __( 'First Select Text', 'wpuf-pro' ),
                'type'          => 'text',
                'section'       => 'basic',
                'priority'      => 13,
                'help_text'     => __( 'First element of the time dropdown. Leave this empty if you don\'t want to show this field', 'wpuf-pro' ),
            ],
        ];

        return array_merge( $default_options, $settings );
    }

    /**
     * Get the field props for this Vue Component
     * @since 3.4.11
     *
     * @return array
     */
    public function get_field_props() {
        $defaults = $this->default_attributes();

        $props = [
            'input_type'       => 'time',
            'show_in_post'     => 'yes',
            'hide_field_label' => 'no',
            'first_option'     => __( '- select -', 'wpuf-pro' ),
            'time_format'      => self::DEFAULT_TIME_FORMAT,
        ];

        return array_merge( $defaults, $props );
    }

    /**
     * Generates time slot based on start, end time and defined slot duration
     *
     * @since 3.4.11
     *
     * @param $field_settings array
     *
     * @return array
     */
    private function generate_times_by_interval( $field_settings ) {
        $interval = ! empty( $field_settings['time_intervals'] ) ? absint( $field_settings['time_intervals'] ) : 60;
        $format = ! empty( $field_settings['time_format'] ) ? esc_html( $field_settings['time_format'] ) : self::DEFAULT_TIME_FORMAT;
        $start = isset( $field_settings['start_time'] ) ? esc_html( $field_settings['start_time'] ) : '12:00 AM';
        $end = isset( $field_settings['end_time'] ) ? esc_html( $field_settings['end_time'] ) : '11:59 PM';

        if ( 'custom' === $format ) {
            $format = ! empty( $field_settings['custom_time_format'] ) ? esc_html( $field_settings['custom_time_format'] ) : self::DEFAULT_TIME_FORMAT;
        }

        $time       = [];
        $date       = wpuf_current_datetime();
        $start_date = $date->modify( $start );
        $end_date   = $date->modify( $end );

        try {
            $interval = new DateInterval( 'PT' . intval( $interval ) . 'M' );
        } catch ( Exception $e ) {
            return $time;
        }

        while ( $start_date < $end_date ) {
            $start          = $start_date->format( $format );
            $start_date     = $start_date->add( $interval );
            $time[ $start ] = $start;
        }

        return $time;
    }
}
