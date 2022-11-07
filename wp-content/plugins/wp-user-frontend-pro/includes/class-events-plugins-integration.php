<?php
/**
 * Events Plugins Integration Class for WPUF
 *
 * @since 2.9
 */
class WPUF_Events_Plugins_Integration {

    public function __construct() {
        add_action( 'wpuf_add_post_form_bottom', array( $this, 'add_fields' ), 10, 2 );
        add_action( 'wpuf_edit_post_form_bottom', array( $this, 'add_fields_update' ), 10, 3 );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 10, 1 );
    }

    /**
     * Add fields on post form bottom
     *
     * @param  int $form_id
     * @param  int $form_settings
     *
     * @return void
     */
    public function add_fields( $form_id, $form_settings ) {
        $post_type = isset( $form_settings['post_type'] ) ? $form_settings['post_type'] : 'post';

        if ( $this->is_tribe_event_form( $post_type ) ) {
            if ( $this->is_edit_page() ) {
                $post_id = isset( $_GET['pid'] ) ? sanitize_key( wp_unslash( $_GET['pid'] ) ) : '';

                update_post_meta( $post_id, 'venue', 'find_a_venue' );
                update_post_meta( $post_id, 'organizer', 'find_organizer' );
            }

            $this->the_events_calendar_fields( $form_id, $form_settings );
        }
    }

    /**
     * Add fields on post update form
     *
     * @param  int $form_id
     * @param  int $post_id
     * @param  int $form_settings
     *
     * @return void
     */
    public function add_fields_update( $form_id, $post_id, $form_settings ) {
        $this->add_fields( $form_id, $form_settings );
    }

    /**
     * Check if the form is created for the events calendar plugin
     *
     * @param  $post_type
     *
     * @return boolean
     */
    public function is_tribe_event_form( $post_type ) {
        if ( $post_type === 'tribe_events' ) {
            return true;
        }
        return false;
    }

    /**
     * The event calendar fields
     *
     * @param  int $form_id
     * @param  int $form_settings
     *
     * @return void
     */
    public function the_events_calendar_fields( $form_id, $form_settings ) {
        $this->the_events_calendar_venues( $form_id );
        $this->the_events_calendar_organizers( $form_id );
    }

    /**
     * Check if the form has the given field
     *
     * @param  $field_name
     * @param  $form_id
     *
     * @return boolean
     */
    public function field_exists( $field_name, $form_id ) {
        $fields = wpuf_get_form_fields( $form_id );

        foreach ( $fields as $key => $value ) {
            if ( isset( $value['name'] ) && ! empty( $value['name'] ) ) {
                if ( $value['name'] === $field_name ) {
                    return true;
                }
            }
        }

        return;
    }

    /**
     * Check if the current page is edit page
     *
     * @return boolean
     */
    public function is_edit_page() {
        $post_id = isset( $_GET['pid'] ) ? sanitize_key( wp_unslash( $_GET['pid'] ) ) : '';

        if ( ! empty( $post_id ) ) {
            return true;
        }
        return;
    }

    /**
     * Get the events calendar venues
     *
     * @param  $form_id
     *
     * @return void
     */
    public function the_events_calendar_venues( $form_id ) {
        if ( ! $this->field_exists( '_EventVenueID', $form_id ) ) {
            return;
        }

        $venue_id = '';

        if ( $this->is_edit_page() ) {
            $post_id    = isset( $_GET['pid'] ) ? sanitize_key( wp_unslash( $_GET['pid'] ) ) : '';
            $venue_id   = get_post_meta( $post_id, '_EventVenueID', true );
        }

        $this->handle_options( 'tribe_venue', $venue_id, '.wpuf-form-add select[name="_EventVenueID"]' );
    }

    /**
     * Get the events calendar organizers
     *
     * @param  $form_id
     *
     * @return void
     */
    public function the_events_calendar_organizers( $form_id ) {
        if ( ! $this->field_exists( '_EventOrganizerID', $form_id ) ) {
            return;
        }

        $organizer_id = '';

        if ( $this->is_edit_page() ) {
            $post_id        = isset( $_GET['pid'] ) ? sanitize_key( wp_unslash( $_GET['pid'] ) ) : '';
            $organizer_id   = get_post_meta( $post_id, '_EventOrganizerID', true );
        }

        $this->handle_options( 'tribe_organizer', $organizer_id, '.wpuf-form-add select[name="_EventOrganizerID"]' );
    }


    /**
     * Get related post title and id to pass in select field as options
     *
     * @return array $posts
     *
     * @since 2.9.0
     */
    public function get_posts( $post_type ) {
        $args = array(
            'post_type'         => $post_type,
            'post_status'       => 'publish',
            'orderby'           => 'DESC',
            'order'             => 'ID',
            'posts_per_page'    => -1,
        );

        $query = new WP_Query( $args );

        $posts = array();

        if ( $query->have_posts() ) {
            $i = 0;

            while ( $query->have_posts() ) {
                $query->the_post();

                $post = $query->posts[ $i ];

                $settings = get_post_meta( get_the_ID(), 'wpuf_form_settings', true );

                $posts[ $post->ID ] = $post->post_title;

                $i++;
            }
        }

        return $posts;
    }

    public function enqueue_scripts() {
        wp_enqueue_script( 'jquery-ui-timepicker', WPUF_PRO_ASSET_URI . '/js/jquery-ui-timepicker-addon.js', [ 'jquery-ui-datepicker' ], WPUF_PRO_VERSION );
    }

    /**
     * Handle options for venue, organizer in add,edit option
     *
     * @param $post_type
     * @param string $meta_id
     * @param $selector
     *
     * @return void
     */
    private function handle_options( $post_type, $meta_id, $selector ){?>

        <script type="text/javascript">
            var selector           = document.querySelector(<?php echo $selector; ?>);
                selector.innerHTML = '';

            <?php foreach ( $this->get_posts( $post_type ) as $option_id => $option_text ) : ?>
                var option          = document.createElement("option");
                    option.text     = "<?php echo $option_text; ?>";
                    option.value    = "<?php echo $option_id; ?>";

                    <?php if ( ! empty( $meta_id ) ) : ?>
                        <?php if ( (int) $meta_id === (int) $option_id ) : ?>
                            option.setAttribute( 'selected', 'selected' );
                        <?php endif; ?>
                    <?php endif; ?>

                selector.add(option);
            <?php endforeach; ?>
        </script>

        <?php
    }
}
