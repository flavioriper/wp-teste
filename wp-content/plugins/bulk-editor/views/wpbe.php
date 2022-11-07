<?php
if (!defined('ABSPATH'))
    wp_die('No direct access allowed');

global $WPBE;
global $wp_version;

if (empty(WPBE_HELPER::filter_post_types())) {
    ?>
    <div class="notice notice-error">
        <p>
            <?php esc_html_e('No any post type permitted to edit for site editors. Ask please site administrators for details!', 'bulk-editor'); ?>
        </p>
    </div>
    <?php
    return;
}
?>

<div class="wpbe-admin-preloader">
    <div class="cssload-loader">
        <div class="cssload-inner cssload-one"></div>
        <div class="cssload-inner cssload-two"></div>
        <div class="cssload-inner cssload-three"></div>
    </div>
</div>

<!----------------------------- Filters ------------------------------------->

<?php echo WPBE_HELPER::render_html(WPBE_PATH . 'views/parts/top_panel.php'); ?>

<!----------------------------- Filters end ------------------------------------->

<div class="wrap nosubsub">

    <?php if (isset($_GET['settings_saved'])): ?>
        <div id="message" class="updated"><p><strong><?php esc_html_e("Your settings have been saved.", 'bulk-editor') ?></strong></p></div>
    <?php endif; ?>

    <section class="wpbe-section">
        <h3 class="wpbe-plugin-name"><?php printf('WPBE - Posts Bulk Editor Professional v.%s', WPBE_VERSION) ?></h3>

        <input type="hidden" name="wpbe_settings" value="" />

        <?php if (version_compare($wp_version, WPBE_MIN_WP_VERSION, '<')): ?>

            <div id="message" class="error fade"><p><strong><?php esc_html_e("ATTENTION! Your version of the WordPress is obsolete. There is no warranty of normal working with the plugin!!", 'bulk-editor') ?></strong></p></div>

        <?php endif; ?>

        <svg class="hidden">
        <defs>
        <path id="tabshape" d="M80,60C34,53.5,64.417,0,0,0v60H80z"/>
        </defs>
        </svg>


        <div id="tabs" class="wpbe-tabs wpbe-tabs-style-shape">

            <nav>
                <ul>
                    <li class="tab-current">
                        <a href="#tabs-posts" onclick="return wpbe_init_js_intab('tabs-posts')">
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <span><?php esc_html_e('Posts Editor', 'bulk-editor') ?></span>
                        </a>
                    </li>
                    <?php if (apply_filters('wpbe_show_tabs', true, 'settings')): ?>
                    <li>
                        <a href="#tabs-settings" onclick="return wpbe_init_js_intab('tabs-settings')">
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <span><?php esc_html_e("Settings", 'bulk-editor') ?></span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php do_action('wpbe_ext_panel_tabs'); //including extensions scripts          ?>

                </ul>
            </nav>

            <div class="content-wrap">

                <section id="tabs-posts" class="content-current">

                    <?php
                    $table_labels = array();
                    $edit_views = array();
                    $edit_sanitize = array();
                    $fields_types = array();
                    if (!empty($active_fields)) {
                        foreach ($active_fields as $key => $f) {
                            $title = $f['title'];
                            if (isset($f['title_static']) AND $f['title_static']) {
                                $title = $settings_fields[$key]['title'];
                            }
                            $table_labels[] = array('title' => $title, 'desc' => isset($f['desc']) ? $f['desc'] : '');
                            $edit_views[] = $f['edit_view'];
                            $edit_sanitize[] = isset($f['sanitize']) ? $f['sanitize'] : 'no';
                        }
                    }
                    $fk = $settings_fields_keys;


//***

                    if (empty($edit_views)) {
                        echo '<strong class="wpbe_set_attention">' . esc_html('Select some columns in tab "Settings"', 'bulk-editor') . '</strong><br /><br />';
                    }

                    $table_labels[] = array('title' => esc_html('Actions', 'bulk-editor'), 'desc' => '');
                    echo WPBE_HELPER::render_html(WPBE_PATH . 'views/parts/advanced-table.php', array(
                        'table_data' => array(
                            'editable' => implode(',', $editable),
                            'default-sort-by' => $default_sortby_col_num,
                            'sort' => $default_sort,
                            'no-order' => implode(',', $no_order),
                            'per-page' => $per_page,
                            'extend_per-page' => $extend_per_page,
                            'additional' => '',
                            'start-page' => isset($_GET['start_page']) ? intval($_GET['start_page']) : 0,
                            'fields' => implode(',', $fk),
                            'edit_views' => (!empty($edit_views) ? implode(',', $edit_views) : ''),
                            'edit_sanitize' => (!empty($edit_sanitize) ? implode(',', $edit_sanitize) : ''),
                        ),
                        'table_labels' => $table_labels
                    ));
                    ?>


                    <?php if (!empty($tax_keys)): ?>
                        <div id="taxonomies_popup" style="display: none;">

                            <div class="wpbe-modal wpbe-modal2 wpbe-style">
                                <div class="wpbe-modal-inner">
                                    <div class="wpbe-modal-inner-header">
                                        <h3 class="wpbe-modal-title">&nbsp;</h3>
                                        <a href="javascript:void(0)" class="wpbe-modal-close wpbe-modal-close1"></a>
                                    </div>
                                    <div class="wpbe-modal-inner-content">
                                        <div class="wpbe-form-element-container">
                                            <div class="wpbe-name-description">
                                                <strong><?php esc_html_e('Quick search', 'bulk-editor') ?></strong>
                                                <span><?php esc_html_e('Quick terms search by its name', 'bulk-editor') ?></span>
                                            </div>
                                            <div class="wpbe-form-element">
                                                <input type="text" class="wpbe_popup_option" id="term_quick_search" value="" /><br />
                                                <a href="#" class="wpbe_create_new_term"><?php esc_html_e('create new term', 'bulk-editor') ?></a>&nbsp;|&nbsp;
                                                <input type="checkbox" id="taxonomies_popup_list_checked_only" value="0" /><label for="taxonomies_popup_list_checked_only"><?php esc_html_e('selected only', 'bulk-editor') ?></label>
                                                <input type="checkbox" id="taxonomies_popup_select_all_terms" value="0" /><label for="taxonomies_popup_select_all_terms"><?php esc_html_e('Select all terms', 'bulk-editor') ?></label>

                                            </div>
                                        </div>

                                        <div class="wpbe-form-element-container">
                                            <ul id="taxonomies_popup_list"></ul>
                                        </div>
                                    </div>
                                    <div class="wpbe-modal-inner-footer">
                                        <a href="javascript:void(0)" class="wpbe-modal-close1 button button-primary button-large button-large-2"><?php esc_html_e('Cancel', 'bulk-editor') ?></a>
                                        <a href="javascript:void(0)" class="wpbe-modal-save1 button button-primary button-large button-large-1"><?php esc_html_e('Apply', 'bulk-editor') ?></a>
                                    </div>
                                </div>
                            </div>

                            <div class="wpbe-modal-backdrop"></div>

                        </div>
                    <?php endif; ?>

                    <?php if ($is_popupeditor): ?>
                        <div id="popupeditor_popup" style="display: none;">
                            <div class="wpbe-modal wpbe-modal2 wpbe-style">
                                <div class="wpbe-modal-inner">
                                    <div class="wpbe-modal-inner-header">
                                        <h3 class="wpbe-modal-title">&nbsp;</h3>
                                        <a href="javascript:void(0)" class="wpbe-modal-close wpbe-modal-close2"></a>
                                    </div>
                                    <div class="wpbe-modal-inner-content">
                                        <div class="wpbe-form-element-container">
                                            <div id="wpbe-modal-content-popupeditor">
                                                <div class="wpbe-form-element-container">
                                                    <?php
                                                    wp_editor('', 'popupeditor', array(
                                                        'editor_height' => 325
                                                    ));
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wpbe-modal-inner-footer">
                                        <a href="javascript:void(0)" class="wpbe-modal-close2 button button-primary button-large button-large-2"><?php esc_html_e('Cancel', 'bulk-editor') ?></a>
                                        <a href="javascript:void(0)" class="wpbe-modal-save2 button button-primary button-large button-large-1"><?php esc_html_e('Apply', 'bulk-editor') ?></a>
                                    </div>
                                </div>
                            </div>

                            <div class="wpbe-modal-backdrop"></div>

                        </div>
                    <?php endif; ?>

                    <?php if (isset($is_gallery) AND $is_gallery): ?>
                        <div id="gallery_popup_editor" style="display: none;">
                            <div class="wpbe-modal wpbe-modal2 wpbe-style">
                                <div class="wpbe-modal-inner">
                                    <div class="wpbe-modal-inner-header">
                                        <h3 class="wpbe-modal-title">&nbsp;</h3>
                                        <a href="javascript:void(0)" class="wpbe-modal-close wpbe-modal-close4"></a>
                                    </div>
                                    <div class="wpbe-modal-inner-content">
                                        <div class="wpbe-form-element-container">
                                            <div id="wpbe-modal-content-popupeditor">
                                                <div class="wpbe-form-element-container">
                                                    <a href="#" class="wpbe-button wpbe_insert_gall_file" data-place="top"><?php esc_html_e('Add Image', 'bulk-editor') ?></a><br />
                                                    <br />
                                                    <div id="wpbe_gallery_bulk_operations">
                                                        <div class="col-lg-12">

                                                            <select id="wpbe_gall_operations">
                                                                <option value="new"><?php esc_html_e('Replace all posts images by the selected ones', 'bulk-editor') ?></option>
                                                                <option value="add"><?php esc_html_e('Add selected images to the already existed ones', 'bulk-editor') ?></option>
                                                                <option value="delete"><?php esc_html_e('Delete selected images from the posts', 'bulk-editor') ?></option>
                                                                <option value="delete_forever"><?php esc_html_e('Delete selected images from the posts, also delete them from the media library forever', 'bulk-editor') ?></option>
                                                            </select>


                                                        </div>

                                                        <div class="clear"></div>

                                                    </div>


                                                    <form method="post" action="" id="posts_gallery_form"></form>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wpbe-modal-inner-footer">
                                        <a href="javascript:void(0)" class="wpbe-modal-close4 button button-primary button-large button-large-2"><?php esc_html_e('Cancel', 'bulk-editor') ?></a>
                                        <a href="javascript:void(0)" class="wpbe-modal-save4 button button-primary button-large button-large-1"><?php esc_html_e('Apply', 'bulk-editor') ?></a>
                                    </div>
                                </div>
                            </div>

                            <div class="wpbe-modal-backdrop"></div>

                        </div>

                        <div style="display: none;" id="wpbe_gallery_li_tpl">
                            <li>
                                <img src="__IMG_URL__" alt="" class="wpbe_gal_img_block" />
                                <a href="#" class="wpbe_gall_file_delete"><img src="<?php echo WPBE_ASSETS_LINK . 'images/delete2.png' ?>" alt="" /></a>
                                <input type="hidden" name="wpbe_gallery_images[]" value="__ATTACHMENT_ID__" />
                            </li>
                        </div>

                    <?php endif; ?>

                    <?php $meta_popup_editor = TRUE; //for bulk edit  ?>
                    <?php if ($meta_popup_editor): ?>
                        <div id="meta_popup_editor" style="display: none;">
                            <div class="wpbe-modal wpbe-modal2 wpbe-style">
                                <div class="wpbe-modal-inner">
                                    <div class="wpbe-modal-inner-header">
                                        <h3 class="wpbe-modal-title">&nbsp;</h3>
                                        <a href="javascript:void(0)" class="wpbe-modal-close wpbe-modal-close10"></a>
                                    </div>
                                    <div class="wpbe-modal-inner-content">

                                        <div class="wpbe-form-element-container">
                                            <div class="row">
                                                <div class="col-lg-10">
                                                    <h4><?php esc_html_e('REMEMBER: sequence of data maybe have sense (and maybe not), so be attentive! Do not mix in the same popup window data of array and object data!', 'bulk-editor') ?></h4>
                                                </div>
                                                <div class="col-lg-2">
                                                    &nbsp;<a href="https://wp.bulk-editor.com/document/bulk-edit-of-serialized-jsoned-meta-data/" target="_blank" class="button button-primary wpbe-info-btn"><span class="icon-book"></span>&nbsp;<?php esc_html_e('Documentation', 'bulk-editor') ?></a>
                                                </div>
                                            </div>

                                            <div class="clear"></div>


                                            <a href="#" class="wpbe-button meta_popup_editor_insert_new" data-place="top"><?php esc_html_e('Prepend array key/value', 'bulk-editor') ?></a>&nbsp;
                                            <a href="#" class="wpbe-button meta_popup_editor_insert_new_o" data-place="top"><?php esc_html_e('Prepend object set', 'bulk-editor') ?></a><br />

                                            <form method="post" action="" id="meta_popup_editor_form"></form>

                                            <a href="#" class="wpbe-button meta_popup_editor_insert_new" data-place="bottom"><?php esc_html_e('Append array key/value', 'bulk-editor') ?></a>&nbsp;
                                            <a href="#" class="wpbe-button meta_popup_editor_insert_new_o" data-place="bottom"><?php esc_html_e('Append object set', 'bulk-editor') ?></a><br />

                                        </div>
                                    </div>
                                    <div class="wpbe-modal-inner-footer">
                                        <a href="javascript:void(0)" class="wpbe-modal-close10 button button-primary button-large button-large-2"><?php esc_html_e('Cancel', 'bulk-editor') ?></a>
                                        <a href="javascript:void(0)" class="wpbe-modal-save10 button button-primary button-large button-large-1"><?php esc_html_e('Apply', 'bulk-editor') ?></a>
                                    </div>
                                </div>
                            </div>

                            <div class="wpbe-modal-backdrop"></div>

                        </div>


                        <div style="display: none;" id="meta_popup_editor_li">
                            <li class="wpbe_options_li">
                                <a href="#" class="help_tip wpbe_drag_and_drope wpbe_drag_and_drope_moved" title="<?php esc_html_e('drag and drop', 'bulk-editor') ?>"><img src="<?php echo WPBE_ASSETS_LINK ?>images/move.png" alt="<?php esc_html_e('move', 'bulk-editor') ?>" /></a>
                                <small><?php esc_html_e('key', 'bulk-editor') ?>:</small><input type="text" value="__KEY__" class="meta_popup_editor_li_key" name="keys[]" /><br />
                                <small><?php esc_html_e('value', 'bulk-editor') ?>:</small><input type="text" value="__VALUE__" class="meta_popup_editor_li_value" name="values[]" />
                                <a href="#" class="wpbe_prod_delete"><img src="<?php echo WPBE_ASSETS_LINK . 'images/delete2.png' ?>" alt="" /></a>
                                __CHILD_LIST__
                            </li>
                        </div>

                        <div style="display: none;" id="meta_popup_editor_li_o">
                            <li class="wpbe_options_li">
                                <a href="#" class="help_tip wpbe_drag_and_drope wpbe_drag_and_drope_moved" title="<?php esc_html_e('drag and drop', 'bulk-editor') ?>"><img src="<?php echo WPBE_ASSETS_LINK ?>images/move.png" alt="<?php esc_html_e('move', 'bulk-editor') ?>" /></a>
                                <small><?php esc_html_e('key', 'bulk-editor') ?>:</small><input type="text" value="__KEY__" class="meta_popup_editor_li_key" name="keys[]" /><br />
                                <a href="#" class="wpbe_prod_delete"><img src="<?php echo WPBE_ASSETS_LINK . 'images/delete2.png' ?>" alt="" /></a>
                                __CHILD_LIST__
                            </li>
                        </div>

                        <div style="display: none;" id="meta_popup_editor_li_object">
                            <li class="wpbe_options_li">
                                <small><?php esc_html_e('key', 'bulk-editor') ?>:</small><br /><input type="text" value="__KEY__" class="meta_popup_editor_li_key meta_popup_editor_li_key2" name="keys2[]" /><br />
                                <small><?php esc_html_e('value', 'bulk-editor') ?>:</small><br /><textarea class="meta_popup_editor_li_value meta_popup_editor_li_value2" name="values2[]">__VALUE__</textarea>
                                <a href="#" class="wpbe_prod_delete"><img src="<?php echo WPBE_ASSETS_LINK . 'images/delete2.png' ?>" alt="" /></a>
                            </li>
                        </div>

                    <?php endif; ?>

                    <div class="row clear">
                        <div class="col-lg-3">
                            <a href="https://wp.bulk-editor.com/document/posts-editor/" target="_blank" class="button button-primary wpbe-info-btn"><span class="icon-book"></span>&nbsp;<?php esc_html_e('Documentation', 'bulk-editor') ?></a><br />
                        </div>

                        <div class="col-lg-9 wpbe-text-align-right">
                            <small>* <i class="wpbe_set_attention"><?php esc_html_e('Note: if horizontal scroll disappeared when it must be visible, click on tab Posts Editor to make it visible', 'bulk-editor') ?></i></small><br />
                        </div>

                    </div>
                    <br />


                </section>

                <section id="tabs-settings">
                    <form id="mainform" method="post" action="">
                        <table class="wpbe-full-width">
                            <tr>
                                <td class="general-settings-td-2">
                                    <?php
                                    $fields_all = $settings_fields;
                                    $fields_all_checked = array();
                                    $fields_all_unchecked = array();

                                    foreach ($fields_all as $key => $f) {
                                        if (intval($f['show']) === 1) {
                                            $fields_all_checked[$key] = $f;
                                        } else {
                                            $fields_all_unchecked[$key] = $f;
                                        }
                                    }
                                    ?>
                                    <h4><?php printf(esc_html('Columns settings%s, columns enabled %s', 'bulk-editor'), ($show_notes ? '' : ' ' . (count($fields_all) - 1)), count($fields_all_checked) - 1) ?></h4>
                                    <ul class="wpbe_fields">
                                        <li class="unsortable">
                                            <input type="text" value="" class="wpbe-full-width" placeholder="<?php esc_html_e('columns finder ...', 'bulk-editor') ?>" id="wpbe_columns_finder" /><br />
                                        </li>
                                        <?php
//***
//lets show selected columns on the top
                                        $columns_colors = array();
                                        foreach (array($fields_all_checked, $fields_all_unchecked) as $counter => $ff):

                                            if ($counter > 0 AND ! empty($fields_all_unchecked)):
                                                ?>
                                                <li class="wpbe_options_li">
                                                    <a href="#" id="show_all_columns"><?php esc_html_e('Show all columns', 'bulk-editor') ?></a>
                                                </li>
                                                <?php
                                            endif;
                                            if (!empty($ff)):
                                                foreach ($ff as $key => $f) :
                                                    if (!$f['direct']) {
                                                        //continue;
                                                    }
                                                    ?>
                                                    <?php if (!empty($f['title'])): ?>
                                                        <li class="wpbe_options_li <?php if (isset($f['move'])): ?>unsortable<?php endif; ?>" <?php if ($counter > 0): ?>style="display: none;"<?php endif; ?>>

                                                            <div class="col-lg-6">
                                                                <div class="wpbe-h7px"></div>

                                                                <?php if (!isset($f['move'])): ?>
                                                                    <a href="#" class="help_tip wpbe_drag_and_drope" title="<?php esc_html_e('drag and drop', 'bulk-editor') ?><?php echo ($show_notes ? ' - ' . esc_html('premium version', 'bulk-editor') : '') ?>"><img src="<?php echo WPBE_ASSETS_LINK ?>images/move.png" alt="<?php esc_html_e('move', 'bulk-editor') ?>" /></a>
                                                                <?php endif; ?>

                                                                <?php if ($f['field_type'] !== 'none'): ?>
                                                                    <?php if (isset($f['title_static']) AND $f['title_static']): ?>
                                                                        <input type="text" name="wpbe_options[fields][<?php echo $key ?>][title]" value="<?php echo $settings_fields[$key]['title'] ?>" readonly="" class="wpbe_column_li_option" /><br />
                                                                    <?php else: ?>
                                                                        <input type="text" name="wpbe_options[fields][<?php echo $key ?>][title]" value="<?php echo $f['title'] ?>" class="wpbe_column_li_option" /><br />
                                                                    <?php endif; ?>
                                                                <?php else: ?>
                                                                    <?php echo $f['desc'] ?><br />
                                                                    <input type="hidden" name="wpbe_options[fields][<?php echo $key ?>][title]" value="" /><br />
                                                                    <div class="wpbe-h10px"></div>
                                                                <?php endif; ?>

                                                                <br />

                                                                <?php if (!in_array($key, array('__checker', 'ID')) AND $current_user_role == 'administrator'): ?>
                                                                    <input type="checkbox" value="1" <?php checked($f['site_editor_visibility']) ?> class="site_editor_visibility" data-key="<?php echo esc_html($key) ?>" id="site_editor_visibility_<?php echo $key ?>" />&nbsp;<label for="site_editor_visibility_<?php echo $key ?>"><?php esc_html_e('visible for the site editor', 'bulk-editor') ?>
                                                                        <?php if ($show_notes): ?><br /><small class="wpbe-free-version">(<?php esc_html_e('premium version', 'bulk-editor') ?>)</small><?php endif; ?></label><br />
                                                                    <input type="hidden" name="wpbe_options[fields][<?php echo $key ?>][site_editor_visibility]" value="<?php echo $f['site_editor_visibility'] ?>" />
                                                                <?php endif; ?>
                                                            </div>


                                                            <div class="col-lg-3">

                                                                <?php
                                                                $col_color = '';
                                                                $txt_color = '';

                                                                if (isset($options['fields'][$key]['col_color'])) {
                                                                    $col_color = $options['fields'][$key]['col_color'];
                                                                }

                                                                if (isset($options['fields'][$key]['txt_color'])) {
                                                                    $txt_color = $options['fields'][$key]['txt_color'];
                                                                }

                                                                $columns_colors[$key] = array(
                                                                    'col_color' => $col_color,
                                                                    'txt_color' => $txt_color
                                                                );
                                                                ?>
                                                                <div class="wpbe_column_color_pickers">
                                                                    <input type="text" name="wpbe_options[fields][<?php echo $key ?>][col_color]" value="<?php echo $col_color ?>" class="wpbe-color-picker" />
                                                                    <input type="text" name="wpbe_options[fields][<?php echo $key ?>][txt_color]" value="<?php echo $txt_color ?>" class="wpbe-color-picker" />
                                                                </div>


                                                            </div>

                                                            <?php if (isset($f['move'])): ?>
                                                                <!-------------------- always visible and not switchable ----------------------------->
                                                                <input type="hidden" value="1" name="wpbe_options[fields][<?php echo $key ?>][show]" />
                                                            <?php else: ?>
                                                                <div class="col-lg-2 wpbe-text-align-right">

                                                                    <?php echo WPBE_HELPER::draw_advanced_switcher(intval(isset($active_fields[$key])), $key, 'wpbe_options[fields][' . $key . '][show]', array('true' => '', 'false' => ''), array('true' => 1, 'false' => 0), 'wpbe_fshow_' . $key); ?>

                                                                </div>
                                                            <?php endif; ?>



                                                            <div class="col-lg-1 wpbe-text-align-right">
                                                                <?php if (isset($f['desc'])): ?>
                                                                    <div class="wpbe_options_li_desc">
                                                                        <br />
                                                                        <?php echo WPBE_HELPER::draw_tooltip($f['desc']); ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>

                                                            <div class="clear"></div>


                                                        </li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            <?php endif; ?>


                                        <?php endforeach; ?>


                                    </ul>


                                    <br />

                                    <input type="submit" class="button button-primary button-primary" value="<?php esc_html_e('Save all settings', 'bulk-editor') ?>" />


                                </td>
                                <td class="general-settings-td">
                                    <h4><?php esc_html_e('General settings', 'bulk-editor') ?> - <a href="https://wp.bulk-editor.com/document/settings/" target="_blank" class="button button-primary wpbe-info-btn"><span class="icon-book"></span>&nbsp;<?php esc_html_e('Documentation', 'bulk-editor') ?></a></h4>

                                    <?php foreach ($total_settings as $k => $o) : ?>
                                        <div class="wpbe-control-section">
                                            <h5><?php echo $o['title'] ?></h5>
                                            <div class="wpbe-control-container">
                                                <div class="wpbe-control">

                                                    <?php
                                                    switch ($o['type']) {
                                                        case 'select':
                                                            ?>
                                                            <div class="select-wrap">
                                                                <select name="wpbe_options[options][<?php echo $k ?>]">
                                                                    <?php foreach ($o['select_options'] as $kk => $vv) : ?>
                                                                        <option <?php selected($kk == $o['value']) ?> value="<?php echo $kk ?>"><?php echo $vv['title'] ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <?php
                                                            break;

                                                        case 'number':
                                                            ?>
                                                            <input type="number" name="wpbe_options[options][<?php echo $k ?>]" value="<?php echo $o['value'] ?>" />
                                                            <?php if ($show_notes): ?><br /><small class="wpbe-free-version">(<?php esc_html_e('premium version', 'bulk-editor') ?>)</small><?php endif; ?>
                                                            <?php
                                                            break;


                                                        default:
                                                            //textinput
                                                            if (is_array($o['value'])) {
                                                                $val = explode(',', $o['value']);
                                                            } else {
                                                                $val = $o['value'];
                                                            }
                                                            ?>
                                                            <input type="text" name="wpbe_options[options][<?php echo $k ?>]" value="<?php echo $val ?>" />
                                                            <?php
                                                            break;
                                                    }
                                                    ?>

                                                </div>
                                                <div class="wpbe-description">
                                                    <p class="description"><?php echo $o['desc'] ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>


                                    <?php if ($WPBE->settings->current_user_role == 'administrator') : ?>

                                        <div class="wpbe-control-section">
                                            <h5><?php esc_html_e('Site editors post types', 'bulk-editor') ?></h5>
                                            <div class="wpbe-control-container">
                                                <div class="wpbe-control">

                                                    <input type="text" name="wpbe_site_editors_post_types" value="<?php echo WPBE_HELPER::get_site_editors_post_types() ?>">

                                                </div>
                                                <div class="wpbe-description">
                                                    <p class="description"><?php esc_html_e('What post types can edit site editors in the tab Posts Editor. Use comma, example: post,car,page', 'bulk-editor') ?></p>
                                                </div>
                                            </div>
                                        </div>

                                    <?php endif; ?>

                                    <hr />


                                    <br />

                                    <input type="submit" class="button button-primary button-primary" value="<?php esc_html_e('Save all settings', 'bulk-editor') ?>" />


                                </td>
                            </tr>
                        </table>

                    </form>
                </section>


                <!--------------------------------- taxonomies terms data ---------------------------------------------->
                <div style="display: none;">
                    <div id="taxonomies_popup_list_li_tpl">
                        <li data-search-value="__SEARCH_TXT__" class="quick_search_element __TOP_LI__">
                            <div class="quick_search_element_container">
                                <input type="checkbox" __CHECK__ name="wpbe_tax_terms[]" value="__TERM_ID__" id="term___TERM_ID__">&nbsp;<label for="term___TERM_ID__">__LABEL__</label><br>
                            </div>
                            __CHILDS__
                        </li>
                    </div>
                </div>



                <script>
                    var taxonomies_terms = {};

<?php if (!empty($tax_keys)): ?>
    <?php foreach ($tax_keys as $tax_key) : ?>

                            taxonomies_terms['<?php echo $tax_key ?>'] =<?php echo json_encode(WPBE_HELPER::get_taxonomies_terms_hierarchy($tax_key)) ?>;

    <?php endforeach; ?>
<?php endif; ?>



                    var wpbe_active_fields =<?php echo json_encode($settings_fields_full) ?>;
                </script>



                <?php do_action('wpbe_ext_panel_tabs_content'); //including extensions scripts          ?>


                <div class="clear"></div>


            </div>

        </div>


    </section><!--/ .wpbe-section-->
    <div class="made_by">
        <a href="https://pluginus.net/" target="_blank">Created by PluginUs.NET</a><br />
    </div>
    <div class="clear"></div>


    <div id="wpbe_buffer" style="display: none;"></div>

    <div id="wpbe_html_buffer" class="wpbe_info_popup" style="display: none;"></div>


    <!-------------------------------- advanced panel popups ------------------------------------------->

    <div id="wpbe_tools_panel_profile_popup" style="display: none;">
        <div class="wpbe-modal wpbe-modal2 wpbe-style">
            <div class="wpbe-modal-inner">
                <div class="wpbe-modal-inner-header">
                    <h3 class="wpbe-modal-title"><?php esc_html_e('Columns profile', 'bulk-editor') ?></h3>
                    <a href="javascript:void(0)" class="wpbe-modal-close wpbe-modal-close8"></a>
                </div>
                <div class="wpbe-modal-inner-content">

                    <div class="wpbe-form-element-container">
                        <div class="wpbe-name-description">
                            <strong><?php esc_html_e('Columns profiles', 'bulk-editor') ?></strong>
                            <span><?php esc_html_e('Here you can load previously saved columns profile. After pressing on the load button, page reloading will start immediately!', 'bulk-editor') ?></span>

                            <?php if (isset($current_profile['title'])): ?>
                                <span class="current_profile_disclaimer"><?php
                                    printf(esc_html('Current profile is: %s %s', 'bulk-editor'), $current_profile['title'], WPBE_HELPER::draw_link(array(
                                                'href' => $current_profile['key'],
                                                'title' => WPBE_HELPER::draw_image(WPBE_ASSETS_LINK . 'images/delete.png', '', '', 15),
                                                'class' => 'wpbe_delete_profile',
                                                'title_attr' => esc_html('remove current columns profile', 'bulk-editor')
                                    )))
                                    ?></span>
                            <?php endif; ?>

                        </div>
                        <div class="wpbe-form-element">
                            <div class="posts_search_container">
                                <select id="wpbe_load_profile">
                                    <option value="0"><?php esc_html_e('Select profile to load', 'bulk-editor') ?></option>
                                    <?php foreach ($profiles as $pkey => $pvalue) : ?>
                                        <option <?php selected((isset($current_profile['key']) ? $current_profile['key'] === trim($pkey) : false)) ?> value="<?php echo $pkey ?>"><?php echo $pvalue['title'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="cssload-container" style="display: none;">
                                    <div class="cssload-whirlpool"></div>
                                </div><br />

                                <div style="display: none;"  id="wpbe_load_profile_actions">
                                    <a href="javascript:void(0)" class="button button-primary button" id="wpbe_load_profile_btn"><?php esc_html_e('load', 'bulk-editor') ?></a>&nbsp;
                                    <a href="#" class="button button-primary button wpbe_delete_profile"><?php esc_html_e('remove', 'bulk-editor') ?></a>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="wpbe-form-element-container">
                        <div class="wpbe-name-description">
                            <strong><?php esc_html_e('New Profile', 'bulk-editor') ?></strong>
                            <span><?php esc_html_e('Here you can type any title and save current columns set and their order. Type here any title and then press Save button OR press Enter button on your keyboard!', 'bulk-editor') ?></span>
                        </div>
                        <div class="wpbe-form-element">
                            <div class="posts_search_container">
                                <input type="text" value="" id="wpbe_new_profile" />
                            </div>
                        </div>
                    </div>

                    <!-- <div class="wpbe-form-element-container"></div> -->
                </div>
                <div class="wpbe-modal-inner-footer">
                    <a href="javascript:void(0)" class="button button-primary button-large button-large-1"  id="wpbe_new_profile_btn"><?php esc_html_e('Create', 'bulk-editor') ?></a>
                    <a href="javascript:void(0)" class="wpbe-modal-close8 button button-primary button-large button-large-2"><?php esc_html_e('Close', 'bulk-editor') ?></a>
                </div>
            </div>
        </div>

        <div class="wpbe-modal-backdrop"></div>

    </div>


    <div id="wpbe_new_term_popup" style="display: none;">
        <div class="wpbe-modal wpbe-modal2 wpbe-style">
            <div class="wpbe-modal-inner">
                <div class="wpbe-modal-inner-header">
                    <h3 class="wpbe-modal-title"><?php printf(esc_html('New term for [%s]', 'bulk-editor'), '<span></span>') ?></h3>
                    <a href="javascript:void(0)" class="wpbe-modal-close wpbe-modal-close9"></a>
                </div>
                <div class="wpbe-modal-inner-content">

                    <div class="wpbe-form-element-container">
                        <div class="wpbe-name-description">
                            <strong><?php esc_html_e('New Term(s)', 'bulk-editor') ?></strong>
                            <span><?php esc_html_e('Here you can write title for the new term. Use comma to create some new tags on the same time! New terms with already existed names will not be created!', 'bulk-editor') ?></span>
                        </div>
                        <div class="wpbe-form-element">
                            <input type="text" value="" id="wpbe_new_term_title" class="wpbe-full-width" />
                        </div>
                    </div>


                    <div class="wpbe-form-element-container">
                        <div class="wpbe-name-description">
                            <strong><?php esc_html_e('Slug(s) of the new term', 'bulk-editor') ?></strong>
                            <span><?php esc_html_e('Here you can write slug for the the new term (optionally). Use comma for slug(s) when you create some on the same time terms, or leave slug field empty to create slug(s) automatically', 'bulk-editor') ?></span>
                        </div>
                        <div class="wpbe-form-element">
                            <input type="text" value="" id="wpbe_new_term_slug" class="wpbe-full-width" />
                        </div>
                    </div>


                    <div class="wpbe-form-element-container">
                        <div class="wpbe-name-description">
                            <strong><?php esc_html_e('Parent of the new term(s)', 'bulk-editor') ?></strong>
                            <span><?php esc_html_e('Here you can select parent for the the new term (optionally)', 'bulk-editor') ?></span>
                        </div>
                        <div class="wpbe-form-element">
                            <select id="wpbe_new_term_parent"></select>
                        </div>
                    </div>

                </div>
                <div class="wpbe-modal-inner-footer">
                    <a href="#" class="button button-primary button-large button-large-1" id="wpbe_new_term_create"><?php esc_html_e('Create', 'bulk-editor') ?></a>
                    <a href="javascript:void(0)" class="wpbe-modal-close9 button button-primary button-large button-large-2"><?php esc_html_e('Cancel', 'bulk-editor') ?></a>
                </div>
            </div>
        </div>

        <div class="wpbe-modal-backdrop wpbe-modal-backdrop2"></div>

    </div>

    <?php do_action('wpbe_page_end') ?>


    <div class="external-scroll_wrapper">
        <div class="external-scroll_x">
            <div class="scroll-element_outer">
                <div class="scroll-element_size"></div>
                <div class="scroll-element_track"></div>
                <div class="scroll-bar"></div>
            </div>
        </div>
    </div>

</div>



<?php if ($show_notes): ?>
    <hr />

    <table class="wpbe-full-width">
        <tr>

            <td class="wpbe-width-33">
                <h3 class="wpbe-full-upgrade"><?php esc_html_e("UPGRADE TO FULL VERSION", 'bulk-editor') ?>:</h3>
                <a href="https://pluginus.net/affiliate/wordpress-posts-bulk-editor" target="_blank"><img src="<?php echo WPBE_LINK ?>assets/images/wpbe_banner.png" width="300" alt="<?php esc_html_e("WPBE - Posts Bulk Editor Professional", 'bulk-editor'); ?>" /></a>
            </td>

            <td class="wpbe-width-33">
                <h3><?php esc_html_e("WOOBE - WooCommerce Bulk Editor Professional", 'bulk-editor') ?>:</h3>
                <a href="https://bulk-editor.com/" target="_blank"><img src="<?php echo WPBE_LINK ?>assets/images/woobe_banner.png" width="300" alt="<?php esc_html_e("WOOBE - WooCommerce Bulk Editor Professional", 'bulk-editor'); ?>" /></a>
            </td>

            <td class="wpbe-width-33">
                <h3><?php esc_html_e("WPCS - WordPress Currency Switcher", 'bulk-editor') ?>:</h3>
                <a href="https://wordpress.currency-switcher.com/" target="_blank"><img src="<?php echo WPBE_LINK ?>assets/images/wpcs_banner.png" alt="<?php esc_html_e("WPCS - WordPress Currency Switcher", 'bulk-editor'); ?>" /></a>
            </td>



        </tr>
    </table>
<?php endif; ?>


<script>
    var wpbe_current_post_type = '<?= $WPBE->settings->current_post_type ?>';
</script>


<?php if (!empty($columns_colors)): ?>

    <style type="text/css">

        <?php foreach ($columns_colors as $key => $colors) : ?>

            <?php if (!empty($colors['col_color'])): ?>
                td[data-field="<?php echo esc_html($key) ?>"] {
                    background-color: <?php echo $colors['col_color'] ?>;
                }
            <?php endif; ?>


            <?php if (!empty($colors['txt_color'])): ?>
                td[data-field="<?php echo esc_html($key) ?>"] {
                    color: <?php echo $colors['txt_color'] ?>;
                }

                td[data-field="<?php echo esc_html($key) ?>"] select,
                td[data-field="<?php echo esc_html($key) ?>"] li.search-choice span,
                td[data-field="<?php echo esc_html($key) ?>"] li.wpbe_li_tag,
                td[data-field="<?php echo esc_html($key) ?>"] .wpbe-button,
                td[data-field="<?php echo esc_html($key) ?>"] input.wpbe_calendar,
                td[data-field="<?php echo esc_html($key) ?>"] .wpbe_btn_gal_block{
                    color: <?php echo $colors['txt_color'] ?> !important;
                }
            <?php endif; ?>

        <?php endforeach; ?>

    </style>

    <?php
endif;
?>