<?php
namespace WPUF\UserDirectory;

/**
 * Class ShortCode
 */
class ShortCode {
    public $per_page;
    public $settings;
    /**
     * Constructor for the ShortCode class
     */
    public function __construct() {
        add_action( 'wpuf_ud_profile_about', array( $this, 'user_directory_profile' ) );
        add_filter( 'wpuf_page_shortcodes', array( $this, 'wpuf_add_user_listing_shortcode' ) );
        $this->init_short_codes();
    }

    /**
     * Init short code
     *
     * @return void
     */
    public function init_short_codes() {
        add_shortcode( 'wpuf_user_listing', [ $this, 'user_lists' ] );
    }

    /**
     * Show user lists
     *
     * @return void
     */
    public function user_lists( $atts ) {
        $atts = shortcode_atts(
            array(
                'role'          => 'all',
                'per_page'      => '6',
                'roles_exclude' => '',
                'roles_include' => '',
            ), $atts
        );

        $all_data = [];

        global $wp;
        $user_id             = isset( $_GET['user_id'] ) ? absint( wp_unslash( $_GET['user_id'] ) ) : '';
        $this->page_url      = get_permalink();
        $this->per_page      = $atts['per_page'];
        $this->roles_exclude = $atts['roles_exclude'];
        $this->roles_include = $atts['roles_include'];
        $users               = $this->get_all_user( $atts['role'] );

        $all_data['user_id'] = $user_id;
        $all_data['users']   = $users;

        ob_start();

        if ( $user_id ) {
            $user             = get_user_by( 'ID', $user_id );
            $all_data['user'] = $user;

            wpuf_load_pro_template( 'show-profile.php', $all_data, WPUF_UD_TEMPLATES . '/' );

            return ob_get_clean();
        }

        wpuf_load_pro_template( 'user-lists.php', $all_data, WPUF_UD_TEMPLATES . '/' );

        return ob_get_clean();
    }

    /**
     * Get all uploaded files of a specific user
     *
     * @since 1.1.2
     *
     * @param $user_id
     *
     * @return array|void
     */
    public function get_user_uploaded_files( $user_id ) {
        if ( ! absint( $user_id ) ) {
            return;
        }

        $args = [
            'author'      => $user_id,
            'numberposts' => -1,
            'post_type'   => 'attachment',
            'post_status' => 'inherit',
        ];

        $files = get_posts( $args );
        $attachments = [];

        if ( ! count( $files ) ) {
            return $attachments;
        }

        foreach ( $files as $file ) {
            if ( 0 === $file->post_parent ) {
                continue;
            }

            $wpuf_form_id = get_post_meta( $file->post_parent, '_wpuf_form_id', true );

            if ( $wpuf_form_id ) {
                $attachments[] = wp_get_attachment_url( $file->ID );
            }
        }

        return $attachments;
    }

    /**
     * Show the user files and images in single user about tab
     *
     * @param $field
     * @param $user_id
     *
     * @return void
     */
    public function user_file( $field, $user_id ) {
        $images = get_user_meta( $user_id, $field['meta'] );
        $image_size = wpuf_get_option( 'pro_img_size', 'user_directory', '78' );

        echo '<div class="wpuf-profile-value">';

        if ( $images ) {
            echo '<ul class="wpuf-profile-gallery">';

            foreach ( $images as $attachment_id ) {
                $file_url   = wp_get_attachment_url( $attachment_id );
                $filename   = basename( get_attached_file( $attachment_id ) );
                $ext        = explode( '.', $filename );
                $ext        = end( $ext );
                $image_size = wpuf_get_image_sizes_array( $image_size );
                $image_size = $image_size['width'];

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
                        $icon  = 'doc.svg';
                        $image = true;
                        break;
                    default:
                        $icon = 'file.svg';
                        break;
                }
                ?>
                <?php if ( $image ) : ?>
                    <div>
                        <a href="<?php echo $file_url; ?>">
                            <img style="width: <?php echo $image_size; ?>px;" src="<?php echo $file_url; ?>" class="preview-image">
                        </a>
                    </div>
                <?php else : ?>
                    <div class="single-file">
                        <a href="<?php echo $file_url; ?>">
                            <img style="width: <?php echo $image_size; ?>px;" src="<?php echo WPUF_UD_ASSET_URI . '/images/' . $icon; ?>" >
                        </a>
                    </div>
                    <?php
                endif;

                // printf( '<li><a href="%s" target="_blank">%s</a></li>', $full_size, $filename );
            }

            echo '</ul>';
        } else {
            esc_html_e( 'Nohting found!', 'wpuf-pro' );
        }

        echo '</div>';
    }

    /**
     * Show the user social icon in single user about tab
     *
     * @param $field
     * @param $userdata
     *
     * @return void
     */
    public function social_list( $field, $userdata ) {
        echo '<ul class="wpuf-social-links wpuf-profile-value">';
        foreach ( $field['social_icon'] as $key => $icon ) {
            $user_data  = $userdata->data;
            $social_key = $field['social_url'][ $key ];
            $url        = get_user_meta( $user_data->ID, $social_key, true );

            // don't show empty urls
            if ( empty( $url ) ) {
                continue;
            }

            ?>
            <li>
                <a href="<?php echo esc_url( $url ); ?>" target="_blank"><img alt="social icon" src="<?php echo esc_url( $icon ); ?>"></a>
            </li>
            <?php
        }
        echo '</ul>';
    }

    /**
     * Get pagination
     *
     * @param integer $total_posts
     *
     * @param integer $perpage
     *
     * @return void
     */
/*    public function pagination( $total_posts, $perpage ) {
        $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
        $num_of_pages = ceil( $total_posts / $perpage );
        $page_links = paginate_links(
            array(
                'base'      => add_query_arg( 'pagenum', '%#%' ),
                'format'    => '',
                'prev_text' => '<svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M0.248874 7.05115L7.19193 0.244361C7.35252 0.086801 7.56688 0 7.79545 0C8.02403 0 8.23839 0.086801 8.39898 0.244361L8.91029 0.745519C9.243 1.07208 9.243 1.60283 8.91029 1.9289L3.08003 7.64483L8.91675 13.3671C9.07734 13.5247 9.166 13.7347 9.166 13.9587C9.166 14.1829 9.07734 14.3929 8.91675 14.5506L8.40545 15.0517C8.24474 15.2092 8.0305 15.296 7.80192 15.296C7.57335 15.296 7.35898 15.2092 7.1984 15.0517L0.248874 8.23864C0.0879093 8.08058 -0.000500916 7.86955 2.13498e-06 7.64521C-0.000500916 7.42 0.0879093 7.20909 0.248874 7.05115Z" fill="#545D7A"/>
            </svg>',
                'next_text' => '<svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M8.97963 7.05115L2.03657 0.244361C1.87599 0.086801 1.66162 0 1.43305 0C1.20448 0 0.99011 0.086801 0.829525 0.244361L0.318217 0.745519C-0.0144943 1.07208 -0.0144943 1.60283 0.318217 1.9289L6.14847 7.64483L0.311748 13.3671C0.151164 13.5247 0.0625 13.7347 0.0625 13.9587C0.0625 14.1829 0.151164 14.3929 0.311748 14.5506L0.823056 15.0517C0.983767 15.2092 1.19801 15.296 1.42658 15.296C1.65515 15.296 1.86952 15.2092 2.0301 15.0517L8.97963 8.23864C9.14059 8.08058 9.229 7.86955 9.2285 7.64521C9.229 7.42 9.14059 7.20909 8.97963 7.05115Z" fill="#545D7A"/>
            </svg>',
                'total'     => $num_of_pages,
                'current'   => $pagenum,
            )
        );

        if ( $page_links ) {
            return '<div class="wpuf-pagination">' . $page_links . '</div>';
        }
    }*/

    /**
     * Return user status
     *
     * @return boolean
     */
    public function is_approved( $user_id ) {
        $user_status = get_user_meta( $user_id, 'wpuf_user_status', true );

        if ( empty( $user_status ) || $user_status === 'approved' ) {
            return true;
        }
        return false;
    }

    public static function can_user_see( $profile_role, $field, $user_role ) {

        // bail out if the current user role is not in the list
        if ( ! in_array( $profile_role, $field['all_user_role'], true ) ) {
            return false;
        }

        // check viewer role
        if ( ! in_array( $user_role, $field['current_user_role'], true ) ) {
            return false;
        }

        return true;
    }

    public function get_options() {
        return get_option( 'wpuf_userlisting', array() );
    }

    public function is_sef_url_active() {
        global $wp_rewrite;

        if ( empty( $wp_rewrite->permalink_structure ) ) {
            return false;
        }

        return true;
    }

    public function user_listing_search() {
        $search_meta       = $this->search_meta_field();
        $search_by         = isset( $_GET['search_by'] ) ? sanitize_text_field( wp_unslash( $_GET['search_by'] ) ) : '';
        $orderby           = isset( $_GET['order_by'] ) ? sanitize_text_field( wp_unslash( $_GET['order_by'] ) ) : 'login';
        $order             = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'ASC';
        $search_query      = isset( $_GET['search_field'] ) ? sanitize_text_field( wp_unslash( $_GET['search_field'] ) ) : '';

        $orderby_parameters = array(
            'user_login'      => __( 'User Login', 'wpuf-pro' ),
            'ID'              => __( 'User ID', 'wpuf-pro' ),
            'display_name'    => __( 'Display Name', 'wpuf-pro' ),
            'user_name'       => __( 'User Name', 'wpuf-pro' ),
            'user_nicename'   => __( 'Nicename', 'wpuf-pro' ),
            'user_registered' => __( 'Registered Date', 'wpuf-pro' ),
            'post_count'      => __( 'Post Count', 'wpuf-pro' ),
        );

        $order_parameters = array(
            'ASC'  => __( 'ASC', 'wpuf-pro' ),
            'DESC' => __( 'DESC', 'wpuf-pro' ),
        );

        ?>
        <form method="get" action="">
            <?php
            if ( ! $this->is_sef_url_active() ) {
                ?>
                    <input type="hidden" value="<?php the_ID(); ?>" name="page_id">
                <?php } ?>

            <label>
                <?php esc_attr_e( 'Search by: ', 'wpuf-pro' ); ?>
                <select class="search-by" name="search_by">
                    <option value="all"><?php esc_html_e( '- all -', 'wpuf-pro' ); ?></option>
                    <?php
                    foreach ( $search_meta as $meta_key => $label ) {
                        ?>
                        <option value="<?php echo esc_attr( $meta_key ); ?>" <?php echo $meta_key === $search_by ? 'selected="selected"' : ''; ?>><?php echo esc_attr( $label ); ?></option>
                        <?php
                    }
                    ?>
                </select>
                <?php esc_attr_e( 'Orderby: ', 'wpuf-pro' ); ?>
                <select class="wpuf-users-order-by" name="order_by">
                    <?php foreach ( $orderby_parameters as $key => $label ) : ?>
                        <option value="<?php echo $key; ?>" <?php echo $key === $orderby ? 'selected="selected"' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach ?>
                </select>
                <?php esc_attr_e( 'Order: ', 'wpuf-pro' ); ?>
                <select class="wpuf-users-order" name="order">
                    <?php foreach ( $order_parameters as $key => $label ) : ?>
                        <option value="<?php echo $key; ?>" <?php echo $key === $order ? 'selected="selected"' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach ?>
                </select>
                <input type="text" placeholder="<?php esc_attr_e( 'Search here', 'wpuf-pro' ); ?>" name="search_field" value="<?php echo $search_query; ?>">
            </label>
            <input type="submit" class="button" name="wpuf_user_search" value="<?php esc_attr_e( 'Search', 'wpuf-pro' ); ?>">
        </form>

        <?php
    }

    public function search_meta_field() {
        $user_meta      = $this->get_options();
        $this->settings = isset( $user_meta['settings'] ) ? $user_meta['settings'] : array();

        $search_meta = array();
        if ( $user_meta ) {
            foreach ( $user_meta['fields'] as $key => $val ) {
                if ( $val['type'] === 'meta' && ( isset( $val['search_by'] ) && $val['search_by'] === 'yes' ) ) {
                    $meta               = $this->get_meta( $val );
                    $search_meta[ $meta ] = $val['label'];
                }
            }
        }

        if ( ! $search_meta ) {
            $search_meta = array(
                'user_login'   => __( 'Username', 'wpuf-pro' ),
                'display_name' => __( 'Name', 'wpuf-pro' ),
            );
        }

        return $search_meta;
    }

    /**
     * This function will return the columns to show from user listing builder
     * default Username and Name will be returned if no item is set to show
     *
     * @return array
     */
    public function unique_meta_field() {
        $user_meta      = $this->get_options();
        $this->settings = isset( $user_meta['settings'] ) ? $user_meta['settings'] : array();

        $unique_meta = array();
        if ( $user_meta ) {
            foreach ( $user_meta['fields'] as $key => $val ) {
                if ( 'meta' === $val['type'] && ( isset( $val['in_table'] ) && 'yes' === $val['in_table'] ) ) {
                    $meta               = $this->get_meta( $val );
                    $unique_meta[ $meta ] = $val['label'];
                }
            }
        }

        if ( ! $unique_meta ) {
            $unique_meta = array(
                'user_login'   => __( 'Username', 'wpuf-pro' ),
                'display_name' => __( 'Name', 'wpuf-pro' ),
            );
        }

        return $unique_meta;
    }

    /**
     * Get an url of supplied users profile
     *
     * @since 1.1.2
     * @since 3.4.11 $query_args introduced for additional query arguments
     *
     * @param int $user_id
     * @param array $query_args
     *
     * @return string New URL query string (unescaped).
     */
    public function get_user_link( $user_id, $query_args = [] ) {
        $default = [
            'user_id' => $user_id,
        ];

        $args = wp_parse_args( $default, $query_args );

        return add_query_arg( $args, $this->page_url );
    }

    public function get_meta( $val ) {
        return $val['meta'];
    }

    public function get_all_user( $user_role ) {
        $meta_user_results = array();
        $all_users         = array();
        $pagenum           = isset( $_GET['pagenum'] ) ? absint( wp_unslash( $_GET['pagenum'] ) ) : 1;
        $offset            = ( $pagenum - 1 ) * $this->per_page;
        $orderby           = isset( $_GET['order_by'] ) ? sanitize_text_field( wp_unslash( $_GET['order_by'] ) ) : 'login';
        $order             = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'ASC';

        $args = array(
            'count_total'   => true,
            'number'        => $this->per_page,
            'offset'        => $offset,
            'role'          => 'all' !== $user_role ? $user_role : '',
            'role__not_in'  => explode( ',', $this->roles_exclude ),
            'orderby'       => $orderby,
            'order'         => $order,
        );

        if ( $this->roles_include ) {
            $args['role__in'] = explode( ',', $this->roles_include );
        }

        if ( ! empty( $_GET['search_field'] ) ) {
            $search_query = sanitize_text_field( wp_unslash( $_GET['search_field'] ) );
            $search_by = isset( $_GET['search_by'] ) ? sanitize_text_field( wp_unslash( $_GET['search_by'] ) ) : '';

            if ( 'all' !== $search_by && in_array( $search_by, array( 'ID', 'user_login', 'user_nicename', 'user_email', 'user_url', 'display_name' ), true ) ) {
                $args['search']         = '*' . $search_query . '*';
                $args['search_columns'] = array( $search_by );
            } elseif ( 'all' !== $search_by && ! in_array( $search_by, array( 'ID', 'user_login', 'user_nicename', 'user_email', 'user_url', 'display_name' ), true ) ) {
                $args['meta_query'] = array(
                    array(
                        'key'     => $search_by,
                        'value'   => $search_query,
                        'compare' => 'LIKE',
                    ),
                );
            } else {
                // search in default user fields
                $search_user = get_users( array( 'search' => '*' . $search_query . '*' ) );

                if ( ! empty( $search_user ) ) {
                    $args['search'] = '*' . $search_query . '*';
                } else {
                    // search in user meta keys if the data not found in default fields
                    global $wpdb;

                    $select         = "SELECT distinct $wpdb->usermeta.meta_key FROM $wpdb->usermeta";
                    $user_meta_keys = $wpdb->get_results( $select );

                    $args['meta_query']['relation'] = 'OR';

                    foreach ( $user_meta_keys as $meta_key ) {
                        $args['meta_query'][] = array(
                            'key'     => $meta_key->meta_key,
                            'value'   => $search_query,
                            'compare' => 'LIKE',
                        );
                    }
                }
            }
        }

        //only user query
        $users        = new \WP_User_Query( $args );
        $users_total  = $users->total_users;
        $user_results = $users->get_results();

        //insersection meta and user query result
        foreach ( $user_results as $user_obj ) {
            $role = reset( $user_obj->roles );

            //filter user role
            if ( $user_role !== 'all' && $role !== strtolower( $user_role ) ) {
                continue;
            }

            $all_users[ $user_obj->ID ] = $user_obj;
        }

        unset( $args['number'] );
        unset( $args['offset'] );
        $users        = new \WP_User_Query( $args );
        $this->total = $users->total_users;

        return $all_users;
    }

    public function unique_meta_file() {
        $user_meta = $this->get_options();

        foreach ( $user_meta['fields'] as $key => $val ) {
            if ( $val['type'] === 'file' ) {
                $meta = $this->get_meta_file( $val );
            }
        }

        return $meta;
    }

    public function get_meta_file( $val ) {
        foreach ( $val as $key => $meta ) {
            if ( $key === 'meta_key' && ! empty( $meta ) && $val['type'] === 'file' ) {
                return $meta;
            } elseif ( $key === 'default_meta' && ! empty( $meta ) && $val['type'] === 'file' ) {
                return $meta;
            }
        }
    }

    public function sort_meta_field() {
        $user_meta      = $this->get_options();
        $this->settings = isset( $user_meta['settings'] ) ? $user_meta['settings'] : array();

        $sort_meta = array();
        foreach ( $user_meta['fields'] as $key => $val ) {
            if ( $val['type'] === 'meta' && ( isset( $val['sort_by'] ) && $val['sort_by'] === 'yes' ) ) {
                $meta               = $this->get_meta( $val );
                $sort_meta[ $meta ] = $val['label'];
            }
        }

        if ( ! $sort_meta ) {
            $sort_meta = array(
                'user_login'   => __( 'Username', 'wpuf-pro' ),
                'display_name' => __( 'Name', 'wpuf-pro' ),
            );
        }

        return $sort_meta;
    }

    public function wpuf_add_user_listing_shortcode( $array ) {
        $array['user-listing'] = array(
            'title'   => __( 'User Listing', 'wpuf-pro' ),
            'content' => '[wpuf_user_listing]',
        );
        return $array;
    }

    /**
     * Get user meta fields
     *
     * @param string $type
     *
     * @return array
     */
    public function get_fields( $type ) {
        $fields = get_option( 'wpuf_userlisting' );

        $fields = isset( $fields['fields'] ) ? $fields['fields'] : [];

        $fields = array_filter(
            $fields, function( $field ) use ( $type ) {
                if ( $field['type'] == $type ) {
                    return $field;
                }
            }
        );

        return $fields;
    }

    /**
     * Get user posts
     *
     * @param integer $user_id
     *
     * @return array
     */
    public function get_user_posts( $user_id ) {
        if ( ! $user_id ) {
            return false;
        }

        $pagenum        = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
        $this->per_page = wpuf_get_option( 'per_page', 'wpuf_dashboard', 5 );
        $offset         = ( $pagenum - 1 ) * $this->per_page;

        $posts = get_posts(
            [
                'count_total'    => true,
                'author'         => $user_id,
                'post_status'    => 'publish',
                'post_type'      => $this->get_post_type( 'post' ),
                'posts_per_page' => $this->per_page,
                'offset'         => $offset,
                'numberposts'    => -1,
            ]
        );

        return $posts;
    }

    /**
     * Get user files
     *
     * @param integer $user_id
     *
     * @return array
     */
    public function get_files( $user_id ) {
        $fields = $this->get_fields( 'file' );

        // Get meta for file fields
        $meta_fields = array_map(
            function( $field ) {
                if ( ! empty( $field['meta'] ) ) {
                      return $field['meta'];
                }
            }, $fields
        );

        // Store attachments
        $attachments = [];

        // Get user files
        if ( $meta_fields ) {
            foreach ( $meta_fields as $meta ) {
                $attachment_id = get_user_meta( $user_id, $meta, true );

                $attchment = wp_get_attachment_url( $attachment_id );

                if ( $attchment ) {
                    $attachments[] = $attchment;
                }
            }
        }

        return $attachments;
    }

    /**
     * Get user comments
     *
     * @param integer $user_id
     *
     * @return array
     */
    public function get_comments( $user_id ) {
        if ( ! $user_id ) {
            return false;
        }

        $pagenum  = isset( $_GET['pagenum'] ) ? intval( wp_unslash( $_GET['pagenum'] ) ) : 1;
        $per_page = 10;
        $offset   = ( $pagenum - 1 ) * $per_page;

        $comments = get_comments(
            [
                'user_id'     => $user_id,
                'order_by'    => 'post_date',
                'order'       => 'DESC',
                'post_type'   => $this->get_post_type( 'comment' ),
                'post_status' => 'publish',
                'number'      => $per_page,
                'offset'      => $offset,
                'paged'       => $pagenum,
            ]
        );

        return $comments;
    }

    /**
     * Get post types
     *
     * @return array
     */
    public function get_post_type( $field_type ) {
        $post_field = $this->get_fields( $field_type );

        $post_types = array_map(
            function( $field ) {
                if ( ! empty( $field['post_type'] ) ) {
                      return $field['post_type'];
                }
            }, $post_field
        );

        return $post_types;
    }

    public function user_directory_profile() {
        echo '<ul class="wpuf-user-profile">';
        $user_id     = isset( $_GET['user_id'] ) ? intval( wp_unslash( $_GET['user_id'] ) ) : '';
        $user_status = self::is_approved( $user_id );

        if ( ! $user_status ) {
            return;
        }

        $userdata          = get_user_by( 'id', $user_id );
        $current_user      = wp_get_current_user();
        $profile_fields    = $this->get_options();
        $this->settings    = isset( $profile_fields['settings'] ) ? $profile_fields['settings'] : array();
        $profile_role      = isset( $userdata->roles[0] ) ? $userdata->roles[0] : '';
        $current_user_role = is_user_logged_in() ? $current_user->roles[0] : 'guest';

        do_action( 'wpuf_user_profile_before_content' );

        foreach ( $profile_fields['fields'] as $key => $field ) {
            if ( ! self::can_user_see( $profile_role, $field, $current_user_role ) ) {
                continue;
            }

            echo '<li>';

            switch ( $field['type'] ) {
                case 'meta':
                    if ( 'display_name' === $field['meta'] ) {
                        break;
                    }
                    $meta_key = $this->get_meta( $field );
                    do_action( 'wpuf_user_about_meta', $meta_key );
                    $value = '';

                    $repeat_field = get_user_meta( $user_id, $meta_key );

                    if ( is_array( $repeat_field ) ) {
                        $value = $repeat_field;
                    }

                    if ( ! empty( $userdata->data->$meta_key ) ) {
                        $value = $userdata->data->$meta_key;
                    }

                    if ( is_array( $value ) ) {
                        $value = implode( ', ', $value );
                    } elseif ( ! empty( $userdata->data->$meta_key ) ) {
                        $value = trim( $userdata->data->$meta_key );
                    }

                    ?>
                    <div class="wpuf-profile-value">
                        <label><?php echo $field['label']; ?>: </label>
                        <?php echo ! empty( $value ) ? make_clickable( $value ) : ' -- '; ?>
                    </div>
                    <?php
                    break;

                //                case 'section':
                //
                ?>
                    <!--                    <div class="wpuf-profile-section">--><?php //echo $field['label']; ?><!--</div>-->
                    <!--                    -->
                    <?php
                    //                    break;
                    //
                    //                case 'post':
                    //
                    ?>
                    <!--                    <label>--><?php //echo $field['label']; ?><!--:</label>-->
                    <!---->
                    <!--                    <div class="wpuf-profile-value">--><?php //$this->user_post( $user_id, $field['post_type'], $field['count'] ); ?><!--</div>-->
                    <!--                    -->
                    <?php
                    //                    break;
                    //
                    //                case 'comment':
                    //
                    ?>
                    <!--                    <label>--><?php //echo $field['label']; ?><!--:</label>-->
                    <!---->
                    <!--                    <div class="wpuf-profile-value">--><?php //$this->user_comments( $user_id, $field['post_type'], $field['count'] ); ?><!--</div>-->
                    <!--                    -->
                    <?php
                    //                    break;
                    //
                    //                case 'social':
                    //                    echo '<label>' . __( 'Social Section', 'wpuf-pro' ) . '</label>';
                    //                    $this->social_list( $field, $userdata );
                    //                    break;
                    //
                    //                case 'file':
                    //
                    ?>
                    <!--                    <label>--><?php //echo $field['label']; ?><!--:</label>-->
                    <!--                    -->
                    <?php
                    //                    $this->user_file( $field, $user_id );
                    //                    break;
            } // switch

            echo '</li>';
        } // foreach

        do_action( 'wpuf_user_profile_after_content' );
        echo '</ul>';
    }
}
