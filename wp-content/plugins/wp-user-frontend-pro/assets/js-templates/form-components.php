<script type="text/x-template" id="tmpl-wpuf-field-address">
<div class="panel-field-opt panel-field-opt-address">
    <label class="label-hr">{{ option_field.title }}</label>

    <ul class="address-fields">
        <li
            v-for="(address, field) in editing_form_field.address"
        >
            <template v-if="'country_select' !== field">
                <div class="clearfix address-title-header">
                    <label class="pull-left">
                        <input
                            type="checkbox"
                            :checked="address.checked"
                            @click="toggle_address_checked(field)"
                        > {{ i18n[field] }}
                    </label>

                    <div class="pull-right">
                        <label v-show="show_details[field]">
                            <input
                                type="checkbox"
                                :checked="address.required"
                                @click="toggle_address_required(field)"
                            > <?php _e( 'Required', 'wpuf-pro' ); ?>
                        </label>

                        <button
                            type="button"
                            class="button button-link button-dropdown"
                            @click="toggle_show_details(field)"
                        >
                            <i class="fa fa-caret-down"></i>
                        </button>
                    </div>
                </div>

                <div v-show="show_details[field]" class="clearfix address-input-fields">
                    <p class="pull-left">
                        <label><?php _e( 'Label', 'wpuf-pro' ); ?> <input type="text" v-model="address.label"></label>
                    </p>

                    <p class="pull-left">
                        <label><?php _e( 'Default', 'wpuf-pro' ); ?> <input type="text" v-model="address.value"></label>
                    </p>

                    <p class="pull-left">
                        <label><?php _e( 'Placeholder', 'wpuf-pro' ); ?> <input type="text" v-model="address.placeholder"></label>
                    </p>
                </div>
            </template>

            <template v-else>
                <div class="clearfix address-title-header">
                    <label class="pull-left">
                        <input
                            type="checkbox"
                            :checked="address.checked"
                            @click="toggle_address_checked(field)"
                        > {{ i18n[field] }}
                    </label>

                    <div class="pull-right">
                        <label v-show="show_details[field]">
                            <input
                                type="checkbox"
                                :checked="address.required"
                                @click="toggle_address_required(field)"
                            > <?php _e( 'Required', 'wpuf-pro' ); ?>
                        </label>

                        <button
                            type="button"
                            class="button button-link button-dropdown"
                            @click="toggle_show_details(field)"
                        >
                            <i class="fa fa-caret-down"></i>
                        </button>
                    </div>
                </div>

                <div v-show="show_details[field]" class="clearfix address-input-fields country-label">
                    <p>
                        <label><?php _e( 'Label', 'wpuf-pro' ); ?> <input type="text" v-model="address.label"></label>
                    </p>
                </div>

                <div v-show="show_details[field]" class="address-country-default address-input-fields">
                    <label>
                        <?php _e( 'Default Country', 'wpuf-pro' ); ?>

                        <select class="default-country" v-model="default_country">
                            <option value=""><?php _e( 'Select Country', 'wpuf-pro' ); ?></option>
                            <option v-for="country in countries" :value="country.code">{{ country.name }}</option>
                        </select>
                    </label>

                    <div class="panel-field-opt-select country-list-selector-container">
                        <label class="label-title-block"><?php _e( 'Country List', 'wpuf-pro' ); ?></label>

                        <div class="button-group wpuf-flex-button-group">
                            <button
                                v-for="button in visibility_buttons"
                                type="button"
                                :class="['button', button.name === active_visibility ? 'active button-primary' : '' ]"
                                @click.prevent="set_visibility(button.name)"
                            >{{ button.title }}</button>
                        </div>

                        <select
                            v-show="'all' === active_visibility"
                            :class="['country-list-selector selectize-element-group', 'all' === active_visibility ? 'active' : '']"
                            disabled
                        >
                            <option value=""><?php _e( 'Select Countries', 'wpuf-pro' ); ?></option>
                        </select>

                        <select
                            v-show="'hide' === active_visibility"
                            :class="['country-list-selector selectize-element-group', 'hide' === active_visibility ? 'active' : '']"
                            v-model="country_in_hide_list"
                            data-visibility="hide"
                            multiple
                        >
                            <option v-for="country in countries" :value="country.code">{{ country.name }}</option>
                        </select>

                        <select
                            v-show="'show' === active_visibility"
                            :class="['country-list-selector selectize-element-group', 'show' === active_visibility ? 'active' : '']"
                            v-model="country_in_show_list"
                            data-visibility="show"
                            multiple
                        >
                            <option v-for="country in countries" :value="country.code">{{ country.name }}</option>
                        </select>
                    </div>
                </div>
            </template>
        </li>
    </ul>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-conditional-logic">
<div v-if="wpuf_cond && wpuf_cond.condition_status" class="panel-field-opt panel-field-opt-conditional-logic">
    <label>
        <?php _e( 'Conditional Logic', 'wpuf-pro' ); ?>
    </label>

    <ul class="list-inline">
        <li>
            <label><input type="radio" value="yes" v-model="wpuf_cond.condition_status"> <?php _e( 'Yes', 'wpuf-pro' ); ?></label>
        </li>

        <li>
            <label><input type="radio" value="no" v-model="wpuf_cond.condition_status"> <?php _e( 'No', 'wpuf-pro' ); ?></label>
        </li>
    </ul>

    <div v-if="'yes' === wpuf_cond.condition_status" class="condiotional-logic-container">
        <ul class="condiotional-logic-repeater">
            <li v-for="(condition, index) in conditions" class="clearfix">
                <div class="cond-field">
                    <select v-model="condition.name" @change="on_change_cond_field(index, condition.name)">
                        <option value=""><?php _e( '- select -', 'wpuf-pro' ); ?></option>
                        <option
                            v-for="dep_field in dependencies"
                            :value="dep_field.name"
                        >{{ dep_field.label }}</option>
                    </select>
                </div>

                <div class="cond-operator">
                    <select v-model="condition.operator">
                        <option value="="><?php _e( 'is', 'wpuf-pro' ); ?></option>
                        <option value="!="><?php _e( 'is not', 'wpuf-pro' ); ?></option>
                    </select>
                </div>

                <div class="cond-option">
                    <select v-model="condition.option">
                        <option value=""><?php _e( '- select -', 'wpuf-pro' ); ?></option>
                        <option
                            v-for="cond_option in get_cond_options(condition.name)"
                            :value="cond_option.opt_name"
                        >
                            {{ cond_option.opt_title }}
                        </option>
                    </select>
                </div>

                <div class="cond-action-btns">
                    <i class="fa fa-plus-circle" @click="add_condition"></i>
                    <i class="fa fa-minus-circle pull-right" @click="delete_condition(index)"></i>
                </div>
            </li>
        </ul>

        <p>
            <?php
                printf(
                    __( 'Show this field when %s of these rules are met', 'wpuf-pro' ),
                    '<select v-model="wpuf_cond.cond_logic"><option value="any">' . __( 'any', 'wpuf-pro' ) . '</option><option value="all">' . __( 'all', 'wpuf-pro' ) . '</option></select>'
                );
            ?>
        </p>
    </div>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-country-list">
<div v-if="met_dependencies">
    <div class="panel-field-opt panel-field-opt-select">
        <label>
            <?php _e( 'Default Country', 'wpuf-pro' ); ?>

            <select class="default-country" v-model="default_country">
                <option value=""><?php _e( 'Select Country', 'wpuf-pro' ); ?></option>
                <option v-for="country in countries" :value="country.code">{{ country.name }}</option>
            </select>
        </label>
    </div>

    <div class="panel-field-opt panel-field-opt-select">
        <label class="label-title-block"><?php _e( 'Country List', 'wpuf-pro' ); ?>
            <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        </label>
        <div class="button-group wpuf-flex-button-group">
            <button
                v-for="button in visibility_buttons"
                type="button"
                :class="['button', button.name === active_visibility ? 'active button-primary' : '' ]"
                @click.prevent="set_visibility(button.name)"
            >{{ button.title }}</button>
        </div>

        <select
            v-show="'all' === active_visibility"
            :class="['country-list-selector selectize-element-group', 'all' === active_visibility ? 'active' : '']"
            disabled
        >
            <option value=""><?php _e( 'Select Countries', 'wpuf-pro' ); ?></option>
        </select>

        <select
            v-show="'hide' === active_visibility"
            :class="['country-list-selector selectize-element-group', 'hide' === active_visibility ? 'active' : '']"
            v-model="country_in_hide_list"
            data-visibility="hide"
            multiple
        >
            <option v-for="country in countries" :value="country.code">{{ country.name }}</option>
        </select>

        <select
            v-show="'show' === active_visibility"
            :class="['country-list-selector selectize-element-group', 'show' === active_visibility ? 'active' : '']"
            v-model="country_in_show_list"
            data-visibility="show"
            multiple
        >
            <option v-for="country in countries" :value="country.code">{{ country.name }}</option>
        </select>
    </div>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-gmap-set-position">
<div>
    <label class="label-hr margin-bottom-10">
        <?php _e( 'Map Settings', 'wpuf-pro' ); ?>
    </label>

    <p>
        <em><?php _e( 'Set default co-ordinate and zoom level', 'wpuf-pro' ); ?></em>
    </p>

    <input class="wpuf-google-map-search" type="text" placeholder="<?php _e( 'Search address', 'wpuf-pro' ); ?>">
    <div class="wpuf-field-google-map"></div>

    <div class="panel-field-opt panel-field-opt-checkbox">
        <ul>
            <li>
                <label>
                    <input
                        type="checkbox"
                        :checked="'yes' === editing_form_field.address"
                        @click="toggle_checkbox_field('address')"
                    > <?php _e( 'Show address search box', 'wpuf-pro' ); ?>
                </label>
            </li>
        </ul>
    </div>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-math-captcha">
<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-text">
    <label>
        {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>

        <input
            v-if="option_field.variation && 'number' === option_field.variation"
            type="number"
            v-model="value"
            @focusout="on_focusout"
            @keyup="on_keyup"
        >

        <input
            v-if="!option_field.variation"
            type="text"
            v-model="value"
            @focusout="on_focusout"
            @keyup="on_keyup"
        >
    </label>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-repeater-columns">
<div v-show="met_dependencies" class="panel-field-opt panel-field-opt-repeater-columns">

    <label>
        {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </label>

    <ul class="repeater-columns">
        <li v-for="(column, index) in editing_form_field.columns" class="clearfix repeater-single-column" :data-index="index">
            <div class="sorter">
                <i class="fa fa-bars sort-handler"></i>
            </div>

            <div class="input-container">
                <input type="text" v-model="editing_form_field.columns[index]">
            </div>

            <div class="action-buttons">
                    <i class="fa fa-plus-circle" @click="add_column"></i>
                    <i class="fa fa-minus-circle" @click="delete_column(index)"></i>
            </div>
        </li>
    </ul>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-step-start">
<div>
    <div class="panel-field-opt panel-field-opt-text">
        <label>
            <?php _e( 'Section Name', 'wpuf-pro' ); ?> <help-text text="<?php _e( 'Title', 'wpuf-pro' ); ?>"></help-text>

            <input type="text" v-model="editing_form_field.label">
        </label>
    </div>

    <div class="panel-field-opt panel-field-opt-text">
        <label>
            <?php _e( 'Previous Button Text', 'wpuf-pro' ); ?> <help-text text="<?php _e( 'Previous Button Text', 'wpuf-pro' ); ?>"></help-text>

            <input type="text" v-model="editing_form_field.step_start.prev_button_text">
        </label>
    </div>

    <div class="panel-field-opt panel-field-opt-text">
        <label>
            <?php _e( 'Next Button Text', 'wpuf-pro' ); ?> <help-text text="<?php _e( 'Next Button Text', 'wpuf-pro' ); ?>"></help-text>

            <input type="text" v-model="editing_form_field.step_start.next_button_text">
        </label>
    </div>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-action_hook">
<div>
    <div class="wpuf-label"><?php _e( 'Action Hook', 'wpuf-pro' ); ?></div>

    <div class="wpuf-fields">
        <span v-if="!field.label"><em><?php _e( "hook name isn't set", 'wpuf-pro' ); ?></em></span>
        <span v-else>{{ field.label }}</span>
    </div>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-address_field">
<div>
    <div class="wpuf-label">
        <label for="addr_field_label">
            {{ field.label }} <span v-if="'yes' === field.required" class="required">*</span>
        </label>
    </div>

    <div class="wpuf-fields">

        <div v-for="(addr_field_details, addr_field) in field.address" :class="['wpuf-address-field', addr_field]" v-if="addr_field_details.checked">

            <div class="wpuf-sub-fields">
                <template v-if="'country_select' !== addr_field">
                    <input
                        type="text"
                        class="textfield"
                        size="40"
                        :value="addr_field_details.value"
                        :placeholder="addr_field_details.placeholder"
                        :required="'checked' === addr_field_details.required"
                        v-if="'text' == addr_field_details.type"
                    >
                    <select 
                        name="" 
                        id=""
                        v-if="'select' == addr_field_details.type"
                    >
                    <option value=""><?php _e( 'Select State', 'wpuf-pro' ); ?></option>
                    </select>
                </template>

                <template v-else>
                    <select :required="'checked' === addr_field_details.required" v-model=default_country>
                        <option value=""><?php _e( 'Select Country', 'wpuf-pro' ); ?></option>
                        <option v-for="country in countries" :value="country.code">{{ country.name }}</option>
                    </select>
                </template>
            </div>

            <label class="wpuf-form-sub-label">
                {{ addr_field_details.label }}
                <span v-if="'checked' === addr_field_details.required" class="required">*</span>
            </label>
        </div>

        <div class="clear"></div>
        <span v-if="field.help" class="wpuf-help" v-html="field.help"/>
    </div>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-avatar">
<div class="wpuf-fields">
    <div :id="'wpuf-img_label-' + field.id + '-upload-container'">
        <div class="wpuf-attachment-upload-filelist" data-type="file" data-required="yes">
            <a class="button file-selector" href="#">
                <template v-if="field.button_label === ''">
                    <?php _e( 'Select Image', 'wpuf-pro' ); ?>
                </template>
                <template v-else>
                    {{ field.button_label }}
                </template>
            </a>
        </div>
    </div>

    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-country_list_field">
<div class="wpuf-fields">
    <select v-model="default_country">
        <option value=""><?php _e( 'Select Country', 'wpuf-pro' ); ?></option>
        <option v-for="country in countries" :value="country.code">{{ country.name }}</option>
    </select>

    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-date_field">
<div class="wpuf-fields">
    <input
        type="text"
        :class="class_names('datepicker')"
        :placeholder="field.format"
        :value="field.default"
        :size="field.size"
    >
    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-display_name">
<div class="wpuf-fields">
    <input
        type="text"
        :class="class_names('textfield')"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
    >
    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-embed">
<div class="wpuf-fields">
    <input
        type="url"
    >
    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-file_upload">
<div class="wpuf-fields">
    <div :id="'wpuf-img_label-' + field.id + '-upload-container'">
        <div class="wpuf-attachment-upload-filelist" data-type="file" data-required="yes">
            <a class="button file-selector wpuf_img_label_148" href="#">
                <?php _e( 'Select Files', 'wpuf-pro' ); ?>
            </a>
        </div>
    </div>

    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
</script>

<script type="text/x-template" id="tmpl-wpuf-form-first_name">
<div class="wpuf-fields">
    <input
        type="text"
        :class="class_names('textfield')"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
    >
    <span v-if="field.help" class="wpuf-help" v-html="field.help"></span>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-google_map">
<div class="wpuf-fields">
    <?php if ( ! empty( wpuf_get_option( 'gmap_api_key', 'wpuf_general' ) ) ): ?>
    <div :class="['wpuf-form-google-map-container', 'yes' === field.address ? 'show-search-box': 'hide-search-box']">
        <input class="wpuf-google-map-search" type="text" placeholder="<?php _e( 'Search address', 'wpuf-pro' ); ?>">
        <div class="wpuf-form-google-map"></div>
    </div>
    <div :class="['wpuf-fields clearfix', field.directions ? 'has-directions-checkbox' : '']">
        <span v-if="field.directions" class="wpuf-directions-checkbox">
            <a class="btn btn-brand btn-sm" href="#" ><i class="fa fa-map-marker" aria-hidden="true"></i><?php _e( 'Directions »', 'wpuf-pro' ); ?></a>
        </span>
    </div>
    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
    <?php else: ?>
        <p><?php esc_html_e( 'Please set Google Map API Key first in WPUF admin settings to use this Google Map field.', 'wpuf-pro' ); ?></p>
    <?php endif; ?>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-last_name">
<div class="wpuf-fields">
    <input
        type="text"
        :class="class_names('textfield')"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
    >
    <span v-if="field.help" class="wpuf-help" v-html="field.help"> </span>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-math_captcha">
<div class="wpuf-fields">
    <div class="wpuf-math-captcha">
        <ul class="captcha">
            <li class="refresh">
                <svg fill="#555555" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" baseProfile="tiny" x="0px" y="0px" width="30px" height="30px" viewBox="0 0 480 480" xml:space="preserve" style="vertical-align: bottom;">
                    <g>
                        <path d="M383.434,172.242c-25.336-58.241-81.998-95.648-145.861-95.648c-65.309,0-125,40.928-148.514,101.827l49.5,19.117   c15.672-40.617,55.469-67.894,99.014-67.894c42.02,0,79.197,24.386,96.408,62.332l-36.117,14.428l92.352,53.279l27.01-100.933   L383.434,172.242z"></path>
                        <path d="M237.573,342.101c-41.639,0-79.615-25.115-96.592-62.819l35.604-13.763l-91.387-52.119l-27.975,98.249l34.08-13.172   c24.852,58.018,82.859,96.671,146.27,96.671c65.551,0,123.598-39.336,147.871-100.196l-49.268-19.652   C319.981,315.877,281.288,342.101,237.573,342.101z"></path>
                    </g>
                </svg>
            </li>
            <li class="captcha-number-area">
                <p class="captcha-number">
                    <span v-html="captcha.operandOne"></span>
                    <span v-html="captcha.operator"></span>
                    <span v-html="captcha.operandTwo"></span>
                </p>
            </li>
            <li class="captcha-equal">=</li>
            <li>
                <input
                    type="text"
                    :class="class_names('textfield')"
                    :placeholder="field.placeholder"
                    :value="field.default"
                    :size="field.size"
                >
            </li>
        </ul>
    </div>
    
    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
</div>

</script>

<script type="text/x-template" id="tmpl-wpuf-form-nickname">
<div class="wpuf-fields">
    <input
        type="text"
        :class="class_names('textfield')"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
    >
    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-numeric_text_field">
<div class="wpuf-fields">
    <input
        type="number"
        :class="class_names('textfield')"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
    >
    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-password">
<div>

    <div class="wpuf-password-field">
        <div class="wpuf-label">
            <label :for="'wpuf-' + field.name ? field.name : 'cls'">
                {{ field.label }} <span v-if="field.required && 'yes' === field.required" class="required">*</span>
            </label>
        </div>

        <div class="wpuf-fields">
            <input
                type="text"
                :class="class_names('textfield')"
                :placeholder="field.placeholder"
                :value="field.default"
                :size="field.size"
            >
            <span v-if="field.help" class="wpuf-help" v-html="field.help" />
        </div>
    </div>

    <div v-if="field.repeat_pass && 'yes' === field.repeat_pass" class="wpuf-password-field">
        <div class="wpuf-label">
            <label :for="'wpuf-' + field.name ? field.name : 'cls'">
                {{ field.re_pass_label }} <span class="required">*</span>
            </label>
        </div>

        <div class="wpuf-fields">
            <input
                type="text"
                :size="field.size"
            >
        </div>
    </div>

    <div v-if="field.pass_strength && 'yes' === field.pass_strength" class="wpuf-password-field">
        <div class="wpuf-label">
        </div>
        <div class="wpuf-fields">
            <div class="password-strength-meter">Strength indicator</div>
        </div>
    </div>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-phone_field">
<div class="wpuf-fields">
    <input
        type="text"
        :class="class_names('phone_field')"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
        style="width: auto;"
    >
    <span v-if="field.help" class="wpuf-help" v-html="field.help"></span>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-ratings">
<div class="wpuf-fields">
    <div class="br-wrapper br-theme-css-stars">
        <div class="br-widget">
            <a v-for="option in field.options" href="#"></a>
        </div>
    </div>

    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-really_simple_captcha">
<div class="wpuf-fields">
    <template v-if="!is_rs_captcha_active">
        <p v-html="no_plugin_msg"></p>
    </template>

    <template v-else>
        <img class="wpuf-rs-captcha-placeholder" src="<?php echo WPUF_PRO_ASSET_URI . '/images/really-simple-captcha-placeholder.png' ?>" alt="">
        <input type="text">
    </template>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-repeat_field">
<div class="wpuf-fields">
    <table v-if="'true' === field.multiple" class="wpuf-repeatable-field">
        <thead>
            <th v-for="column in field.columns">
                {{ column }}
            </th>
            <th>&nbsp;</th>
        </thead>
        <tbody>
            <tr>
                <td v-for="column in field.columns">
                    <input
                        type="text"
                        placeholder=""
                        value=""
                        :size="field.size"
                    >
                </td>

                <td class="wpuf-repeater-buttons">
                    <i class="wpuf-repeater-add">
                        <img src="<?php echo WPUF_ASSET_URI . '/images/icon-plus.png' ?>" alt="">
                    </i>
                    <i class="wpuf-repeater-remove">
                        <img src="<?php echo WPUF_ASSET_URI . '/images/icon-minus.png' ?>" alt="">
                    </i>
                </td>
            </tr>
        </tbody>
    </table>

    <table v-else class="wpuf-repeatable-field">
        <tbody>
            <tr>
                <td>
                    <input
                        type="text"
                        :placeholder="field.placeholder"
                        :value="field.default"
                        :size="field.size"
                    >
                </td>

                <td class="wpuf-repeater-buttons">
                    <i class="wpuf-repeater-add">
                        <img src="<?php echo WPUF_ASSET_URI . '/images/icon-plus.png' ?>" alt="">
                    </i>
                    <i class="wpuf-repeater-remove">
                        <img src="<?php echo WPUF_ASSET_URI . '/images/icon-minus.png' ?>" alt="">
                    </i>
                </td>
            </tr>
        </tbody>
    </table>

    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-shortcode">
<div class="wpuf-fields" v-html="field.shortcode"></div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-step_start">
<div>
    <div class="step-start-indicator">
        <div class="hr-line"></div>
        <span class="step-label">{{ field.label }}</span>
    </div>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-time_field">
<div class="wpuf-fields">
    <select
        :class="class_names('time_field')"
    >
        <option v-if="field.first_option" value="-1">{{ field.first_option }}</option>
    </select>

    <span v-if="field.help" class="wpuf-help" v-html="field.help"></span>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-toc">
<div class="wpuf-toc-container">
    <div class="wpuf-label"></div>

    <div :class="['wpuf-fields clearfix', field.show_checkbox ? 'has-toc-checkbox' : '']">
        <span v-if="field.show_checkbox" class="wpuf-toc-checkbox">
            <input type="checkbox">
        </span>

        <div class="wpuf-toc-description" v-html="content"></div>
    </div>
</div>

</script>

<script type="text/x-template" id="tmpl-wpuf-form-user_bio">
<div class="wpuf-fields">
    <textarea
        v-if="'no' === field.rich"
        :class="class_names('textareafield')"
        :placeholder="field.placeholder"
        :rows="field.rows"
        :cols="field.cols"
    >{{ field.default }}</textarea>

    <text-editor v-if="'no' !== field.rich" :rich="field.rich"></text-editor>

    <span v-if="field.help" class="wpuf-help" v-html="field.help"/>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-user_email">
<div class="wpuf-fields">
    <input
        type="text"
        :class="class_names('textfield')"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
    >
    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-user_login">
<div class="wpuf-fields">
    <input
        type="text"
        :class="class_names('textfield')"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
    >
    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-user_url">
<div class="wpuf-fields">
    <input
        type="text"
        :class="class_names('textfield')"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
    >
    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-submit-button-conditional-logic">
<div class="panel-field-opt panel-field-opt-conditional-logic notification-conditional-logic">

    <ul class="list-inline condition-status">
        <li>
            <label><input type="radio" value="yes" v-model="settings.submit_button_cond.condition_status" name="wpuf_settings[submit_button_cond][condition_status]"> <?php _e( 'Yes', 'wpuf-pro' ); ?></label>
        </li>

        <li>
            <label><input type="radio" value="no" v-model="settings.submit_button_cond.condition_status" name="wpuf_settings[submit_button_cond][condition_status]"> <?php _e( 'No', 'wpuf-pro' ); ?></label>
        </li>
    </ul>

    <div v-if="'yes' === settings.submit_button_cond.condition_status" class="condiotional-logic-container">
        <ul class="condiotional-logic-repeater">
            <li v-for="(condition, index) in settings.submit_button_cond.conditions" class="clearfix">
                <div class="cond-field">
                    <select v-model="condition.name" @change="on_change_cond_field(index, condition.name)" :name="`wpuf_settings[submit_button_cond][conditions][${index}][name]`">
                        <option value=""><?php _e( '- select -', 'wpuf-pro' ); ?></option>
                        <option
                            v-for="dep_field in dependencies"
                            :value="dep_field.name"
                        >{{ dep_field.label }}</option>
                    </select>
                </div>

                <div class="cond-operator">
                    <select v-model="condition.operator" :name="`wpuf_settings[submit_button_cond][conditions][${index}][operator]`">
                        <option value="="><?php _e( 'is', 'wpuf-pro' ); ?></option>
                        <option value="!="><?php _e( 'is not', 'wpuf-pro' ); ?></option>
                    </select>
                </div>

                <div class="cond-option">
                    <select v-model="condition.option" :name="`wpuf_settings[submit_button_cond][conditions][${index}][option]`">
                        <option value=""><?php _e( '- select -', 'wpuf-pro' ); ?></option>
                        <option
                            v-for="cond_option in get_cond_options(condition.name)"
                            :value="cond_option.opt_name"
                        >
                            {{ cond_option.opt_title }}
                        </option>
                    </select>
                </div>

                <div class="cond-action-btns">
                    <i class="fa fa-plus-circle" @click="add_condition"></i>
                    <i class="fa fa-minus-circle pull-right" @click="delete_condition(index)"></i>
                </div>
            </li>
        </ul>

        <p class="help">
            <?php
                printf(
                    __( 'Show submit button when %s of these rules are met', 'wpuf-pro' ),
                    '<select v-model="settings.submit_button_cond.cond_logic" name="wpuf_settings[submit_button_cond][cond_logic]"><option value="any">' . __( 'any', 'wpuf-pro' ) . '</option><option value="all">' . __( 'all', 'wpuf-pro' ) . '</option></select>'
                );
            ?>
        </p>
    </div>
    <div v-else>
        <input type="hidden" name="wpuf_settings[submit_button_cond][conditions][0][name]" value="">
        <input type="hidden" name="wpuf_settings[submit_button_cond][conditions][0][operator]" value="=">
        <input type="hidden" name="wpuf_settings[submit_button_cond][conditions][0][option]" value="">
        <input type="hidden" name="wpuf_settings[submit_button_cond][cond_logic]" valye="any">
    </div>
</div></script>
