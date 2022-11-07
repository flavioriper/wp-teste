<?php
namespace WPUF\UserDirectory\Admin;

/**
 * Class Form
 */
class Form {
    /**
     * Constructor for the Form class
     */
    public function __construct() {}

    /**
     * Print social
     *
     * @param string $key
     * @param string $val
     *
     * @return void
     */
    public function print_social( $key, $val ) {
        if ( isset( $val['social_icon']['wpuf_userlisting'] ) && $val['social_icon']['wpuf_userlisting'] == 'wpuf_userlisting' ) {
            $val['social_icon'] = array( '' => '' );
            $vals = '';
        } else {
            $vals = $val;
        }
        ?>
        <input type="hidden" value="type_social" name="wpuf_pf_field[type][<?php echo $key; ?>]">
        <?php
            //for all user
            $this->user_role_template( 'all_user_role', __( 'Profile User Role', 'wpuf-pro' ), $vals, $key );
            //current user
            $this->user_role_template( 'current_user_role', 'Viewer Role', $vals, $key );
        ?>
        <div style="padding: 10px 0;"></div>
        <?php $this->print_social_icon_url( $val ); ?>
        <?php
    }

    /**
     * Print file
     *
     * @param string $key
     * @param string $val
     *
     * @return void
     */
    function print_file( $key, $val ) {
        $meta_key = isset( $val['meta'] ) ? esc_attr( $val['meta'] ) : '';
        $label = isset( $val['label'] ) ? esc_attr( $val['label'] ) : '';
        ?>
        <div class="wpuf-form-rows">
            <label><?php _e( 'Label', 'wpuf-pro' ); ?></label>

            <input type="hidden" value="type_file" name="wpuf_pf_field[type][<?php echo $key; ?>]">
            <input type="text" value="<?php echo $label; ?>" name="wpuf_pf_field[label][<?php echo $key; ?>]">
        </div>

        <div class="wpuf-form-rows">
            <label><?php _e( 'Meta Key', 'wpuf-pro' ); ?></label>

            <div class="wpuf-form-sub-fields">

                <select name="wpuf_pf_field[meta][<?php echo $key; ?>]">
                    <option value="">- select -</option>
                    <optgroup label="<?php _e( 'Profile Fields', 'wpuf-pro' ); ?>">
                        <?php $this->default_meta_dropdown( $meta_key, $val ); ?>
                    </optgroup>
                    <optgroup label="<?php _e( 'Meta Keys', 'wpuf-pro' ); ?>">
                        <?php $this->custom_meta_key( $meta_key, $val ); ?>
                    </optgroup>
                </select>
            </div>
        </div>

        <?php
        //for all user
        $this->user_role_template( 'all_user_role', __( 'Profile User Role', 'wpuf-pro' ), $val, $key );
        //current user
        $this->user_role_template( 'current_user_role', 'Viewer Role', $val, $key );
    }

    public function print_social_icon_url( $val ) {
        if ( is_array( $val['social_icon'] ) && count( $val['social_icon'] ) > 0 ) {
            $loop_val = $val['social_icon'];
        } elseif ( is_array( $val['social_url'] ) && count( $val['social_icon'] ) > 0 ) {
            $loop_val = $val['social_url'];
        } else {
            return;
        }

        foreach ( $loop_val as $key => $url_icon ) {
            ?>
            <div class="wpuf-form-rows">
                <label><?php _e( 'Icon URL', 'wpuf-pro' ); ?></label>

                <div class="wpuf-form-sub-fields">
                    <input type="text" class="wpuf-file-field" value="<?php echo esc_url( $val['social_icon'][ $key ] ); ?>" name="wpuf_pf_field[social_icon][]">

                    <a href="#" class="button wpuf-file-upload"><?php _e( 'Upload Icon', 'wpuf-pro' ); ?></a>

                    <?php $meta_key = isset( $val['social_url'][ $key ] ) ? $val['social_url'][ $key ] : ''; ?>

                    <span class="wpuf-social-url">
                        <label><?php _e( 'Profile URL', 'wpuf-pro' ); ?></label>
                        <select name="wpuf_pf_field[social_url][]">
                            <option value="">- select -</option>
                            <optgroup label="<?php _e( 'Profile Fields', 'wpuf-pro' ); ?>">
                                <?php $this->default_meta_dropdown( $meta_key, '' ); ?>
                            </optgroup>
                            <optgroup label="<?php _e( 'Meta Keys', 'wpuf-pro' ); ?>">
                                <?php $this->custom_meta_key( $meta_key, '' ); ?>
                            </optgroup>
                        </select>
                    </span>

                    <span class="wpuf-social-actions">
                        <a href="#" data-social_field_type="#wpuf-extr-social-field" class="social-row-add button">+</a>
                        <a href="#" data-close_social="rmv_social" class="del-social button">-</a>
                    </span>
                </div>
            </div>
            <?php
        }
    }

    function print_post( $key, $val ) {
        $label = isset( $val['label'] ) ? esc_attr( $val['label'] ) : '';
        $count = isset( $val['count'] ) ? esc_attr( $val['count'] ) : 5;
        ?>
        <div class="wpuf-form-rows">
            <label><?php _e( 'Label', 'wpuf-pro' ); ?></label>

            <input value="<?php echo $label; ?>" type="text" name="wpuf_pf_field[label][<?php echo $key; ?>]">
            <input type="hidden" value="type_post" name="wpuf_pf_field[type][<?php echo $key; ?>]">
        </div>

        <div class="wpuf-form-rows">
            <label><?php _e( 'Post type', 'wpuf-pro' ); ?></label>

            <select name="wpuf_pf_field[post_type][<?php echo $key; ?>]">
                <?php $this->post_type( $key, $val ); ?>
            </select>
        </div>

        <div class="wpuf-form-rows">
            <label><?php _e( 'Post Count', 'wpuf-pro' ); ?></label>

            <input type="text" value="<?php echo $count; ?>" name="wpuf_pf_field[count][<?php echo $key; ?>]">
        </div>
        <?php
            //for all user
            $this->user_role_template( 'all_user_role', __( 'Profile User Role', 'wpuf-pro' ), $val, $key );
            //current user
            $this->user_role_template( 'current_user_role', 'Viewer Role', $val, $key );
        ?>
        <?php
    }

    /**
     * Print section
     *
     * @param string $key
     * @param string $val
     *
     * @return void
     */
    public function print_section( $key, $val ) {
        $label = isset( $val['label'] ) ? esc_attr( $val['label'] ) : '';
        ?>
        <div class="wpuf-form-rows">
            <label><?php esc_html_e( 'Label', 'wpuf-pro' ); ?></label>

            <input type="text" hidden  value="type_section" name="wpuf_pf_field[type][<?php echo $key; ?>]">
            <input type="text" value="<?php echo $label; ?>" name="wpuf_pf_field[label][<?php echo $key; ?>]">
        </div>

        <?php
            //for all user
            $this->user_role_template( 'all_user_role', __( 'Profile User Role', 'wpuf-pro' ), $val, $key );
            //current user
            $this->user_role_template( 'current_user_role', 'Viewer Role', $val, $key );
    }

    /**
     * Print comment
     *
     * @param string $key
     * @param string $val
     *
     * @return void
     */
    public function print_comment( $key, $val ) {
        $label = isset( $val['label'] ) ? esc_attr( $val['label'] ) : '';
        $count = isset( $val['count'] ) ? esc_attr( $val['count'] ) : 5;
        ?>
        <div class="wpuf-form-rows">
            <label><?php _e( 'Label', 'wpuf-pro' ); ?></label>

            <input placeholder="" value="<?php echo $label; ?>" type="text" name="wpuf_pf_field[label][<?php echo $key; ?>]">
        </div>

        <div class="wpuf-form-rows">
            <label><?php _e( 'Post type Comment', 'wpuf-pro' ); ?></label>

            <input type="hidden" value="type_comment" name="wpuf_pf_field[type][<?php echo $key; ?>]">
            <select name="wpuf_pf_field[post_type][<?php echo $key; ?>]">
                <?php $this->post_type( $key, $val ); ?>
            </select>
        </div>

        <div class="wpuf-form-rows">
            <label><?php _e( 'Comment count', 'wpuf-pro' ); ?></label>

            <input value="<?php echo $count; ?>" type="text" name="wpuf_pf_field[count][<?php echo $key; ?>]">
        </div>

        <?php
            //for all user
            $this->user_role_template( 'all_user_role', __( 'Profile User Role', 'wpuf-pro' ), $val, $key );
            //current user
            $this->user_role_template( 'current_user_role', 'Viewer Role', $val, $key );
    }

    /**
     * Print meta
     *
     * @param string $key
     * @param string $val
     *
     * @return void
     */
    public function print_meta( $key, $val ) {
        $meta_key = isset( $val['meta'] ) ? esc_attr( $val['meta'] ) : '';
        $label = isset( $val['label'] ) ? esc_attr( $val['label'] ) : '';

        // var_dump($meta_key, $val);
        ?>
        <div class="wpuf-form-rows">
            <label><?php _e( 'Label', 'wpuf-pro' ); ?></label>

            <input type="hidden" value="type_meta" name="wpuf_pf_field[type][<?php echo $key; ?>]">
            <input type="text" value="<?php echo $label; ?>" name="wpuf_pf_field[label][<?php echo $key; ?>]">
        </div>

        <div class="wpuf-form-rows">
            <label><?php _e( 'Meta Key', 'wpuf-pro' ); ?></label>

            <div class="wpuf-form-sub-fields">

                <select name="wpuf_pf_field[meta][<?php echo $key; ?>]">
                    <option value="">- select -</option>
                    <optgroup label="<?php _e( 'Profile Fields', 'wpuf-pro' ); ?>">
                        <?php $this->default_meta_dropdown( $meta_key, $val ); ?>
                    </optgroup>
                    <optgroup label="<?php _e( 'Meta Keys', 'wpuf-pro' ); ?>">
                        <?php $this->custom_meta_key( $meta_key, $val ); ?>
                    </optgroup>
                </select>
            </div>
        </div>

        <?php
        //for all user
        $this->user_role_template( 'all_user_role', __( 'Profile User Role', 'wpuf-pro' ), $val, $key );
        //current user
        $this->user_role_template( 'current_user_role', 'Viewer Role', $val, $key );

        $this->meta_in_table( $key, $val );
    }

    /**
     * Social icon row template
     *
     * @return void
     */
    public function social_icon_row_template() {
        ?>
        <div class="wpuf-form-rows">
            <label><?php _e( 'Icon URL', 'wpuf-pro' ); ?></label>

            <div class="wpuf-form-sub-fields">
                <input type="text" class="wpuf-file-field" value="" name="wpuf_pf_field[social_icon][]">

                <a href="#" class="button wpuf-file-upload"><?php _e( 'Upload Icon', 'wpuf-pro' ); ?></a>

                <span class="wpuf-social-url">
                    <label><?php _e( 'Profile URL', 'wpuf-pro' ); ?></label>

                    <select name="wpuf_pf_field[social_url][]">
                        <option value="">- select -</option>
                        <optgroup label="<?php _e( 'Profile Fields', 'wpuf-pro' ); ?>">
                            <?php $this->default_meta_dropdown(); ?>
                        </optgroup>
                        <optgroup label="<?php _e( 'Meta Keys', 'wpuf-pro' ); ?>">
                            <?php $this->custom_meta_key(); ?>
                        </optgroup>
                    </select>
                </span>

                <span class="wpuf-social-actions">
                    <a href="#" data-social_field_type="#wpuf-extr-social-field" class="social-row-add button">+</a>
                    <a href="#" data-close_social="rmv_social" class="del-social button">-</a>
                </span>
            </div>
        </div>
        <?php
    }

    /**
     * User role template
     *
     * @param string $field_name
     * @param string $label
     * @param string $user_meta
     * @param string $dbkey
     *
     * @return void
     */
    public function user_role_template( $field_name = '', $label = '', $user_meta = '', $dbkey = null ) {
        $count = ( $dbkey === null ) ? '<%= count %>' : $dbkey;

        // var_dump($field_name, $label, $user_meta, $dbkey);
        ?>
        <div class="wpuf-form-rows">
            <label><?php _e( $label, 'wpuf-pro' ); ?></label>

            <div class="wpuf-form-sub-fields">
                <ul class="wpuf-role">
                    <?php
                    $roles = $this->get_user_roles();

                    foreach ( $roles as $key => $role_name ) {
						?>
                        <li>
                            <label>
                                <?php
                                $checked = false;
                                if ( ! isset( $user_meta[ $field_name ] ) ) {
                                    // on inserting the field
                                    $checked = true;
                                } else {
                                    $checked = in_array( $key, $user_meta[ $field_name ] );
                                }
                                ?>
                                <input type="checkbox" <?php echo checked( $checked ); ?>  value="<?php echo esc_attr( $key ); ?>" name="wpuf_pf_field[<?php echo $field_name; ?>][<?php echo $count; ?>][<?php echo $key; ?>]">
                                <?php echo $role_name; ?>
                            </label>
                        </li>

                    <?php } ?>

                    <?php if ( $field_name != 'all_user_role' ) { ?>
                        <?php //var_dump( $user_meta); ?>
                        <li>
                            <label>
                                <?php
                                $checked = false;
                                if ( ! isset( $user_meta['current_user_role'] ) ) {
                                    // on inserting the field
                                    $checked = true;
                                } elseif ( in_array( 'guest', $user_meta['current_user_role'] ) ) {
                                    $checked = true;
                                }
                                ?>

                                <input type="checkbox" <?php checked( $checked ); ?>  value="guest" name="wpuf_pf_field[<?php echo $field_name; ?>][<?php echo $count; ?>][guest]">
                                <?php echo __( 'Guest', 'wpuf-pro' ); ?>
                            </label>
                        </li>
                    <?php } ?>
                </ul>

                <p class="description">
                    <?php if ( $field_name == 'all_user_role' ) { ?>
                        Show this field if the currenty viewed user profile has one of these role
                    <?php } else { ?>
                        Show this field if the viewer (current logged in user or guest) has one of these role.
                    <?php } ?>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Shows the settings for a single user profile tabs.
     * defaults are Comments, Posts, File/Image, About
     *
     * @return void
     */
    public function show_profile_tab_settings( $profile_tabs = [] ) {
        $activity_module_active = class_exists( 'WPUF_User_Activity' );
        $show_activity_tab  = false;
        $activity_module_id = 'activity';
        $saved_tabs   = [];
        $activity_tab = [
            'label'    => __( 'Activity', 'wpuf-pro' ),
            'id'       => $activity_module_id,
            'show_tab' => 1,
        ];

        if ( count( $profile_tabs ) ) {
            foreach ( $profile_tabs as $tab ) {
                if ( $activity_module_id === $tab['id'] ) {
                    // don't show activity setup if
                    // activity module settings found
                    // but activity module is turned off now
                    if ( ! $activity_module_active ) {
                        continue;
                    } else {
                        $show_activity_tab = true;
                    }
                }

                $saved_tabs[] = [
                    'label'    => $tab['label'],
                    'id'       => $tab['id'],
                    'show_tab' => $tab['show_tab'],
                ];
            }

            // show the activity tab settings if admin activates User Activity module
            // after set up the profile tab
            if ( ! $show_activity_tab && $activity_module_active ) {
                $saved_tabs[] = $activity_tab;
            }
        }

        $default_tabs = [
            [
                'label'    => __( 'Comments', 'wpuf-pro' ),
                'id'       => 'comments',
                'show_tab' => 1,
            ],
            [
                'label'    => __( 'Posts', 'wpuf-pro' ),
                'id'       => 'posts',
                'show_tab' => 1,
            ],
            [
                'label'    => __( 'File/Image', 'wpuf-pro' ),
                'id'       => 'file',
                'show_tab' => 1,
            ],
            [
                'label'    => __( 'About', 'wpuf-pro' ),
                'id'       => 'about',
                'show_tab' => 1,
            ],
        ];

        // if User Activity module is activated, show settings for this tab
        if ( class_exists( 'WPUF_User_Activity' ) ) {
            $default_tabs[] = $activity_tab;
        }

        // if tab is previously saved, show them. if not, show the defaults
        $profile_tabs = count( $saved_tabs ) ? $saved_tabs : $default_tabs;

        ?>
        <div class="profile-tabs-area">
            <table>
                <thead>
                    <tr>
                        <td style="text-align: center;" colspan="3">
                            <h2><?php esc_html_e( 'Show/Hide profile tabs', 'wpuf-pro' ); ?></h2>
                            <p><?php esc_html_e( 'Show or Hide any single profile tab', 'wpuf-pro' ); ?></p>
                        </td>
                    </tr>
                </thead>
                <tbody id="profile-tabs">
                    <?php
					foreach ( $profile_tabs as $tab ) {
						$this->print_tab( $tab );
					}
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Print tab settings for single user profile
     *
     * @param string $val
     *
     * @return void
     */
    public function print_tab( $val ) {
        $label     = isset( $val['label'] ) ? esc_html( $val['label'] ) : '';
        $id        = isset( $val['id'] ) ? esc_attr( $val['id'] ) : '';
        $is_show   = isset( $val['show_tab'] ) ? esc_attr( $val['show_tab'] ) : 1;
        $tab_label = isset( $val['id'] ) ? ucfirst( $val['id'] ) : '';
        $tab_label .= ' Label:';
        ?>

        <tr>
            <td>
                <div class="wpuf-label">
                    <label><?php echo $tab_label; ?></label>
                </div>
            </td>
            <td>
                <input type="hidden" value="type_tabs" name="wpuf_pf_field[type][<?php echo $id; ?>]">
                <input type="text" value="<?php echo $label; ?>" name="wpuf_pf_field[label][<?php echo $id; ?>]">
            </td>
            <td>
                <label class="switch" value="yes">
                    <input type="checkbox" <?php checked( $is_show, 1 ); ?> name="wpuf_pf_field[show_tab][<?php echo $id; ?>]">
                    <span class="slider round"></span>
                </label>
            </td>
            <td class="column-handle ui-sortable-handle" style="display: table-cell;"></td>
        </tr>
        <?php
    }

    /**
     * Wrap open div
     *
     * @param array $value
     *
     * @return void
     */
    public function li_wrap_open( $value ) {
        $label = isset( $value['label'] ) ? $value['label'] : '';
        $type = isset( $value['type'] ) ? ucfirst( $value['type'] ) . ': <strong>' . $label . '</strong>' : $label;
        ?>
        <li>
            <div class="wpuf-legend">
                <div class="wpuf-label"><?php echo $type; ?></div>
                <div class="wpuf-actions">
                    <a href="#" class="wpuf-remove"><?php _e( 'Remove', 'wpuf-pro' ); ?></a>
                    <a href="#" class="wpuf-toggle"><?php _e( 'Toggle', 'wpuf-pro' ); ?></a>
                </div>
            </div>

            <div class="wpuf-form-holder">
        <?php
    }

    /**
     * Get custom meta key
     *
     * @param string $dbkey
     * @param string $user_meta
     *
     * @return void
     */
    public function custom_meta_key( $dbkey = '', $user_meta = '' ) {
        global $wpdb;
        $query = $wpdb->get_results(
            "SELECT DISTINCT(meta_key) FROM {$wpdb->usermeta}
            WHERE
            meta_key != 'admin_color'
            AND meta_key != 'wp_user_level'
            AND meta_key != 'wp_capabilities'
            AND meta_key != 'user_role'
            AND meta_key != 'dismissed_wp_pointers'
            AND meta_key != 'users_per_page'
            AND meta_key != 'wp_dashboard_quick_press_last_post_id'
            AND meta_key != 'wp_post_formats_post'
            AND meta_key != 'wp_nav_menu_recently_edited'
            AND meta_key != 'use_ssl'
            AND meta_key NOT LIKE 'closedpostboxes%'
            AND meta_key NOT LIKE 'meta-box-order_%'
            AND meta_key NOT LIKE 'metaboxhidden_%'
            AND meta_key NOT LIKE 'screen_layout_%'
            AND meta_key NOT LIKE 'wp_user-settings%'", ARRAY_A
        );

        $fields = array(
            'Username'          => 'user_login',
            'First Name'        => 'first_name',
            'Last Name'         => 'last_name',
            'Nickname'          => 'nickname',
            'E-mail'            => 'user_email',
            'Website'           => 'user_url',
            'Biographical Info' => 'description',
        );

        foreach ( $query as $val ) {
            $option_val = array_diff( $val, $fields );

            if ( count( $option_val ) > 0 ) {
                ?>
                <option value="<?php echo esc_attr( $option_val['meta_key'] ); ?>"<?php selected( $dbkey, $option_val['meta_key'] ); ?>><?php echo $option_val['meta_key']; ?>
                <?php
            }
        }
    }

    function li_wrap_close() {
        ?>
            </div> <!-- .wpuf-form-holder -->
        </li>
        <?php
    }

    /**
     * Post type
     *
     * @param string $dbkey
     * @param string $user_meta
     *
     * @return void
     */
    public function post_type( $dbkey = '', $user_meta = '' ) {
        $post_type = get_post_types();

        unset( $post_type['attachment'] );
        unset( $post_type['revision'] );
        unset( $post_type['nav_menu_item'] );
        unset( $post_type['wpuf_forms'] );
        unset( $post_type['wpuf_profile'] );

        foreach ( $post_type as $key => $val ) {
            if ( isset( $user_meta['post_type'] ) && strtolower( $user_meta['post_type'] ) == strtolower( $key ) ) {
                $select = 'selected';
            } else {
                $select = '';
            }
            ?>

            <option <?php echo $select; ?> value="<?php echo $key; ?>"><?php echo $val; ?></option>

            <?php
        }
    }

    /**
     * Get default dropdown meta
     *
     * @param string $dbkey
     * @param string $user_meta
     *
     * @return void
     */
    public function default_meta_dropdown( $dbkey = '', $user_meta = '' ) {
        $fields = array(
            'Username'          => 'user_login',
            'First Name'        => 'first_name',
            'Last Name'         => 'last_name',
            'Display Name'      => 'display_name',
            'Nickname'          => 'nickname',
            'E-mail'            => 'user_email',
            'Website'           => 'user_url',
            'Biographical Info' => 'description',
        );

        foreach ( $fields as $key => $val ) {
            ?>
                <option value="<?php echo esc_attr( $val ); ?>"<?php selected( $dbkey, $val ); ?>><?php echo $key; ?></option>
            <?php
        }
    }

        /**
     * Get user roles
     *
     * @return array
     */
    public function get_user_roles() {
        global $wp_roles;

        if ( ! $wp_roles ) {
            $wp_roles = new \WP_Roles();
        }

        return $wp_roles->get_names();
    }

    /**
     * Print meta table
     *
     * @param string $key
     * @param string $value
     *
     * @return void
     */
    public function meta_in_table( $key, $value ) {
        $in_table = isset( $value['in_table'] ) ? 'yes' : 'no';
        $search_by = isset( $value['search_by'] ) ? 'yes' : 'no';
        $sort_by = isset( $value['sort_by'] ) ? 'yes' : 'no';
        $show_class = ( 'no' == $in_table ) ? ' wpuf-hide' : '';
        ?>
        <div class="wpuf-form-rows">
            <div class="wpuf-form-sub-fields">
            <label class="full-width show">
                <input type="checkbox" <?php checked( $in_table, 'yes' ); ?> value="yes" name="wpuf_pf_field[in_table][<?php echo $key; ?>]">
                <?php _e( 'Show in user listing table', 'wpuf-pro' ); ?>
            </label>
            &nbsp;&nbsp;
            <label class="full-width search-by <?php echo $show_class; ?>">
                <input type="checkbox" <?php checked( $search_by, 'yes' ); ?> value="yes" name="wpuf_pf_field[search_by][<?php echo $key; ?>]">
                <?php _e( 'Search by this meta in user listing table', 'wpuf-pro' ); ?>
            </label>
            <!-- &nbsp;&nbsp;
            <label class="full-width sort-by <?php echo $show_class; ?>">
                <input type="checkbox" <?php checked( $sort_by, 'yes' ); ?> value="yes" name="wpuf_pf_field[sort_by][<?php echo $key; ?>]">
                <?php esc_html_e( 'Sort by this meta listing table', 'wpuf-pro' ); ?>
            </label> -->
            </div>
        </div>
        <?php
    }
}
