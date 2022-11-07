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
