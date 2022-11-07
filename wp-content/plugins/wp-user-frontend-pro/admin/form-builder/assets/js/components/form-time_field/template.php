<div class="wpuf-fields">
    <select
        :class="class_names('time_field')"
    >
        <option v-if="field.first_option" value="-1">{{ field.first_option }}</option>
    </select>

    <span v-if="field.help" class="wpuf-help" v-html="field.help"></span>
</div>
