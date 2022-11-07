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
