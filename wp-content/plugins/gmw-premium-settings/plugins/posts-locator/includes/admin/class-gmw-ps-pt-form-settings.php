<?php
/**
 * GMW Premium Settings - Form Editor Settings.
 *
 * @package gmw-premium-settings.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_PT_Admin class
 */
class GMW_PS_PT_Form_Settings {

	/**
	 * GMW Options
	 *
	 * @var [type]
	 */
	public $options;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		$this->options = GMW()->options;

		add_filter( 'gmw_form_default_settings', array( $this, 'default_settings' ), 15, 2 );

		// posts locator form fields.
		add_filter( 'gmw_form_settings', array( $this, 'form_settings' ), 15, 2 );

		// custom settings.
		add_action( 'gmw_form_settings_ps_pt_post_types_settings', array( $this, 'post_types_settings' ), 15, 4 );
		add_action( 'gmw_form_settings_ps_pt_taxonomies', array( $this, 'taxonomies' ), 15, 3 );
		add_action( 'gmw_form_settings_ps_pt_custom_fields', array( $this, 'custom_fields' ), 15, 4 );
		add_action( 'gmw_form_settings_ps_pt_include_exclude_terms', array( $this, 'include_exclude_terms' ), 15, 4 );
		add_action( 'gmw_form_settings_ps_pt_post_excerpt', array( 'GMW_Form_Settings_Helper', 'excerpt' ), 15, 2 );

		// validations.
		add_filter( 'gmw_validate_form_settings_ps_pt_post_types_settings', array( $this, 'validate_post_types_settings' ), 15 );
		add_filter( 'gmw_validate_form_settings_ps_pt_include_exclude_terms', array( $this, 'validate_include_exclude_terms' ), 15, 2 );
	}

	/**
	 * Default settings
	 *
	 * @param  array $settings settings.
	 *
	 * @param  array $form     form being edited.
	 *
	 * @return [type]           [description]
	 */
	public function default_settings( $settings, $form ) {

		if ( 'posts_locator' === $form['component'] ) {

			$settings['search_form']['post_types_settings']['usage']            = 'dropdown';
			$settings['search_form']['post_types_settings']['show_options_all'] = 'Search site';
			$settings['search_results']['orderby']                              = 'distance:Distance,post_title:Name,post_modified:Last Updated';
		}

		return $settings;
	}

	/**
	 * Form Settings for all forms types
	 *
	 * @param  array $form_fields form fields.
	 *
	 * @param  array $form     form being edited.
	 *
	 * @return [type]           [description]
	 */
	public function form_settings( $form_fields, $form ) {

		if ( 'posts_locator' !== $form['component'] ) {
			return $form_fields;
		}

		// no need for global map.
		if ( 'global_maps' !== $form['addon'] ) {

			$form_fields['page_load_results']['include_exclude_terms'] = array(
				'name'       => 'include_exclude_terms',
				'type'       => 'function',
				'function'   => 'ps_pt_include_exclude_terms',
				'default'    => '',
				'label'      => __( 'Include/Exclude Terms', 'gmw-premium-settings' ),
				'desc'       => __( 'Filter locations based on taxonomy by including/excluding taxonomy terms.', 'gmw-premium-settings' ),
				'attributes' => array(),
				'priority'   => 15,
			);

			if ( 'ajax_forms' !== $form['addon'] ) {

				$form_fields['page_load_results']['orderby'] = array(
					'name'       => 'orderby',
					'type'       => 'select',
					'default'    => 'distance',
					'label'      => __( 'Orderby', 'gmw-premium-settings' ),
					'desc'       => __( 'Select the order of the results.', 'gmw-premium-settings' ),
					'options'    => array(
						'distance'      => __( 'Distance', 'gmw-premium-settings' ),
						'ID'            => __( 'Post id', 'gmw-premium-settings' ),
						'post_title'    => __( 'Post title', 'gmw-premium-settings' ),
						'post_date'     => __( 'Date created', 'gmw-premium-settings' ),
						'post_modified' => __( 'Last modified', 'gmw-premium-settings' ),
						'post_type'     => __( 'Post type', 'gmw-premium-settings' ),
					),
					'attributes' => array(),
					'priority'   => 95,
				);

				$form_fields['form_submission']['orderby'] = array(
					'name'       => 'orderby',
					'type'       => 'select',
					'default'    => 'distance',
					'label'      => __( 'Orderby', 'gmw-premium-settings' ),
					'desc'       => __( 'Select the default order of the results on form submission.', 'gmw-premium-settings' ),
					'options'    => array(
						'distance'      => __( 'Distance', 'gmw-premium-settings' ),
						'ID'            => __( 'Post id', 'gmw-premium-settings' ),
						'post_title'    => __( 'Post title', 'gmw-premium-settings' ),
						'post_date'     => __( 'Date created', 'gmw-premium-settings' ),
						'post_modified' => __( 'Last modified', 'gmw-premium-settings' ),
						'post_type'     => __( 'Post type', 'gmw-premium-settings' ),
					),
					'attributes' => array(),
					'priority'   => 25,
				);

				$form_fields['search_results']['orderby'] = array(
					'name'        => 'orderby',
					'type'        => 'text',
					'default'     => '',
					'placeholder' => 'ex. distance:Distance,post_title:Name,post_modified:Last Updated',
					'label'       => __( 'Orderby', 'gmw-premium-settings' ),
					'desc'        => __( '<p>Generate an orderby select dropdown menu to display in the search results ( leave blank to omit ).</p><p> - Enter sets of value:label, comma separated and in the order that you would like them to appear in the dropdown menu. For ex. post_title:Name,distance:Distance.</p><p>The availabe orderby values are distance, post_title, ID ( post ID ), post_date ( date created ), post_modified ( last modifed ) and post_type.</p>', 'gmw-premium-settings' ),
					'priority'    => 18,
				);
			}

			$form_fields['info_window']['excerpt'] = array(
				'name'       => 'excerpt',
				'type'       => 'function',
				'function'   => 'ps_pt_post_excerpt',
				'label'      => __( 'Excerpt', 'gmw-premium-settings' ),
				'default'    => '',
				'desc'       => __( 'Display the post\'s excerpt.<p style="color:red;font-size:11px;"> * Note - this feature require AJAX content to be enabled.</p>', 'gmw-premium-settings' ),
				'attributes' => array(),
				'priority'   => 95,
			);

			$form_fields['search_form']['taxonomies'] = array(
				'name'       => 'taxonomies',
				'type'       => 'function',
				'default'    => '',
				'function'   => 'ps_pt_taxonomies',
				'label'      => __( 'Taxonomies', 'gmw-premium-settings' ),
				'desc'       => __( 'Setup the taxonomies as filters in the search form.', 'gmw-premium-settings' ),
				'attributes' => array(),
				'priority'   => 14,
			);
		}

		$form_fields['search_form']['post_types'] = array(
			'name'       => 'post_types',
			'type'       => 'fields_group',
			'label'      => __( 'Post Types', 'gmw-premium-settings' ),
			'desc'       => __( 'Select a single post type to set as the default, or select multiple post types to display as a dropdown select box in the search form.', 'gmw-premium-settings' ),
			'fields'     => array(
				array(
					'name'        => 'post_types',
					'type'        => 'multiselect',
					'default'     => array( 'post' ),
					// 'label'         => __( 'Post Types', 'gmw-premium-settings' ),
					'placeholder' => __( 'Select post types', 'gmw-premium-settings' ),
					'options'     => GMW_Form_Settings_Helper::get_post_types(),
					'attributes'  => '',
					'priority'    => 5,
				),
				array(
					'name'       => 'post_types_settings',
					'type'       => 'function',
					'default'    => '',
					'function'   => 'ps_pt_post_types_settings',
					'label'      => '',
					'desc'       => '',
					'attributes' => array(),
					'priority'   => 10,
				),
			),
			'attributes' => '',
			'priority'   => 12,
		);

		$form_fields['search_form']['custom_fields'] = array(
			'name'       => 'custom_fields',
			'type'       => 'function',
			'default'    => '',
			'function'   => 'ps_pt_custom_fields',
			'label'      => __( 'Custom fields', 'gmw-premium-settings' ),
			'desc'       => __( '<p>Using this feature you can add custom fields filters to the search form. At the moment, the field can only be used as textboxes.</p><p> - Click the cog icon to manage the custom fields.</p><p> - Select a field from the dropdown menu, then click "add field".</p><p> - Select the field type and the comparison that you would like to use.</p><p> - Enter field lable or leave blank to ommit the label.</p><p> - Enter a placeholder or leave blank to ommit it.</p>', 'gmw-premium-settings' ),
			'attributes' => array(),
			'priority'   => 16,
		);

		// generate map icon dropdown options.
		$map_icons_options = array(
			'global' => __( 'Global', 'gmw-premium-settings' ),
		);

		if ( ! empty( $this->options['post_types_settings']['per_post_icons'] ) ) {
			$map_icons_options['per_post'] = __( 'Per Post', 'gmw-premium-settings' );
		}

		$map_icons_options['per_post_type'] = __( 'Per post type', 'gmw-premium-settings' );

		if ( ! empty( $this->options['post_types_settings']['per_category_icons']['enabled'] ) ) {
			$map_icons_options['per_category'] = __( 'Per Category', 'gmw-premium-settings' );
		}

		$map_icons_options['image'] = __( 'Featured image', 'gmw-premium-settings' );

		$form_fields['map_markers']['usage'] = array(
			'name'       => 'usage',
			'type'       => 'select',
			'default'    => '',
			'label'      => __( 'Map icon usage', 'gmw-premium-settings' ),
			'desc'       => __( 'How would you like to display the map icon?', 'gmw-premium-settings' ),
			'options'    => $map_icons_options,
			'attributes' => array(),
			'priority'   => 13,
		);

		return $form_fields;
	}

	/**
	 * Post types in form settings.
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 *
	 * @param  [type] $form      [description].
	 *
	 * @return [type]            [description]
	 */
	public static function post_types_settings( $value, $name_attr, $form ) {

		if ( empty( $value ) ) {
			$value = array(
				'usage'            => 'dropdown',
				'label'            => '',
				'show_options_all' => '',
			);
		}
		?>
		<div class="posts-types-settings-wrapper gmw-options-box" style="display:none;margin-top: 20px;">	
			<div class="post-type-setting single-option usage">	
				<label><?php esc_attr_e( 'Usage', 'gmw-premium-settings' ); ?></label>	
				<?php
				if ( ! isset( $value['usage'] ) ) {
					$value['usage'] = 'dropdown';
				}
				?>
				<div class="option-content">
					<select name="<?php echo esc_attr( $name_attr ); ?>[usage]" style="width:100%">

						<option value="pre_defined" selected="selected">
							<?php esc_attr_e( 'Pre-defined', 'gmw-premium-settings' ); ?>
						</option>

						<option value="dropdown" <?php if ( 'dropdown' === $value['usage'] ) echo 'selected="selected"'; ?>>
							<?php esc_attr_e( 'Dropdown', 'gmw-premium-settings' ); ?>		
						</option>

						<option value="checkboxes" <?php if ( 'checkboxes' === $value['usage'] ) echo 'selected="selected"'; ?>>
							<?php esc_attr_e( 'Checkboxes', 'gmw-premium-settings' ); ?>		
						</option>

						<option value="smartbox" <?php if ( 'smartbox' === $value['usage'] ) echo 'selected="selected"'; ?>>
							<?php esc_attr_e( 'Smartbox', 'gmw-premium-settings' ); ?>
						</option>
						<option value="smartbox_multiple" <?php if ( 'smartbox_multiple' === $value['usage'] ) echo 'selected="selected"'; ?>>
							<?php esc_attr_e( 'Smartbox ( multiple selections )', 'gmw-premium-settings' ); ?>
						</option>
					</select>
				</div>
			</div>

			<div class="single-option label half">

				<label><?php esc_attr_e( 'Label', 'gmw-premium-settings' ); ?></label>	

				<div class="option-content">
					<input 
						type="text" 
						placeholder="<?php esc_attr_e( 'Field label', 'gmw-premium-settings' ); ?>" 
						name="<?php echo esc_attr( $name_attr ); ?>[label]" 
						value="<?php echo ! empty( $value['label'] ) ? esc_attr( stripcslashes( $value['label'] ) ) : ''; ?>" 
					/>
				</div>
			</div>

			<div class="single-option options-all half">

				<label><?php esc_attr_e( 'Options all label', 'gmw-premium-settings' ); ?></label>	

				<div class="option-content">
					<input 
						type="text" 
						placeholder="<?php esc_attr_e( 'Options all label', 'gmw-premium-settings' ); ?>" 
						name="<?php echo esc_attr( $name_attr ); ?>[show_options_all]" 
						value="<?php echo ! empty( $value['show_options_all'] ) ? esc_attr( stripcslashes( $value['show_options_all'] ) ) : ''; ?>" 
					/>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Validate bp groups
	 *
	 * @param  array $output input values before validation.
	 *
	 * @return array validated input
	 */
	public static function validate_post_types_settings( $output ) {

		$options = array(
			'pre_defined',
			'dropdown',
			'checkboxes',
			'smartbox',
			'smartbox_multiple',
		);

		if ( ! isset( $output['usage'] ) || ! in_array( $output['usage'], $options, true ) ) {
			$output['usage'] = 'dropdown';
		}

		$output['label']            = sanitize_text_field( $output['label'] );
		$output['show_options_all'] = sanitize_text_field( $output['show_options_all'] );

		return $output;
	}

	/**
	 * Form Builder taxonomies.
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 *
	 * @param  [type] $form      [description].
	 */
	public static function taxonomies( $value, $name_attr, $form ) {

		if ( empty( $value ) ) {
			$value = array();
		}

		$posts = get_post_types();
		?>
		<div id="taxonomies-wrapper">

			<?php foreach ( $posts as $post ) : ?>

				<?php $post_taxonomies = get_object_taxonomies( $post ); ?>	

				<?php if ( ! empty( $post_taxonomies ) ) { ?>

					<?php $style = ( isset( $form['search_form']['post_types'] ) && ( count( $form['search_form']['post_types'] ) == 1 ) && ( in_array( $post, $form['search_form']['post_types'], true ) ) ) ? '' : 'style="display:none"'; ?>

					<div id="post-type-<?php echo esc_attr( $post ); ?>-taxonomies-wrapper" class="post-type-taxonomies-wrapper" <?php echo $style; ?>>
						<?php
						// reorder taxonomies based on order saved.
						$taxonomies_order = isset( $value[$post] ) ? array_keys( $value[ $post ] ) : array();
						$new_order        = array();

						foreach ( $taxonomies_order as $val ) {
							$new_order[ array_search( $val, $post_taxonomies ) ] = $val;
						}

						$post_taxonomies = array_unique( array_merge( $new_order, $post_taxonomies ) );
						?>
						<?php
						foreach ( $post_taxonomies as $key => $taxonomy_name ) :

							if ( empty( $value[ $post ][ $taxonomy_name ] ) ) {
								$value[ $post ][ $taxonomy_name ]['style'] = 'na';
							}

							$tax_option = $value[ $post ][ $taxonomy_name ];
							$taxonomy   = get_taxonomy( $taxonomy_name );

							if ( empty( $taxonomy ) || ! is_object( $taxonomy ) ) {
								continue;
							}
							?>
							<div id="<?php echo esc_attr( $taxonomy_name ); ?>_cat" class="taxonomy-wrapper <?php echo esc_attr( $post ); ?>_cat " >
								<div class="taxonomy-header">
									<i href="#" class="gmw-taxonomy-sort-handle gmw-icon-sort" title="Sort taxonomy"></i>
									<i class="gmw-icon-cog"></i>
									<span><?php echo esc_html( $taxonomy->labels->singular_name ) ; ?></span>
								</div>

								<?php $style = ! empty( $tax_option['style'] ) ? $tax_option['style'] : 'disabled'; ?>

								<div class="gmw-options-box taxonomy-settings-table-wrapper taxonomy-settings" data-type="<?php echo esc_attr( $style ); ?>">
									<?php $tax_name_attr = esc_attr( $name_attr . '[' . $post . '][' . $taxonomy_name . ']' ); ?>

									<div style="display: inline-block;">

										<div class="single-option">

											<label><?php echo esc_attr_e( 'Usage', 'gmw-premium-settings' ); ?></label>

											<div class="taxonomy-usage taxonomy-tab-content option-content">					

												<select name="<?php echo $tax_name_attr; // WPCS: XSS ok. ?>[style]" class="taxonomy-usage">

													<option value="pre_defined" selected="selected">
														<?php esc_attr_e( 'Pre Defined', 'gmw-premium-settings' ); ?>
													</option>	

													<option value="dropdown" <?php if ( 'dropdown' === $tax_option['style'] ) echo 'selected="selected"'; ?>>
														<?php esc_attr_e( 'Dropdown','gmw-premium-settings' ); ?>
													</option>
													<option value="smartbox" <?php if ( 'smartbox' === $tax_option['style'] ) echo 'selected="selected"'; ?>> 
														<?php esc_attr_e( 'Smartbox','gmw-premium-settings' ); ?>
													</option>
													<option value="smartbox_multiple" <?php if ( 'smartbox_multiple' === $tax_option['style'] ) echo 'selected="selected"'; ?>>
														<?php esc_attr_e( 'Smartbox ( Multiple selections )', 'gmw-premium-settings' ); ?>
													</option>
													<option value="checkbox" <?php if ( 'checkbox' === $tax_option['style'] ) echo 'selected="selected"'; ?>> 
														<?php esc_attr_e( 'Checkboxes', 'gmw-premium-settings' ); ?>
													</option>						
												</select>
												<p class="description">
													<?php esc_attr_e( 'Select the taxonomy usage', 'gmw-premium-settings' ); ?>
												</p>
											</div>
										</div>

										<div class="single-option">
											<label><?php esc_attr_e( 'Include Terms', 'gmw-premium-settings' ); ?></label>
											<div class="tax-content option-content">
												<?php $include_value = isset( $tax_option['include'] ) ? $tax_option['include'] : ''; ?>
												<select 
													multiple 
													placeholder="Select terms to exclude..." 
													class="taxonomies-picker" 
													name="<?php echo $tax_name_attr; // WPCS: XSS ok. ?>[include][]">
													<?php echo GMW_Form_Settings_Helper::get_taxonomy_terms( $taxonomy_name, $include_value ); // WPCS: XSS ok. ?>
												</select>
												<p class="description">
													<?php esc_attr_e( 'Select specific taxonmoy terms to include.', 'gmw-premium-settings' ); ?>
												</p>
											</div>
										</div>

										<div class="single-option">
											<label><?php esc_attr_e( 'Exclude Terms', 'gmw-premium-settings' ); ?></label>
											<div class="tax-content option-content">
												<?php $exclude_value = isset( $tax_option['exclude'] ) ? $tax_option['exclude'] : ''; ?>
												<select 
													multiple 
													placeholder="Select terms to exclude..." 
													class="taxonomies-picker" 
													name="<?php echo $tax_name_attr; // WPCS: XSS ok. ?>[exclude][]"
												>
													<?php echo GMW_Form_Settings_Helper::get_taxonomy_terms( $taxonomy_name, $exclude_value ); // WPCS: XSS ok. ?>
												</select>
												<p class="description">
													<?php esc_attr_e( 'Select specific taxonmoy terms to exclude.', 'gmw-premium-settings' ); ?>
												</p>
											</div>
										</div>

										<div class="taxonomy-enabled-settings">

											<?php $tax_label = esc_attr( stripcslashes( $taxonomy->labels->name ) ); ?>

											<div class="single-option label half">
												<label><?php esc_attr_e( 'Field label', 'gmw-premium-settings' ); ?></label>	
												<div class="tax-content option-content">
													<input 
														type="text" 
														placeholder="<?php esc_attr_e( 'Taxonomy label', 'gmw-premium-settings' ); ?>"
														name="<?php echo $tax_name_attr; // WPCS: XSS ok. ?>[label]"
														value="<?php echo isset( $tax_option['label'] ) ? esc_attr( stripcslashes( $tax_option['label'] ) ) : $tax_label; ?>"
													/>
													<p class="description">
														<?php esc_attr_e( 'Enter lable or leave blank to omit.', 'gmw-premium-settings' ); ?>
													</p>
												</div>
											</div>

											<div class="single-option options-all half" style="display:none">
												<label><?php esc_attr_e( 'Options all label', 'gmw-premium-settings' ); ?></label>	
												<div class="tax-content option-content">
													<input 
														type="text" 
														placeholder="<?php esc_attr_e( 'Options all label', 'gmw-premium-settings' ); ?>" 
														name="<?php echo $tax_name_attr; // WPCS: XSS ok. ?>[show_options_all]"
														value="<?php echo isset( $tax_option['show_options_all'] ) ? esc_attr( stripcslashes( $tax_option['show_options_all'] ) ) : 'All '. $tax_label; ?>"
													/>
													<p class="description">
														<?php esc_attr_e( 'Enter the options all label.', 'gmw-premium-settings' ); ?>
													</p>
												</div>
											</div>

											<div class="single-option orderby half">
												<label><?php esc_attr_e( 'Order terms by', 'gmw-premium-settings' ); ?></label>
												<?php $selected = ! empty( $tax_option['orderby'] ) ? $tax_option['orderby'] : ''; ?>

												<div class="tax-content option-content">
													<select name="<?php echo $tax_name_attr; // WPCS: XSS ok. ?>[orderby]">
														<option value="id" selected="selected"><?php esc_attr_e( 'ID', 'gmw-premium-settings' ); ?></option>
														<option value="name" <?php if ( 'name' === $selected ) echo 'selected="selected"'; ?>>
															<?php esc_attr_e( 'Name', 'gmw-premium-settings' ); ?>
														</option>
														<option value="slug" <?php if ( 'slug' === $selected ) echo 'selected="selected"'; ?>>
															<?php esc_attr_e( 'Slug', 'gmw-premium-settings' ); ?>
														</option>
													</select>
												</div>
											</div>

											<div class="single-option order half">
												<label><?php esc_attr_e( 'Order', 'gmw-premium-settings' ); ?></label>
												<?php $selected = ! empty( $tax_option['order'] ) ? $tax_option['order'] : ''; ?>
												<div class="tax-content option-content">
													<select name="<?php echo $tax_name_attr; // WPCS: XSS ok. ?>[order]">
														<option value="ASC" selected="selected"><?php esc_attr_e( 'ASC', 'gmw-premium-settings' ); ?></option>
														<option value="DESC" <?php if ( $selected == 'DESC' ) echo 'selected="selected"'; ?>><?php esc_attr_e( 'DESC', 'gmw-premium-settings' ); ?></option>
													</select>
												</div>
											</div>

											<div class="single-option show-count half">
												<label>
													<input 
														type="checkbox" 
														name="<?php echo $tax_name_attr; // WPCS: XSS ok. ?>[show_count]"
														value="1" 
														<?php echo ! empty( $tax_option['show_count'] ) ? 'checked="checked"' : ''; ?> 
													/>
													<?php esc_attr_e( 'Show posts count', 'gmw-premium-settings' ); ?>
												</label>				
											</div>

											<div class="single-option hide-empty half">
												<label>	
													<input 
														type="checkbox" 
														name="<?php echo $tax_name_attr; // WPCS: XSS ok. ?>[hide_empty]"
														value="1" <?php echo ! empty( $tax_option['hide_empty'] ) ? 'checked="checked"' : ''; ?>
													/>
													<?php esc_attr_e( 'Hide terms without posts', 'gmw-premium-settings' ); ?>
												</label>					
											</div>

											<?php if ( ! empty( GMW()->options['post_types_settings']['per_category_icons']['enabled'] ) ) { ?>
												<div class="single-option category-icons" style="display:none">
													<label>						
														<input 
															type="checkbox" 
															class="category-icon" 
															name="<?php echo $tax_name_attr; // WPCS: XSS ok. ?>[cat_icons]"
															value="1" 
															<?php echo ! empty( $tax_option['cat_icons'] ) ? 'checked="checked"' : ''; ?> />
														<?php esc_attr_e( 'Category icons', 'gmw-premium-settings' ); ?>
													</label>
												</div>	
											<?php } ?>	
										</div>
									</div>
								</div>		
							</div>
						<?php endforeach; ?>
					</div>
				<?php } ?>
			<?php endforeach; ?> 
			<?php
			$style = ( empty( $form['search_form']['post_types'] ) || ( 0 === count( $form['search_form']['post_types'] ) ) ) ? '' : 'style="display: none;"';

			echo '<div id="post-types-select-taxonomies-message" ' . $style . '>'; // WPCS: XSS ok.
			echo '<p>' . esc_attr__( 'Select a post type to view its taxonomies.', 'gmw-premium-settings' ) . '</p>';
			echo '</div>';

			$style = ( isset( $form['search_form']['post_types'] ) && ( count( $form['search_form']['post_types'] ) == 1 ) ) ? 'style="display: none;"' : '';

			echo '<div id="post-types-no-taxonomies-message" ' . $style . '>'; // WPCS: XSS ok.

			$val = isset( $value['include_exclude_terms'] ) ? $value['include_exclude_terms'] : array();

			echo self::include_exclude_terms( $val, $name_attr . '[include_exclude_terms]', $form ); // WPCS: XSS ok.

			echo '</div>';

			?>
		</div>
		<?php
		wp_enqueue_script( 'jquery-ui-sortable' );
	}

	/**
	 * Custom Fields Settings.
	 *
	 * @param  [type] $name_attr    [description].
	 *
	 * @param  [type] $field_name   [description].
	 *
	 * @param  [type] $field_values [description].
	 *
	 * @param  [type] $is_original  [description].
	 */
	public static function custom_field_generator( $name_attr, $field_name, $field_values, $is_original ) {
		$disabled   = $is_original ? 'disabled="disabled"' : '';
		$name_attr  = esc_attr( $name_attr );
		$field_name = esc_attr( $field_name );

		if ( ! empty( $field_values['label'] ) && is_string( $field_values['label'] ) ) {
			$field_values['label'] = explode( ',', $field_values['label'] );
		}
		?>
		<div class="single-custom-field-wrapper <?php echo ( $is_original ) ? 'original-field' : ''; ?>" data-key="<?php echo ( ! $is_original ) ? $field_name : ''; // WPCS: XSS ok. ?>">						

			<div class="handle custom-field-handle">
				<i class="gmw-icon-sort" title="Sort Field"></i>
			</div>

			<div class="name">
				<input 
					type="text" 
					name="<?php echo $name_attr . '[' . $field_name . '][name]';  // WPCS: XSS ok. ?>" 
					value="<?php echo esc_attr( $field_values['name'] ); ?>"  
					size="15" 
					readonly="readonly"
					<?php echo $disabled; // WPCS: XSS ok. ?>
				/>
			</div>

			<div class="type"> 											
				<select 
					<?php echo $disabled; // WPCS: XSS ok. ?>
					class="type-select gmw-smartbox-not" 
					name="<?php echo $name_attr . '[' . $field_name . '][type]'; // WPCS: XSS ok. ?>"
				>
					<?php
					if ( ! isset( $field_values['type'] ) ) {
						$field_values['type'] = 'CHAR';
					}
					?>

					<?php $options = array( 'CHAR', 'NUMERIC', 'BINARY', 'DATE', 'TIME', 'DECIMAL', 'SIGNED', 'UNSIGNED' ); ?>

					<?php foreach ( $options as $option ) { ?>

						<?php $selected = ( $option === $field_values['type'] ) ? 'selected="selected"' : ''; ?>

						<option value="<?php echo $option; // WPCS: XSS ok. ?>" <?php echo $selected; // WPCS: XSS ok. ?>><?php echo $option; // WPCS: XSS ok. ?></option>
					<?php } ?>
				</select>

				<select 
					<?php echo $disabled; // WPCS: XSS ok. ?>
					class="date-type-select gmw-smartbox-not" 
					name="<?php echo $name_attr . '[' . $field_name . '][date_type]'; // WPCS: XSS ok. ?>" 

					<?php echo ( 'DATE' !== $field_values['type'] ) ? 'style="display:none;"' : ''; ?>>

					<option value="yyyy/mm/dd" selected="selected"><?php echo esc_attr_e( 'Date Type', 'gmw-premium-settings' ); ?></option>

					<?php $options = array( 'yyyy/mm/dd', 'mm/dd/yyyy', 'dd/mm/yyyy' ); ?> 

					<?php
					if ( ! isset( $field_values['date_type'] ) ) {
						$field_values['date_type'] = 'yyyy/mm/dd';
					}
					?>

					<?php foreach ( $options as $option ) { ?>

						<?php $selected = ( $option === $field_values['date_type'] ) ? 'selected="selected"' : ''; ?>

						<option value="<?php echo $option; // WPCS: XSS ok. ?>" <?php echo $selected; // WPCS: XSS ok. ?>><?php echo $option; // WPCS: XSS ok. ?></option>
					<?php } ?>			
				</select>
			</div>

			<div class="compration">
				<select 
					<?php echo $disabled; // WPCS: XSS ok. ?>
					class="compration-select gmw-smartbox-not" 
					name="<?php echo $name_attr . '[' . $field_name . '][compare]'; // WPCS: XSS ok. ?>"
				>
					<?php $options = array( '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS', 'NOT EXISTS' ); ?> 

					<?php
					if ( ! isset( $field_values['compare'] ) ) {
						$field_values['compare'] = '=';
					}
					?>

					<?php foreach ( $options as $option ) { ?>
						<?php $selected = ( $option === $field_values['compare'] ) ? 'selected="selected"' : ''; ?>
						<option value="<?php echo $option; // WPCS: XSS ok. ?>" <?php echo $selected; // WPCS: XSS ok. ?>><?php echo $option; // WPCS: XSS ok. ?></option>
					<?php } ?>
				</select>
			</div>

			<div class="label">
				<span>
					<input 
						type="text" 
						name="<?php echo $name_attr . '[' . $field_name . '][label][0]'; // WPCS: XSS ok. ?>" 
						value="<?php echo isset( $field_values['label'][0] ) ? esc_attr( stripcslashes( $field_values['label'][0] ) ) : ''; ?>"  
						<?php echo $disabled; // WPCS: XSS ok. ?>
						placeholder="<?php esc_attr_e( 'Enter label', 'gmw-premium-settings' ); ?>"
					/>
				</span>
				<span <?php echo ( 'BETWEEN' !== $field_values['compare'] && 'NOT BETWEEN' !== $field_values['compare'] ) ? 'style="display:none;"' : ''; ?>>
					<input 
						type="text" 
						name="<?php echo $name_attr . '[' . $field_name . '][label][1]'; // WPCS: XSS ok. ?>" 
						value="<?php echo isset( $field_values['label'][1] ) ? esc_attr( stripcslashes( $field_values['label'][1] ) ) : ''; ?>"
						<?php echo $disabled; // WPCS: XSS ok. ?>
						placeholder="<?php esc_attr_e( 'Second label', 'gmw-premium-settings' ); ?>"
					/>
				</span>
			</div>

			<div class="placeholder">
				<span>
					<input 
						type="text" 
						name="<?php echo $name_attr . '[' . $field_name . '][placeholder][0]'; // WPCS: XSS ok. ?>" 
						value="<?php echo isset( $field_values['placeholder'][0] ) ? esc_attr( stripcslashes( $field_values['placeholder'][0] ) ) : ''; ?>"  
						<?php echo $disabled; // WPCS: XSS ok. ?>
						placeholder="<?php esc_attr_e( 'Placeholder', 'gmw-premium-settings' ); ?>"
					/>
				</span>
				<span <?php echo ( 'BETWEEN' !== $field_values['compare'] && 'NOT BETWEEN' !== $field_values['compare'] ) ? 'style="display:none;"' : ''; ?>>
					<input 
						type="text" 
						name="<?php echo $name_attr . '[' . $field_name . '][placeholder][1]'; // WPCS: XSS ok. ?>" 
						value="<?php echo isset( $field_values['placeholder'][1] ) ? esc_attr( stripcslashes( $field_values['placeholder'][1] ) ) : ''; ?>"
						<?php echo $disabled; // WPCS: XSS ok. ?>
						placeholder="<?php esc_attr_e( 'Second placeholder', 'gmw-premium-settings' ); ?>"
					/>
				</span>
			</div>

			<div class="custom-field-delete delete">
				<i class="gmw-icon-cancel-light" title="Delete Field"></i>
			</div>
		</div>
		<?php
	}

	/**
	 * Custom fields settings.
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 *
	 * @param  [type] $form      [description].
	 *
	 * @param  [type] $section   [description].
	 */
	public static function custom_fields( $value, $name_attr, $form, $section ) {
		?>
		<div class="gmw-custom-fields-wrapper">

			<div id="header" onClick="jQuery( '.gmw-custom-fields-wrapper' ).find( '#content, #new-field-picker' ).slideToggle();" style="cursor: pointer">
				<?php
				global $wpdb;
				$keys = $wpdb->get_col( "
		        	SELECT meta_key
		        	FROM $wpdb->postmeta
		        	GROUP BY meta_key
		        	ORDER BY meta_id DESC"
				);

				if ( $keys ) {
					natcasesort( $keys );
				}
				?>

				<span class="edit-field-trigger">
					<i class="gmw-icon-cog"></i>
					<?php esc_attr_e( 'Manage Fields', 'gmw-premium-settings' ); ?>
				</span>

			</div>

			<div id="content">

				<div id="new-field-picker" style="display:none; padding:12px;" class="single-custom-field-wrapper">

					<label style="float: left;margin-right: 10px;padding: 5px 0;font-weight: 500">
						<?php esc_attr_e( 'Select Field:', 'gmw-premium-settings' ); ?>
					</label>
					<div style="float: left;width: 350px;margin-right: 10px;">

						<select id="custom-field-picker">
							<?php foreach ( $keys as $key ) : ?>	
							<option value="<?php echo $key; // WPCS: XSS ok. ?>">
								<?php echo $key; // WPCS: XSS ok. ?>
							</option>			
							<?php endforeach; ?>
						</select>	
					</div>

					<div>
						<input 
							type="button" 
							class="button-primary new-field-button" 
							form_id="<?php echo esc_attr( $form['ID'] ); ?>"
							value="<?php esc_attr_e( 'Add field', 'gmw-premium-settings' ); ?>" 
						/>
					</div>
				</div>
				<div class="single-custom-field-wrapper top">
					<div class="name">
						<?php echo esc_attr_e( 'Field', 'gmw-premium-settings' ); ?>
					</div>

					<div class="type"> 											
						<?php echo esc_attr_e( 'Type', 'gmw-premium-settings' ); ?>
					</div>

					<div class="compration">
						<?php echo esc_attr_e( 'Comparison', 'gmw-premium-settings' ); ?>
					</div>

					<div class="label">
						<?php echo esc_attr_e( 'Label', 'gmw-premium-settings' ); ?>
					</div>

					<div class="placeholder">
						<?php echo esc_attr_e( 'Placeholder', 'gmw-premium-settings' ); ?>
					</div>
				</div>

				<div id="custom-fields-holder">

					<?php
					self::custom_field_generator(
						$name_attr,
						'%%field_name%%',
						array(
							'name'    => '',
							'label'   => '',
							'type'    => 'CHAR',
							'compare' => '=',
						),
						true
					); // WPCS: XSS ok.
					?>

					<?php
					if ( ! empty( $value ) ) {
						foreach ( $value as $field_name => $field_values ) {
							self::custom_field_generator( $name_attr, $field_name, $field_values, false );
						}
					}
					?>

				</div>	
			</div>
		</div>
		<?php
	}

	/**
	 * Include exclude terms
	 *
	 * @param  [type] $value       [description].
	 *
	 * @param  [type] $name_attr   [description].
	 *
	 * @param  [type] $form_fields [description].
	 *
	 * @return [type]              [description]
	 */
	public static function include_exclude_terms( $value, $name_attr, $form_fields ) {

		if ( empty( $form_fields['search_form']['post_types'] ) ) {

			echo '<p>' . esc_attr__( 'You need to select at least one post type in the search form tab to be able to use this feature.' ) . '</p>';

			return;
		}

		$post_types = get_post_types( array(), 'objects' );
		$used_taxes = array();

		echo '<div class="include-exclude-taxonomy-terms-wrapper">';

		foreach ( $post_types as $post_type ) {
			$taxonomies = get_object_taxonomies( $post_type->name, 'object' );
			?>
			<div class="gmw-taxonomies-picker-wrapper <?php echo esc_attr( $post_type->name ); ?>" style="display:none">

				<?php $count = 0; ?>

				<?php if ( empty( $taxonomies ) ) { ?>

					<div class="header">
						<i href="#" class="gmw-icon-cancel-light" title="taxonomy"></i>
						<label><?php echo esc_attr( $post_type->label . ' ( ' . $post_type->name . ' )' ); ?></label>
						<span><?php esc_attr_e( '- no taxonomies found.', 'gmw-premium-settings' ); ?></span>
					</div>

					<?php $count++; ?>

				<?php } else { ?>

					<div class="header">
						<i href="#" class="gmw-icon-cog" title="Sort taxonomy"></i>
						<label><?php echo esc_attr( $post_type->label . ' ( ' . $post_type->name . ' )' ); ?></label>
					</div>

					<?php $count = 0; ?>

					<div class="single-taxonomy-picker gmw-options-box <?php echo esc_attr( $post_type->name ); ?>">
						<ul class="options-tabs">
							<?php foreach ( $taxonomies as $taxonomy ) { ?>
								<li class="option-tab <?php echo esc_attr( $taxonomy->name ); ?> <?php if ( 0 === $count ) echo 'active'; // WPCS: XSS ok. ?>">
									<a href="#" class="tab-anchor <?php echo esc_attr( $taxonomy->name ); ?>">
										<?php echo esc_attr( $taxonomy->label . ' ( ' . $taxonomy->name . ' )' ); ?>
									</a>
								</li>
								<?php $count++; ?>
							<?php } ?>
						</ul>

						<ul class="options-tabs-content">

							<?php $count = 0; ?>

							<?php foreach ( $taxonomies as $taxonomy ) { ?>

								<?php if ( isset( $used_taxes[ $taxonomy->name ] ) ) { ?>

								<li class="option-content tab-content <?php echo esc_attr( $taxonomy->name ); ?>" <?php if ( $count == 0 ) echo 'style="display:block"'; ?>>

									<div class="single-option">
										<div class="option-content">
											<p>You can set this taxonomy under the "<?php echo $used_taxes[ $taxonomy->name ]; ?>" post type which uses the same taxnomies.</p>
										</div>
									</div>
								</li>

								<?php } else { ?>

										<?php $used_taxes[ $taxonomy->name ] = $post_type->name; ?>

										<li class="option-content tab-content <?php echo esc_attr( $taxonomy->name ); ?>" <?php if ( $count == 0 ) echo 'style="display:block"'; ?>>

										<div class="single-option">
											<label><?php esc_attr_e( 'Include Terms', 'gmw-premium-settings' ); ?></label>

											<div class="option-content">
												<?php $selected = ! empty( $value[$taxonomy->name]['include'] ) ? $value[ $taxonomy->name ]['include'] : array(); ?>
												<select 
													multiple 
													class="taxonomies-picker" 
													name="<?php echo esc_attr( $name_attr . '[' . $taxonomy->name . '][include][]' ); // WPCS: XSS ok. ?>"
												>
													<?php echo GMW_Form_Settings_Helper::get_taxonomy_terms( $taxonomy->name, $selected ); // WPCS: XSS ok. ?>
												</select>
											</div>
										</div>

										<div class="single-option">
											<label><?php esc_attr_e( 'Exclude Terms', 'gmw-premium-settings' ); ?></label> 
											<div class="option-content">
												<?php $selected = ! empty( $value[ $taxonomy->name ]['exclude'] ) ? $value[ $taxonomy->name ]['exclude'] : array(); ?>
												<select 
													multiple 
													placeholder="Select terms to exclude..." 
													class="taxonomies-picker" 
													name="<?php echo esc_attr( $name_attr . '[' . $taxonomy->name . '][exclude][]' ); ?>"
												>
													<?php echo GMW_Form_Settings_Helper::get_taxonomy_terms( $taxonomy->name, $selected ); // WPCS: XSS ok. ?>
												</select>
											</div>
										</div>
									</li>
								<?php } ?>
								<?php $count++; ?>
							<?php } ?>
						</ul>
					</div>
				<?php } ?>
			</div>
		<?php
		}
		echo '</div>';
	}

	/**
	 * Validate settings.
	 *
	 * @param  [type] $output [description].
	 *
	 * @param  [type] $form   [description].
	 *
	 * @return [type]         [description]
	 */
	public static function validate_include_exclude_terms( $output, $form ) {

		if ( ! is_array( $output ) || empty( $output ) ) {
			return array();
		}

		$valid = array();

		foreach ( $output as $tax_name => $tax_data ) {
			if ( empty( $tax_data ) ) {
				continue;
			}

			foreach ( $tax_data as $action => $ids ) {

				// make sure there aren't duplicate values.
				$ids = array_unique( $ids );

				if ( 'include' === $action ) {
					$valid[ $tax_name ]['include'] = array_map( 'intval', $ids );
				}
				if ( 'exclude' === $action ) {
					$valid[ $tax_name ]['exclude'] = array_map( 'intval', $ids );
				}
			}
		}

		return $valid;
	}
}
new GMW_PS_PT_Form_Settings();
