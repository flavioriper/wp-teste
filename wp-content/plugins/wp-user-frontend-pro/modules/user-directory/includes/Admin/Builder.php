<?php
namespace WPUF\UserDirectory\Admin;

/**
 * Class Builder
 */
class Builder {
    /**
     * Store Form Class object
     *
     * @var object
     */
    private $form;

    /**
     * Store user meta field
     *
     * @var array
     */
    private $meta_fields;

    /**
     * Constructor for the Builder Class
     */
    public function __construct() {
        add_action( 'admin_init', [ $this, 'form_handler' ] );

        $this->form = new Form();
    }

    /**
     * Profile meta form
     *
     * @return void
     */
    public function build_form() {
        $this->meta_fields = get_option( 'wpuf_userlisting', array() );
        $show_avatar = false;

        if ( isset( $this->meta_fields['settings']['avatar'] ) && $this->meta_fields['settings']['avatar'] == true ) {
            $show_avatar = true;
        }

        require_once WPUF_UD_INCLUDES . '/views/admin/form.php';

        do_action( 'wpuf-userlisting-templates', $this );
    }

    /**
     * Show form
     *
     * @return void
     */
    public function show_form() {
        $user_meta       = $this->meta_fields;
        $this->user_meta = $user_meta;

        if ( ! $user_meta ) {
            return;
        }

        if ( ! is_array( $user_meta['fields'] ) || ! count( $user_meta['fields'] ) > 0 ) {
            return;
        }

        foreach ( $user_meta['fields'] as $key => $val ) {
            $type = $val['type'];

            $this->form->li_wrap_open( $val );

            switch ( $type ) {
                case 'meta':
                    $this->form->print_meta( $key, $val );
                    break;

                case 'comment':
                    $this->form->print_comment( $key, $val );
                    break;

                case 'section':
                    $this->form->print_section( $key, $val );
                    break;

                case 'post':
                    $this->form->print_post( $key, $val );
                    break;

                case 'file':
                    $this->form->print_file( $key, $val );
                    break;

                case 'social':
                    $this->form->print_social( $key, $val );
                    break;
            }

            $this->form->li_wrap_close();
        }
    }

    public function print_profile_tab_settings() {
        $user_meta       = $this->meta_fields;
        $this->user_meta = $user_meta;
        $profile_tabs = isset( $user_meta['profile_tabs'] ) ? $user_meta['profile_tabs'] : [];

        $this->form->show_profile_tab_settings( $profile_tabs );
    }

    /**
     * Handle form submission
     *
     * @return void
     */
    public function form_handler() {
        $query_val = array();
        $tab_val = [];

        if ( ! isset( $_POST['wpuf_userlinstin_nonce'] ) || ! wp_verify_nonce( $_POST['wpuf_userlinstin_nonce'], 'wpuf_userlisting' ) ) {
            return;
        }

        if ( isset( $_POST['wpuf_pf_field']['type'] ) ) {
            foreach ( $_POST['wpuf_pf_field']['type'] as $key => $val ) {
                switch ( $val ) {
                    case 'type_meta':
                        $query_val[] = $this->get_meta_type( $key );
                        break;

                    case 'type_post':
                        $query_val[] = $this->get_post_type( $key );
                        break;

                    case 'type_comment':
                        $query_val[] = $this->get_comment_type( $key );
                        break;

                    case 'type_section':
                        $query_val[] = $this->get_section_type( $key );
                        break;

                    case 'type_social':
                        $query_val[] = $this->get_social_type( $key );
                        break;

                    case 'type_file':
                        $query_val[] = $this->get_file_type( $key );
                        break;

                    case 'type_tabs':
                        $tab_val[ $key ] = $this->get_tabs_type( $key );
                        break;
                }
            }
        }

        if ( isset( $_POST['wpuf_pf_field']['settings']['show_avatar'] ) ) {
            $fields['settings'] = array( 'avatar' => true );
        }

        $fields['profile_tabs'] = $tab_val;
        $fields['fields']       = $query_val;

        foreach ( $fields['fields'] as $key => $val ) {
            if ( empty( $val ) ) {
                unset( $fields['fields'][ $key ] );
            }
        }

        foreach ( $fields['profile_tabs'] as $key => $val ) {
            if ( empty( $val ) ) {
                unset( $fields['profile_tabs'][ $key ] );
            }
        }

        update_option( 'wpuf_userlisting', $fields );

        echo '<div class="updated fade"><p><strong>' . __( 'Fields are updated.', 'wpuf-pro' ) . '</strong></p></div>';
    }

    /**
     * Select meta data from $_POST
     */
    public function get_meta_type( $key ) {
        $type_meta = array();
        $meta_post = $_POST['wpuf_pf_field'];

        $type_meta['type'] = 'meta';
        $type_meta['label'] = isset( $meta_post['label'][ $key ] ) ? $meta_post['label'][ $key ] : '';
        $type_meta['meta'] = isset( $meta_post['meta'][ $key ] ) ? $meta_post['meta'][ $key ] : '';
        $type_meta['all_user_role'] = isset( $meta_post['all_user_role'][ $key ] ) ? $meta_post['all_user_role'][ $key ] : array();
        $type_meta['current_user_role'] = isset( $meta_post['current_user_role'][ $key ] ) ? $meta_post['current_user_role'][ $key ] : array();

        if ( isset( $meta_post['in_table'][ $key ] ) ) {
            $type_meta['in_table'] = 'yes';
            if ( isset( $meta_post['search_by'][ $key ] ) ) {
                $type_meta['search_by'] = 'yes';
            }
            if ( isset( $meta_post['sort_by'][ $key ] ) ) {
                $type_meta['sort_by'] = 'yes';
            }
        }

        return $type_meta;
    }

    /**
     *
     *
     */
    public function get_post_type( $key ) {
        $type_post = array();
        $post_post = $_POST['wpuf_pf_field'];

        $type_post['type']              = 'post';
        $type_post['post_type']         = $post_post['post_type'][ $key ];
        $type_post['label']             = $post_post['label'][ $key ];
        $type_post['count']             = empty( $post_post['count'][ $key ] ) ? $this->post_num : $post_post['count'][ $key ];
        $type_post['all_user_role']     = is_array( $post_post['all_user_role'][ $key ] ) ? $post_post['all_user_role'][ $key ] : array();
        $type_post['current_user_role'] = $_POST['wpuf_pf_field']['current_user_role'][ $key ];

        return $type_post;
    }

    public function get_comment_type( $key ) {
        $type_comment = array();
        $comment_post = $_POST['wpuf_pf_field'];

        $type_comment['type'] = 'comment';

        $type_comment['post_type'] = $comment_post['post_type'][ $key ];
        $type_comment['label']     = $comment_post['label'][ $key ];

        $type_comment['count']             = empty( $comment_post['count'][ $key ] ) ? $this->cmt_num : $comment_post['count'][ $key ];
        $type_comment['all_user_role']     = is_array( $comment_post['all_user_role'][ $key ] ) ? $comment_post['all_user_role'][ $key ] : array();
        $type_comment['current_user_role'] = $_POST['wpuf_pf_field']['current_user_role'][ $key ];

        return $type_comment;
    }

    public function get_social_type( $key ) {
        $type_social = array();

        if ( empty( $_POST['wpuf_pf_field']['social_icon'][0] ) && empty( $_POST['wpuf_pf_field']['social_url'][0] ) ) {
            return;
        }

        $type_social['type']              = 'social';
        $type_social['social_url']        = is_array( $_POST['wpuf_pf_field']['social_url'] ) ? $_POST['wpuf_pf_field']['social_url'] : array();
        $type_social['social_icon']       = is_array( $_POST['wpuf_pf_field']['social_icon'] ) ? $_POST['wpuf_pf_field']['social_icon'] : array();
        $type_social['all_user_role']     = $_POST['wpuf_pf_field']['all_user_role'][ $key ];
        $type_social['current_user_role'] = $_POST['wpuf_pf_field']['current_user_role'][ $key ];

        return $type_social;
    }

    /**
     * Select meta data from $_POST
     */
    function get_file_type( $key ) {
        $type_meta = array();
        $meta_post = $_POST['wpuf_pf_field'];

        $type_meta['type'] = 'file';
        $type_meta['label'] = $meta_post['label'][ $key ];
        $type_meta['meta'] = $meta_post['meta'][ $key ];
        $type_meta['all_user_role'] = $meta_post['all_user_role'][ $key ];
        $type_meta['current_user_role'] = $_POST['wpuf_pf_field']['current_user_role'][ $key ];

        if ( empty( $type_meta['meta'] ) ) {
            return;
        }

        return $type_meta;
    }

    /**
     *
     *
     */
    function get_section_type( $key ) {
        if ( empty( $_POST['wpuf_pf_field']['label'][ $key ] ) ) {
            return;
        }

        $type_section = array();
        $section_post = $_POST['wpuf_pf_field'];

        $type_section['type'] = 'section';
        $type_section['label'] = $section_post['label'][ $key ];
        $type_section['all_user_role'] = isset( $section_post['all_user_role'][ $key ] ) ? $section_post['all_user_role'][ $key ] : array();
        $type_section['current_user_role'] = isset( $_POST['wpuf_pf_field']['current_user_role'][ $key ] ) ? $_POST['wpuf_pf_field']['current_user_role'][ $key ] : array();

        return $type_section;
    }

    /**
     * get the tabs type
     *
     * @param string $key
     *
     * @return array
     */
    private function get_tabs_type( $key ) {
        $tab_post = $_POST['wpuf_pf_field'];

        $type_tab['label'] = wp_unslash( sanitize_text_field( $tab_post['label'][ $key ] ) );
        $type_tab['id']    = $key;

        if ( isset( $tab_post['show_tab'][ $key ] ) ) {
            $type_tab['show_tab'] = 1;
        } else {
            $type_tab['show_tab'] = 0;
        }

        return $type_tab;
    }
}
