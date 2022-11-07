<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function wpbe_get_fields() {
    static $users = array();

    if ($users === array()) {
        $uu = get_users();
        $users = array();
        if (!empty($uu)) {
            foreach ($uu as $u) {
                $users[$u->data->ID] = $u->data->display_name;
            }
        }
    }

    //***

    $post_mime_types = [];
    foreach (get_allowed_mime_types() as $value) {
        $post_mime_types[$value] = $value;
    }


    //***

    return apply_filters('wpbe_extend_fields', array(
        '__checker' => array(
            'show' => 1, //this is special checkbox only for functionality
            'title' => WPBE_HELPER::draw_checkbox(array('class' => 'all_posts_checker')),
            'desc' => esc_html('Checkboxes for the posts selection. Use SHIFT button on your keyboard to select multiple rows.', 'bulk-editor'),
            'field_type' => 'none',
            'type' => 'number',
            'editable' => FALSE,
            'edit_view' => 'checkbox',
            'order' => FALSE,
            'move' => FALSE,
            'direct' => TRUE,
            'site_editor_visibility' => 1
        ),
        'ID' => array(
            'show' => 1, //1 - enabled here by default
            'title' => 'ID',
            'field_type' => 'field',
            'type' => 'number',
            'editable' => FALSE,
            'edit_view' => 'textinput',
            'order' => TRUE,
            'move' => FALSE,
            'direct' => TRUE,
            'site_editor_visibility' => 1
        ),
        '_thumbnail_id' => array(
            'show' => 1, //by default
            'title' => esc_html('Thumbnail', 'bulk-editor'),
            'field_type' => 'meta',
            'type' => 'number',
            'editable' => true,
            'edit_view' => 'thumbnail',
            'order' => FALSE,
            'direct' => TRUE,
            'site_editor_visibility' => 1,
            'prohibit_post_types' => array('attachment'),
            'css_classes' => '',
        ),
        'post_title' => array(
            'show' => 1,
            'title' => esc_html('Title', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'textinput',
            'order' => TRUE,
            'direct' => TRUE,
            'css_classes' => 'not-for-variations',
            'site_editor_visibility' => 1
        ),
        'post_content' => array(
            'show' => 1,
            'title' => esc_html('Content', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'popupeditor',
            'order' => FALSE,
            'direct' => TRUE,
            'site_editor_visibility' => 1
        ),
        'post_excerpt' => array(
            'show' => 1,
            'title' => esc_html('Excerpt', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'popupeditor',
            'order' => FALSE,
            'direct' => TRUE,
            'css_classes' => 'not-for-variations',
            'site_editor_visibility' => 1
        ),
        'post_name' => array(
            'show' => 0,
            'title' => esc_html('Slug', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'textinput',
            'sanitize' => 'urldecode',
            'order' => FALSE,
            'direct' => TRUE,
            //'prohibit_post_types' => array(),
            'css_classes' => 'not-for-variations',
            'site_editor_visibility' => 1
        ),
        'post_status' => array(
            'show' => 1,
            'title' => esc_html('Status', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'select',
            'select_options' => get_post_statuses(),
            'order' => FALSE,
            'direct' => TRUE,
            'prohibit_post_types' => array('attachment'),
            'site_editor_visibility' => 1
        ),
        'comment_status' => array(
            'show' => 0,
            'title' => esc_html('Comment status', 'bulk-editor'),
            'desc' => esc_html('Can users comment post or not.', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'switcher',
            'select_options' => array(
                'open' => esc_html('Open', 'bulk-editor'), //true
                'closed' => esc_html('Closed', 'bulk-editor'), //false
            ),
            'order' => FALSE,
            'direct' => TRUE,
            //'allow_post_types' => array(),
            'prohibit_post_types' => array('attachment'),
            'site_editor_visibility' => 1,
            'css_classes' => '',
        ),
        'ping_status' => array(
            'show' => 0,
            'title' => esc_html('Ping status', 'bulk-editor'),
            'desc' => esc_html('A ping is a [this site has new content] notification that invites search engine bots to visit your blog.', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'switcher',
            'select_options' => array(
                'open' => esc_html('Open', 'bulk-editor'), //true
                'closed' => esc_html('Closed', 'bulk-editor'), //false
            ),
            'order' => FALSE,
            'direct' => TRUE,
            //'allow_post_types' => array(),
            'prohibit_post_types' => array('attachment'),
            'site_editor_visibility' => 1,
            'css_classes' => '',
        ),
        'to_ping' => array(
            'show' => 0,
            'title' => esc_html('Send trackbacks to', 'bulk-editor'),
            'desc' => esc_html('Separate multiple URLs with spaces. Ping status should be open! Remove 2 links separated by space, save. After opening you will not see links as they had been pinged! No sense for roll back, its sending is instant. Trackbacks are a way to notify legacy blog systems that you’ve linked to them. If you link other WordPress sites, they’ll be notified automatically using pingbacks, no other action necessary. Do not use these fields for something else. They are parsed many times in core code (69 matches for to_ping); their format is fixed.', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'popupeditor',
            'order' => FALSE,
            'direct' => TRUE,
            'prohibit_post_types' => array('attachment'),
            'site_editor_visibility' => 1
        ),
        /*
          'pinged' => array(
          'show' => 0,
          'title' => esc_html('Trackbacks sent', 'bulk-editor'),
          'desc' => esc_html('Where to its pigned already', 'bulk-editor'),
          'field_type' => 'field',
          'type' => 'string',
          'editable' => TRUE,
          'edit_view' => 'popupeditor',
          'order' => FALSE,
          'direct' => TRUE,
          'prohibit_post_types' => array('attachment'),
          'site_editor_visibility' => 1
          ),
         */
        'post_password' => array(
            'show' => 0,
            'title' => esc_html('Post password', 'bulk-editor'),
            'desc' => esc_html('Password for private posts', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'textinput',
            'order' => TRUE,
            'direct' => TRUE,
            'css_classes' => 'not-for-variations',
            'prohibit_post_types' => array('attachment'),
            'site_editor_visibility' => 1
        ),
        'menu_order' => array(
            'show' => 0,
            'title' => esc_html('Menu order', 'bulk-editor'),
            'desc' => esc_html('Custom ordering position.', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'number',
            'sanitize' => 'intval',
            'editable' => TRUE,
            'edit_view' => 'textinput',
            'order' => TRUE,
            'direct' => TRUE,
            'site_editor_visibility' => 1
        ),
        'post_author' => array(
            'show' => 1,
            'title' => esc_html('Author', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'select',
            'select_options' => $users,
            'order' => FALSE,
            'direct' => TRUE,
            'css_classes' => 'not-for-variations',
            'site_editor_visibility' => 1
        ),
        'post_date' => array(
            'show' => 1,
            'title' => esc_html('Date Published', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'timestamp', //timestamp, unix
            'set_day_end' => FALSE, //false: 00:00:00, true: 23:59:59
            'editable' => TRUE,
            'edit_view' => 'calendar',
            'order' => TRUE,
            'direct' => TRUE,
            'css_classes' => 'not-for-variations',
            'site_editor_visibility' => 1
        ),
        'post_date_gmt' => array(
            'show' => 0,
            'title' => esc_html('Date Published GMT', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'timestamp', //timestamp, unix
            'set_day_end' => FALSE, //false: 00:00:00, true: 23:59:59
            'editable' => TRUE,
            'edit_view' => 'calendar',
            'order' => TRUE,
            'direct' => TRUE,
            //'prohibit_post_types' => array(),
            'css_classes' => 'not-for-variations',
            'site_editor_visibility' => 1
        ),
        'post_modified' => array(
            'show' => 1,
            'title' => esc_html('Date modified', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'timestamp', //timestamp, unix
            'set_day_end' => FALSE, //false: 00:00:00, true: 23:59:59
            'editable' => TRUE,
            'edit_view' => 'calendar',
            'order' => TRUE,
            'direct' => TRUE,
            'css_classes' => 'not-for-variations',
            'site_editor_visibility' => 1
        ),
        'post_modified_gmt' => array(
            'show' => 0,
            'title' => esc_html('Date modified GMT', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'timestamp', //timestamp, unix
            'set_day_end' => FALSE, //false: 00:00:00, true: 23:59:59
            'editable' => TRUE,
            'edit_view' => 'calendar',
            'order' => TRUE,
            'direct' => TRUE,
            'css_classes' => 'not-for-variations',
            'site_editor_visibility' => 1
        ),
        'post_parent' => array(
            'show' => 0,
            'title' => esc_html('Post parent', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'number',
            'editable' => TRUE,
            'edit_view' => 'textinput',
            'sanitize' => 'intval',
            'order' => FALSE,
            'direct' => TRUE,
            //'allow_post_types' => array(),
            //'prohibit_post_types' => array('attachment'),
            'site_editor_visibility' => 1
        ),
        'post_type' => array(
            'show' => 1,
            'title' => esc_html('Post type', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'select',
            'select_options' => WPBE_HELPER::filter_post_types(),
            'order' => FALSE,
            'direct' => TRUE,
            //'prohibit_post_types' => array(),
            'prohibit_post_types' => array('attachment'),
            'site_editor_visibility' => 1
        ),
        'post_mime_type' => array(
            'disabled' => TRUE,
            'show' => 0,
            'title' => esc_html('Post mime type', 'bulk-editor'),
            'desc' => esc_html('Not in bulk edit as there is no built-uploader here, so no sense change mime type. Filtering only! The function is designed specifically for attached records.', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'select',
            'select_options' => $post_mime_types,
            'order' => FALSE,
            'direct' => TRUE,
            'allow_post_types' => array('attachment'),
            'css_classes' => 'not-for-variations',
            'site_editor_visibility' => 1
        ),
        'sticky_posts' => array(
            'show' => 0,
            'title' => esc_html('Sticky', 'bulk-editor'),
            'desc' => esc_html('Stick this post to the front page', 'bulk-editor'),
            'field_type' => 'meta',
            'type' => 'intval',
            'editable' => TRUE,
            'edit_view' => 'switcher',
            'select_options' => array(
                1 => esc_html('Yes', 'bulk-editor'), //true
                0 => esc_html('No', 'bulk-editor'), //false
            ),
            'order' => FALSE,
            'direct' => TRUE,
            'allow_post_types' => array('post'),
            'prohibit_post_types' => array('attachment'),
            'site_editor_visibility' => 1,
            'css_classes' => '',
        ),
    ));
}
