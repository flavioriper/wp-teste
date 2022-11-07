<?php
/**
 * GMW PS Admin Settings.
 *
 * @author Eyal Fitoussi
 *
 * @since 1.0
 *
 * @package gmw-multiple-locations
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_PS_PT_Admin class
 */
class GMW_PS_PT_Admin_Settings {

	/**
	 * GMW icons
	 *
	 * @var [type]
	 */
	public $icons;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// admin settings.
		add_filter( 'gmw_post_types_settings_admin_settings', array( $this, 'settings' ), 10 );
		add_action( 'gmw_main_settings_post_types_icons', array( $this, 'post_types_icons' ), 5, 2 );
		add_action( 'gmw_main_settings_per_category_icons', array( $this, 'per_category_icons' ), 5, 3 );
		add_filter( 'gmw_edit_post_location_form_args', array( $this, 'location_form_settings' ), 50 );
	}

	/**
	 * Extend admin settings
	 *
	 * @param array $settings settings array.
	 *
	 * @access public
	 *
	 * @return $settings
	 */
	public function settings( $settings ) {

		$settings['location_form_options'] = array(
			'name'       => 'location_form_options',
			'type'       => 'fields_group',
			'label'      => __( 'Location Form', 'gmw-premium-settings' ),
			'desc'       => __( 'Setup the Location form of the admin\'s "Edit post" page.', 'gmw-premium-settings' ),
			'fields'     => array(
				'location_form_exclude_fields_groups' => array(
					'name'       => 'location_form_exclude_fields_groups',
					'type'       => 'multiselect',
					'default'    => array(),
					'label'      => __( 'Exclude Fields Group', 'gmw-premium-settings' ),
					'desc'       => __( 'Select the fields groups that you would like to exclude from the location tab', 'gmw-premium-settings' ),
					'options'    => array(
						'location'    => __( 'Location', 'gmw-premium-settings' ),
						'address'     => __( 'Address', 'gmw-premium-settings' ),
						'coordinates' => __( 'Coordinates', 'gmw-premium-settings' ),
						'contact'     => __( 'Contact Info', 'gmw-premium-settings' ),
						'days_hours'  => __( 'Days & Hours', 'gmw-premium-settings' ),
					),
					'attributes' => array(),
					'priority'   => 5,
				),
				'location_form_exclude_fields'        => array(
					'name'       => 'location_form_exclude_fields',
					'type'       => 'multiselect',
					'default'    => array(),
					'label'      => __( 'Exclude form fields', 'gmw-premium-settings' ),
					'desc'       => __( 'Select specific fields that you would like to exclude from the location form', 'gmw-premium-settings' ),
					'options'    => array(
						'address'      => __( 'Address ( with autocomplete )', 'gmw-premium-settings' ),
						'map'          => __( 'Map', 'gmw-premium-settings' ),
						'street'       => __( 'Street', 'gmw-premium-settings' ),
						'premise'      => __( 'Apt/Suit ', 'gmw-premium-settings' ),
						'city'         => __( 'City', 'gmw-premium-settings' ),
						'region_name'  => __( 'State', 'gmw-premium-settings' ),
						'postcode'     => __( 'Postcode', 'gmw-premium-settings' ),
						'country_code' => __( 'Country', 'gmw-premium-settings' ),
					),
					'attributes' => array(),
					'priority'   => 10,
				),
				'location_form_template'              => array(
					'name'       => 'location_form_template',
					'type'       => 'select',
					'default'    => 'location-form-tabs-left',
					'label'      => __( 'Template', 'gmw-premium-settings' ),
					'desc'       => __( 'Select the Location form template', 'gmw-premium-settings' ),
					'options'    => array(
						'location-form-tabs-left' => __( 'Tabs Left', 'gmw-premium-settings' ),
						'location-form-tabs-top'  => __( 'Tabs Top ', 'gmw-premium-settings' ),
						'location-form-no-tabs'   => __( 'No Tabs', 'gmw-premium-settings' ),
					),
					'attributes' => array(),
					'priority'   => 15,
				),
			),
			'attributes' => '',
			'optionsbox' => 1,
			'priority'   => 10,
		);

		$settings['post_types_icons'] = array(
			'name'       => 'post_types_icons',
			'type'       => 'function',
			'default'    => '',
			'label'      => __( 'Post types map icons', 'gmw-premium-settings' ),
			'desc'       => __( 'Assign map icon to each post type.', 'gmw-premium-settings' ),
			'attributes' => array(),
			'priority'   => 40,
		);

		$settings['post_types_settings'] = array(
			'name'       => 'per_post_icons',
			'type'       => 'checkbox',
			'default'    => '',
			'label'      => __( 'Per post map icon', 'gmw-premium-settings' ),
			'cb_label'   => __( 'Enable', 'gmw-premium-settings' ),
			'desc'       => __( 'Add map icons tab to the location form to be able to select map icon per post.', 'gmw-premium-settings' ),
			'attributes' => array(),
			'priority'   => 45,
		);
		$settings['per_category_icons']  = array(
			'name'       => 'per_category_icons',
			'type'       => 'function',
			'default'    => '',
			'label'      => __( 'Category icons', 'gmw-premium-settings' ),
			'desc'       => __( 'This feature does 2 things: <ol><li>Assign categoty icons to taxonomies - allows you to display the category icon next to eacho category when using as checkboxes in the search form.</li><li>Assign category icons as map markers.</li></ol>After enabling this feature you will be able to select icons in the add/edit taxonomy pages.', 'gmw-premium-settings' ),
			'attributes' => array(),
			'priority'   => 50,
		);

		return $settings;
	}

	/**
	 * Manage location form
	 *
	 * @param  [type] $args [description].
	 *
	 * @return [type]       [description]
	 */
	public function location_form_settings( $args ) {
		$args['exclude_fields_groups'] = gmw_get_option( 'post_types_settings', 'location_form_exclude_fields_groups', array() );
		$args['exclude_fields']        = gmw_get_option( 'post_types_settings', 'location_form_exclude_fields', array() );
		$args['form_template']         = gmw_get_option( 'post_types_settings', 'location_form_template', 'location-form-tabs-left' );

		return $args;
	}

	/**
	 * Per post type map icon.
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 *
	 * @return [type]            [description]
	 */
	public function post_types_icons( $value, $name_attr ) {

		// get the post types used with GEO my WP.
		$post_types = gmw_get_option( 'post_types_settings', 'post_types', array() );

		// abort if no post types were chosen.
		if ( empty( $post_types ) ) {

			echo '<em>' . esc_attr_e( 'You need to select at least 1 post type above in order to use this feature.', 'gmw-premium-settings' ) . '</em>';

			return;
		}
		?>
		<div id="post-types-map-icons-wrapper" style="width:100%;max-width: 500px;">
			<?php

			$icons = gmw_get_icons();

			foreach ( $post_types as $post_type ) {

				$pt_object = get_post_type_object( $post_type );

				if ( empty( $pt_object ) ) {
					continue;
				}
				?>
				<div class="post-type-map-icons-wrapper">
					<div class="header">
						<i class="gmw-icon-params"></i>
						<span>
							<?php echo esc_html( $pt_object->labels->name ); ?>		
						</span>
					</div>

					<div class="content">
						<?php
						$map_icons = $icons['pt_map_icons']['all_icons'];
						$icons_url = $icons['pt_map_icons']['url'];
						$cic       = 1;

						foreach ( $map_icons as $map_icon ) {

							$checked = ( ( isset( $value[ $post_type ] ) && $value[ $post_type ] === $map_icon ) || 1 === absint( $cic ) ) ? 'checked="checked"' : '';

							echo '<label><input type="radio" name="' . esc_attr( $name_attr ) . '[' . esc_attr( $post_type ) . ']" value="' . esc_attr( $map_icon ) . '" ' . $checked . ' />'; // WPCS: XSS ok.
							echo '<img src="' . esc_url( $icons_url . $map_icon ) . '" />';
							echo '</label>';
							$cic++;
						}
						?>
					</div>
				</div>
			<?php } ?>	
		</div>

		<div class="gmw-description-box" style="width:100%;max-width: 500px;margin:0;">
			<p><?php esc_attr_e( '* Make sure to click "Save Changes" after modfing the Post Types settings above to refresh the list of taxonomies icons below.', 'gmw-premium-settings' ); ?></p>
			<?php gmw_refresh_map_icons_button( 'admin.php?page=gmw-settings' ); ?>
		</div>

		<script>
			jQuery( document ).ready( function($) {
				jQuery( '.post-type-map-icons-wrapper' ).find( '.header' ).click( function(){
					jQuery( this ).closest( '.post-type-map-icons-wrapper' ).find( 'div.content' ).slideToggle();
				});
			});
		</script>	
		<?php
	}

	/**
	 * Per category Icon main settings page.
	 *
	 * @param  [type] $value       [description].
	 *
	 * @param  [type] $name_attr   [description].
	 *
	 * @param  [type] $gmw_options [description].
	 *
	 * @return [type]              [description]
	 */
	public function per_category_icons( $value, $name_attr, $gmw_options ) {

		$checked   = '';
		$display   = 'style="display:none;"';
		$name_attr = esc_attr( $name_attr );

		if ( isset( $value['enabled'] ) ) {
			$checked = 'checked="checked"';
			$display = '';
		}
		?>
		<p>
		<label>
			<input 
				class="setting-per_category_icons" 
				name="<?php echo $name_attr . '[enabled]'; // wpcs: XSS ok. ?>"
				onchange="jQuery('.per-category-icons-trigger').slideToggle();" 
				type="checkbox" value="1" 
				<?php echo $checked; // wpcs: XSS ok. ?>
			/>
			<?php esc_attr_e( 'Enable category icons', 'gmw-premium-settings' ); ?>
		</label>
		</p>

		<?php $checked = ! empty( $value['same_icons'] ) ? 'checked="checked"' : ''; ?>

		<div class="gmw-options-box per-category-icons-trigger" <?php echo $display; // wpcs: XSS ok. ?>>

			<input 
				style="display: none"
				class="setting-per_category_icons_same_icons" 
				name="<?php echo $name_attr . '[same_icons]'; // wpcs: XSS ok. ?>" 
				type="checkbox" 
				value="1" 
				checked="checked"
			/>

			<div class="single-option checkbox">
				<?php
				if ( empty( $gmw_options['post_types_settings']['post_types'] ) ) {
					return;
				}
				?>
				<label><b><?php esc_attr_e( 'Taxonomies', 'gmw-premium-settings' ); ?></b></label>

				<div class="option-content">
					<?php
					foreach ( get_object_taxonomies( $gmw_options['post_types_settings']['post_types'] ) as $taxonomy ) {
						$checked = ( ! empty( $value['taxonomies'] ) && in_array( $taxonomy, $value['taxonomies'] ) ) ? 'checked="checked"' : '';
						?>
						<label>
							<input 
								class="setting-per_category_icons_taxonomy" 
								name="<?php echo $name_attr . '[taxonomies][]'; // wpcs: XSS ok. ?>" 
								type="checkbox" value="<?php esc_attr_e( $taxonomy ); ?>"
								<?php echo $checked; // wpcs: XSS ok. ?>
							/> 
							<?php esc_attr_e( $taxonomy ); ?>
						</label>
						<?php
					}
					?>
					<p class="description">
						<?php esc_attr_e( 'Check the taxnomies to enable map icons.', 'gmw-premium-settings' ); ?>

						<span style="font-size:12px;display:block;line-height: 18px;color:red;">
							<?php esc_attr_e( '* Make sure to click "Save Changes" after modfing the Post Types settings above to refresh the list of taxonomies icons below.', 'gmw-premium-settings' ); ?>
						</span>
					</p>
				</div>
			</div>

			<div class="single-option">

				<label><b><?php esc_attr_e( 'Taxonomies term orderby', 'gmw-premium-settings' ); ?></b></label>

				<div class="option-content">
					<select name="<?php echo $name_attr . '[terms_orderby]'; // wpcs: XSS ok. ?>">

						<?php $orderby_value = ( ! empty( $value['terms_orderby'] ) ) ? $value['terms_orderby'] : ''; ?>

						<option value="term_id" selected="selected"><?php esc_attr_e( 'Term ID', 'gmw-premium-settings' ); ?></option>
						<option value="parent" <?php selected( $orderby_value, 'parent' ); ?>><?php esc_attr_e( 'Parent', 'gmw-premium-settings' ); ?></option>
						<option value="name" <?php selected( $orderby_value, 'name' ); ?>><?php esc_attr_e( 'Name', 'gmw-premium-settings' ); ?></option>
						<option value="count" <?php selected( $orderby_value, 'count' ); ?>><?php esc_attr_e( 'Count', 'gmw-premium-settings' ); ?></option>
						<option value="slug" <?php selected( $orderby_value, 'slug' ); ?>><?php esc_attr_e( 'Slug', 'gmw-premium-settings' ); ?></option>
						<option value="term_group" <?php selected( $orderby_value, 'term_group' ); ?>><?php esc_attr_e( 'Term group', 'gmw-premium-settings' ); ?></option>
						<option value="taxonomy" <?php selected( $orderby_value, 'taxonomy' ); ?>><?php esc_attr_e( 'Taxonomy', 'gmw-premium-settings' ); ?></option>
						<option value="term_taxonomy_id" <?php selected( $orderby_value, 'term_taxonomy_id' ); ?>><?php esc_attr_e( 'Term taxonomy id', 'gmw-premium-settings' ); ?></option>
						<option value="term_order" <?php selected( $orderby_value, 'term_order' ); ?>><?php esc_attr_e( 'Term order', 'gmw-premium-settings' ); ?></option>
					</select>
					<p class="description">
						<?php esc_attr_e( 'When a post have multiple cateogies, the orderby will determine which category icon will be used on the map based on the terms order.', 'gmw-premium-settings' ); ?>	
					</p>
				</div>
			</div>

			<div class="single-option">
				<label><b><?php esc_attr_e( 'Taxonomies term order', 'gmw-premium-settings' ); ?></b></label>
				<div class="option-content">
					<select name="<?php echo $name_attr . '[terms_order]'; // wpcs: XSS ok. ?>">
						<?php $order_value = ! empty( $value['terms_order'] ) ? $value['terms_order'] : ''; ?>
						<option value="DESC" selected="selected"><?php esc_attr_e( 'DESC', 'gmw-premium-settings' ); ?></option>
						<option value="ASC"  <?php selected( $order_value, 'ASC' ); ?>><?php esc_attr_e( 'ASC', 'gmw-premium-settings' ); ?></option>
					</select>
				</div>
			</div>

			<div class="single-option">

				<label><b><?php esc_attr_e( 'Default icon', 'gmw-premium-settings' ); ?></b></label>

				<div class="option-content form-field term-category-icons-wrap">

					<div class="category-icons icons" style="display: inline-block">
						<?php

						$icons          = gmw_get_icons();
						$category_icons = $icons['pt_category_icons']['all_icons'];

						$cic = 1;

						foreach ( $category_icons as $category_icon ) {

							$checked = '';

							// look for checked icon only if in edit tag page.
							if ( 1 === absint( $cic ) || ( ! empty( $value['default_icon'] ) && $value['default_icon'] === $category_icon ) ) {
								$checked = 'checked="checked"';
							}

							echo '<label style="width:initial !important">';
							echo '<input type="radio" name="' . $name_attr . '[default_icon]" value="' . esc_attr( $category_icon ) . '" ' . $checked . '/>'; // wpcs: XSS ok.
							echo '<img src="' . esc_url( $icons['pt_category_icons']['url'] . $category_icon ) . '"/>';
							echo '</label>';
							$cic++;
						}
						?>
					</div>
					<p class="description"><?php esc_attr_e( 'Select the icon that will be used when no icon found for specific category or post.', 'gmw-premium-settings' ); ?>
					</p>
			</div>

			<div class="option-content form-field term-category-icons-refresh">
				<div class="gmw-description-box"><?php gmw_refresh_map_icons_button( 'admin.php?page=gmw-settings' ); ?></div>
			</div>
		</div>
		<?php
	}
}
new GMW_PS_PT_Admin_Settings();
?>
