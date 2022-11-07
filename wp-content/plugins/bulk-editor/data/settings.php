<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function wpbe_get_total_settings($data) {
    return array(
        'per_page' => array(
            'title' => esc_html('Default posts count per page', 'bulk-editor'),
            'desc' => esc_html('How many rows shows per page in tab Posts Editor. Max possible value is 100!', 'bulk-editor'),
            'value' => '',
            'type' => 'number'
        ),
        'default_sort_by' => array(
            'title' => esc_html('Default sort by', 'bulk-editor'),
            'desc' => esc_html('Select column by which posts sorting is going after plugin page loaded', 'bulk-editor'),
            'value' => '',
            'type' => 'select',
            'select_options' => $data['default_sort_by']
        ),
        'default_sort' => array(
            'title' => esc_html('Default sort', 'bulk-editor'),
            'desc' => esc_html('Select sort direction for Default sort', 'bulk-editor'),
            'value' => '',
            'type' => 'select',
            'select_options' => array(
                'desc' => array('title' => 'DESC'),
                'asc' => array('title' => 'ASC')
            )
        ),
        'show_admin_bar_menu_btn' => array(
            'title' => esc_html('Show button in admin bar', 'bulk-editor'),
            'desc' => esc_html('Show Bulk Editor button in admin bar for quick access to the Posts Editor', 'bulk-editor'),
            'value' => '',
            'type' => 'select',
            'select_options' => array(
                1 => array('title' => esc_html('Yes', 'bulk-editor')),
                0 => array('title' => esc_html('No', 'bulk-editor')),
            )
        ),
        'show_thumbnail_preview' => array(
            'title' => esc_html('Show thumbnail preview', 'bulk-editor'),
            'desc' => esc_html('Show bigger thumbnail preview on mouse over', 'bulk-editor'),
            'value' => '',
            'type' => 'select',
            'select_options' => array(
                1 => array('title' => esc_html('Yes', 'bulk-editor')),
                0 => array('title' => esc_html('No', 'bulk-editor')),
            )
        ),
        'load_switchers' => array(
            'title' => esc_html('Load beauty switchers', 'bulk-editor'),
            'desc' => esc_html('Load beauty switchers instead of checkboxes in the Posts Editor.', 'bulk-editor'),
            'value' => '',
            'type' => 'select',
            'select_options' => array(
                1 => array('title' => esc_html('Yes', 'bulk-editor')),
                0 => array('title' => esc_html('No', 'bulk-editor')),
            )
        ),
        'quick_search_fieds' => array(
            'title' => __('Add fields to the quick search', 'bulk-editor'),
            'desc' => __('Adds more fields to quick search fields drop-down on the tools panel. Works only for text fields. Syntax: post_name:Post slug,post_content:Content', 'bulk-editor'),
            'value' => '',
            'type' => 'text'
        ),
    );
}
