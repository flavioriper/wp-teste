<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

final class WPBE_META extends WPBE_EXT {

    protected $slug = 'meta'; //unique
    private $storage_key = 'wpbe_meta_fields';

    public function __construct() {

        load_plugin_textdomain('bulk-editor', false, 'bulk-editor/languages');

        add_action('wpbe_ext_scripts', array($this, 'wpbe_ext_scripts'), 1);

        //ajax
        add_action('wp_ajax_wpbe_save_meta', array($this, 'wpbe_save_meta'), 1);
        add_action('wp_ajax_wpbe_meta_get_keys', array($this, 'wpbe_meta_get_keys'), 1);

        //tabs

        $user = wp_get_current_user();
        $role = (array) $user->roles;

        if ($role[0] == 'administrator') {
            $this->add_tab($this->slug, 'panel', esc_html('Meta Fields', 'bulk-editor'));
            add_action('wpbe_ext_panel_' . $this->slug, array($this, 'wpbe_ext_panel'), 1);
        }


        //hooks
        add_filter('wpbe_extend_fields', array($this, 'wpbe_extend_fields'), 1);
        add_filter('wpbe_filter_text', array($this, 'wpbe_filter_text'), 1);
        add_filter('wpbe_filter_numbers', array($this, 'wpbe_filter_numbers'), 1);
        add_filter('wpbe_filter_other', array($this, 'wpbe_filter_other'), 1);

        add_filter('wpbe_bulk_text', array($this, 'wpbe_bulk_text'), 1);
        add_filter('wpbe_bulk_number', array($this, 'wpbe_bulk_number'), 1);
        add_filter('wpbe_bulk_other', array($this, 'wpbe_bulk_other'), 1);
    }

    public function wpbe_ext_scripts() {
        wp_enqueue_script('wpbe_ext_' . $this->slug, $this->get_ext_link() . 'assets/js/' . $this->slug . '.js', [], WPBE_VERSION);
        wp_enqueue_style('wpbe_ext_' . $this->slug, $this->get_ext_link() . 'assets/css/' . $this->slug . '.css', [], WPBE_VERSION);
        ?>
        <script>
            lang.<?php echo $this->slug ?> = {};
            lang.<?php echo $this->slug ?>.enter_key = '<?php esc_html_e('Meta key cannot be empty!', 'bulk-editor') ?>';
            lang.<?php echo $this->slug ?>.enter_prod_id = '<?php esc_html_e('Enter a post ID!', 'bulk-editor') ?>';
            lang.<?php echo $this->slug ?>.no_keys_found = '<?php esc_html_e('No meta keys found!', 'bulk-editor') ?>';
            lang.<?php echo $this->slug ?>.new_key = '<?php esc_html_e('New meta key', 'bulk-editor') ?>';
        </script>
        <?php
    }

    public function wpbe_ext_panel() {
        $data = array();
        $data['metas'] = $this->get_fields();
        echo WPBE_HELPER::render_html($this->get_ext_path() . 'views/panel.php', $data);
    }

    //***
    //ajax
    public function wpbe_save_meta() {
        if (!WPBE_HELPER::can_manage_data()) {
            wp_die('0');
        }

        if ($this->settings->current_user_role != 'administrator') {
            return;
        }

        //***

        $data = array();
        parse_str($_REQUEST['formdata'], $data);

        if (isset($data['wpbe_meta_fields'])) {
            if (is_array($data['wpbe_meta_fields'])) {
                $this->update_fields($data['wpbe_meta_fields']);
            }
        }

        exit;
    }

    private function update_fields($data) {
        if (!empty($data)) {
            foreach ($data as $k => $m) {
                if (!isset($data[$k]['meta_key'])) {
                    continue;
                }
                $data[$k]['meta_key'] = /* sanitize_key */trim($m['meta_key']);
                //do not sanitize as exists such meta keys as for example _woocs_sale_price_USD and if to make lowerstring key will be invalid!
                if ($m['edit_view'] == 'textarea') {
                    $data[$k]['type'] = 'string'; //important
                }
            }
        }

        update_option($this->storage_key, $data);
    }

    private function get_fields() {
        $metas = get_option($this->storage_key);

        if (!empty($metas) AND is_array($metas)) {
            foreach ($metas as $k => $m) {
                if (empty($m['meta_key'])) {
                    unset($metas[$k]);
                }
            }
        } else {
            $metas = array();
        }

        return $metas;
    }

    //hook wpbe_extend_fields - add columns into editor
    public function wpbe_extend_fields($fields) {
        $metas = $this->get_fields();

        if (!empty($metas)) {
            foreach ($metas as $m) {
                $f = array(
                    'show' => 0,
                    'title' => $m['title'],
                    'title_static' => true, //will not be possible to change title in columns settings
                    'field_type' => 'meta',
                    'meta_key' => $m['meta_key'],
                    'type' => $m['type'],
                    'editable' => TRUE,
                    'direct' => TRUE,
                    'edit_view' => $m['edit_view'],
                    'order' => FALSE,
                    //'prohibit_post_types' => array(),
                    'site_editor_visibility' => 1
                );

                if ($m['type'] == 'number') {
                    $f['sanitize'] = 'floatval';
                    $f['order'] = TRUE;
                }

                if ($m['edit_view'] == 'switcher') {
                    $f['select_options'] = array(
                        '1' => esc_html('Yes', 'bulk-editor'), //true                        
                        '0' => esc_html('No', 'bulk-editor'), //false
                    );
                    $f['type'] = 'string'; //matter
                }

                //$f['css_classes'] = 'not-for-variations';
                $f['css_classes'] = '';

                $fields[$m['meta_key']] = $f;
            }
        }

        return $fields;
    }

    //hook wpbe_filter_text
    public function wpbe_filter_text($data) {
        $metas = $this->get_fields();
        if (!empty($metas)) {
            foreach ($metas as $m) {
                if (in_array($m['meta_key'], $this->settings->get_fields_keys())) {
                    if ($m['type'] == 'string') {
                        $data[$m['meta_key']] = array(
                            'placeholder' => $m['title'],
                            'direct' => TRUE,
                            'behavior_options' => array(
                                'LIKE' => esc_html('LIKE', 'bulk-editor'),
                                '=' => esc_html('EXACT (=)', 'bulk-editor'),
                                '!=' => esc_html('NOT EXACT (!=)', 'bulk-editor'),
                                'NOT LIKE' => esc_html('NOT LIKE', 'bulk-editor'),
                            ),
                            'css_classes' => 'not-for-variations'
                        );
                    }
                }
            }
        }

        return $data;
    }

    //hook wpbe_filter_numbers
    public function wpbe_filter_numbers($data) {
        $metas = $this->get_fields();
        if (!empty($metas)) {
            foreach ($metas as $m) {
                if (in_array($m['meta_key'], $this->settings->get_fields_keys())) {
                    if ($m['type'] == 'number' AND $m['edit_view'] != 'switcher' AND $m['edit_view'] != 'calendar') {
                        $data[$m['meta_key']] = array(
                            'placeholder_from' => sprintf(esc_html('%s from', 'bulk-editor'), $m['title']),
                            'placeholder_to' => sprintf(esc_html('%s to', 'bulk-editor'), $m['title']),
                            //'css_classes' => 'not-for-variations'
                            'direct' => TRUE,
                            'css_classes' => ''
                        );
                    }
                }
            }
        }

        return $data;
    }

    //hook wpbe_filter_other
    public function wpbe_filter_other($data) {

        $metas = $this->get_fields();
        if (!empty($metas)) {
            foreach ($metas as $m) {
                if (in_array($m['meta_key'], $this->settings->get_fields_keys())) {
                    if ($m['edit_view'] == 'switcher') {
                        $data[$m['meta_key']] = array(
                            'title' => $m['title'],
                            'direct' => TRUE,
                            'css_classes' => 'not-for-variations'
                        );
                    }
                }
            }
        }

        return $data;
    }

    //ajax
    public function wpbe_meta_get_keys() {
        $res = '';

        $post_id = intval($_REQUEST['post_id']);
        if ($post_id > 0) {
            $a1 = array_keys(get_post_meta($post_id, '', true));
            //$a2 = (new WPBE_PDS_CPT())->get_internal_meta_keys();
            $a2 = [];
            $res = array_diff($a1, $a2);
        }

        wp_die(json_encode(array_values($res)));
    }

    //hook wpbe_bulk_text
    public function wpbe_bulk_text($data) {
        $metas = $this->get_fields();
        if (!empty($metas)) {
            foreach ($metas as $m) {
                if (in_array($m['meta_key'], $this->settings->get_fields_keys())) {
                    if ($m['type'] == 'string') {
                        $data[$m['meta_key']] = array(
                            'title' => $m['title'],
                            'direct' => TRUE,
                            'css_classes' => 'not-for-variations'
                        );
                    }
                }
            }
        }

        return $data;
    }

    //hook wpbe_bulk_number
    public function wpbe_bulk_number($data) {
        $metas = $this->get_fields();
        if (!empty($metas)) {
            foreach ($metas as $m) {
                if ($m['type'] == 'number' AND $m['edit_view'] != 'switcher') {
                    if (in_array($m['meta_key'], $this->settings->get_fields_keys())) {
                        $data[$m['meta_key']] = array(
                            'title' => $m['title'],
                            'direct' => TRUE,
                            'options' => array(
                                'new' => esc_html('set new', 'bulk-editor'),
                                'invalue' => esc_html('increase by value', 'bulk-editor'),
                                'devalue' => esc_html('decrease by value', 'bulk-editor'),
                                'inpercent' => esc_html('increase by %', 'bulk-editor'),
                                'depercent' => esc_html('decrease by %', 'bulk-editor')
                            ),
                            //'css_classes' => 'not-for-variations'
                            'css_classes' => ''
                        );
                    }
                }
            }
        }

        return $data;
    }

    //hook wpbe_bulk_other
    public function wpbe_bulk_other($data) {

        $metas = $this->get_fields();
        if (!empty($metas)) {
            foreach ($metas as $m) {
                if (in_array($m['meta_key'], $this->settings->get_fields_keys())) {
                    if ($m['edit_view'] == 'switcher') {
                        $data[$m['meta_key']] = array(
                            'title' => $m['title'],
                            'direct' => TRUE,
                            'options' => array(
                                '1' => esc_html('Yes', 'bulk-editor'), //true                        
                                '0' => esc_html('No', 'bulk-editor'), //false
                            ),
                            'css_classes' => 'not-for-variations'
                        );
                    }
                }
            }
        }

        return $data;
    }

}
