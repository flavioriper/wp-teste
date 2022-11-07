<div class="wpuf-qr-code wpuf-fields">
	<select>
		<option><?php _e( '- Select -', 'wpuf-pro' ); ?></option>
		<option v-for="type in field.qr_type">{{ type }}</option>
	</select>
    <span v-if="field.help" class="wpuf-help" v-html="field.help"> </span>
</div>
