<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_PS_PT_Admin class
 */
class GMW_PS_BPMDG_Admin_Settings {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// admin settings
		add_filter( 'gmw_members_directory_geolocation_admin_settings', array( $this, 'settings' ), 10 );
		add_action( 'gmw_main_settings_info_window_template', array( $this, 'info_window_template' ), 10, 2 );
	}
	
	/**
	 * Extend admin settings
	 *
	 * @access public
	 * 
	 * @return $settings
	 */
	public function settings( $settings ) {
		
		$settings['markers_grouping'] = array(
			'name'        	=> 'markers_grouping',
			'type'  	  	=> 'select',
			'default'       => 'standard',
			'label' 	  	=> __( 'Markers grouping', 'gmw-premium-settings'),
			'desc'        	=> __( 'Use marker Clusterer to group near locations.', 'gmw-premium-settings' ),
			'options'  	 	=> array(
				'standard'		     => 'No Grouping',
				'markers_clusterer'  => 'Markers clusterer',
				'markers_spiderfier' => 'Markers Spiderfier'
			),
			'attributes'  	=> array(),
			'priority'	  	=> 125
		);

		$settings['iw_type'] = array(
			'name'       	=> 'iw_type',
			'type'  	  	=> 'select',
			'default'       => 'infobox',
			'label' 	  	=> __( 'Info-window Type', 'gmw-premium-settings'),
			'desc'        	=> __( 'Select the info-window type which you would like to use.', 'gmw-premium-settings' ),
			'options'	  	 => array(
				'standard' 	 => 'Standard',
				'infobubble' => 'Info-Bubble',
				'infobox' 	 => 'Info-Box',
				'popup'	  	 => 'Popup Window'
			),
			'attributes'  	=> array(),
			'priority'	  	=> 130
		);
		
		$settings['ajax_enabled'] = array(
			'name'       	=> 'ajax_enabled',
			'type'  	  	=> 'checkbox',
			'default'       => '',
			'label' 	  	=> __( 'Ajax Powered Content', 'gmw-premium-settings'),
			'cb_label'    	=> __( 'Enable', 'gmw-premium-settings'),
			'desc'        	=> __( 'Load the info-window content via Ajax. This feature uses PHP template files that can be easily modified based on your need.', 'gmw-premium-settings' ),
			'attributes'  	=> array(),
			'priority'	  	=> 140
		);

		$settings['template'] = array(
			'name'       	=> 'template',
			'type'       	=> 'function',
			'default'       => 'default',
			'function'		=> 'info_window_template',
			'label'      	=> __( 'Template File', 'gmw-premium-settings' ),
			'desc'       	=> __( 'Select the template file which you would like to use. Template files can be only be used when the AJAX content option is enabled.', 'gmw-premium-settings'),
			'attributes'  	=> array(),
			'priority'	  	=> 150
		);

		return $settings;
	}

	/**
	 * results template form settings posts
	 *
	 */
	public static function info_window_template( $value, $name_attr ) {

		echo '<div id="info-window-templates-wrapper" style="display: none;">';
	
		$iw_types = array( 
			'standard'   => 'Standard', 
			'infobubble' => 'Info Bubble', 
			'infobox' 	 => 'Info Box',  
			'popup'   	 => 'Popup Window' 
		);

		foreach ( $iw_types as $iw_name => $iw_title ) {
			
			// get templates
			$templates = gmw_get_info_window_templates( 'members_locator', $iw_name, 'premium_settings' );
			?>
			<div class="gmw-info-window-template <?php echo esc_attr( $iw_name ); ?>" style="display:none;">
				<select name="<?php echo esc_attr( $name_attr.'['.$iw_name.']' ); ?>">			
					
					<?php foreach ( $templates as $template_value => $template_name ) { ?>
						
						<?php $selected = ( isset( $value[$iw_name] ) && $value[$iw_name] == $template_value ) ? 'selected="selected"' : ''; ?>
					
						<option value="<?php echo esc_attr( $template_value ); ?>" <?php echo $selected; ?>>
							<?php echo esc_html( $template_name ); ?>	
						</option>
					<?php } ?>
				</select>
			</div>
			<?php
		}
		?>
		</div>
		<p id="info-window-no-templates-message" style="display: none;">
			<?php _e( 'Template files availabe when using AJAX only.', 'gmw-premium-settings' ); ?>
		</p>
		<?php
	}
}
new GMW_PS_BPMDG_Admin_Settings();
?>