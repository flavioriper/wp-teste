<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

/**
 * GMW_PT_Admin class
 */
class GMW_PS_Category_icons {

	/**
	 * GMW options
	 * 
	 * @var [type]
	 */
	public $options;

	/**
	 * Icons collections saved in global
	 * 
	 * @var [type]
	 */
	public $icons;

	/**
	 * Categoy icons saved in database
	 * 
	 * @var [type]
	 */
	public $saved_category_icons;

	/**
	 * Categoy map icons saved in database
	 * 
	 * @var [type]
	 */
	public $saved_category_map_icons;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
				
		$this->icons 		  	        = gmw_get_icons(); 
		$this->options  		        = GMW()->options;
		$this->saved_category_icons     = get_option( 'gmw_category_icons' );
		$this->saved_category_map_icons = get_option( 'gmw_category_map_icons' );

		//check if feature enabled and taxonomies exist
		if ( empty( $this->options['post_types_settings']['per_category_icons']['enabled'] ) || empty( $this->options['post_types_settings']['per_category_icons']['taxonomies'] ) ) {
			return;
		}

		// make sure taxonomies are selected for category icons
		if ( ! is_array( $this->options['post_types_settings']['per_category_icons']['taxonomies'] ) ) {
			return;
		}

		// enable the map icons feature for each selected taxonomy/cattegory
		foreach ( $this->options['post_types_settings']['per_category_icons']['taxonomies'] as $tax ) {

			add_action( 'edited_'.$tax,	array( $this, 'update_category_icons' ) );
			add_action( 'created_'.$tax, array( $this, 'update_category_icons' ) );
			add_action( 'delete_term', array( $this, 'delete_category_icons'  ) );	
		
			add_filter( 'manage_edit-'.$tax.'_columns',  array( $this, 'table_column_name' ) );
			add_filter( 'manage_'.$tax.'_custom_column', array( $this, 'table_column_content' ), 10, 3 );

			add_filter( $tax.'_edit_form_fields', array( $this, 'display_category_icons' ) );
			add_filter( $tax.'_add_form_fields', array( $this, 'display_category_icons'  ), 10 );	

			add_filter( $tax.'_add_form_fields', array( $this, 'display_refresh_icons_button' ), 10 );			 
		}
	}

	/**
	 * Update category icons
	 * 
	 * @param  int $term_id term ID
	 * 
	 * @return void
	 */
	public function update_category_icons( $term_id ) {

		//update category icons
		$this->saved_category_icons[$term_id] = ! empty( $_POST['gmw_category_icon'] ) ? strip_tags( $_POST['gmw_category_icon'] ) : '';
		update_option( 'gmw_category_icons', $this->saved_category_icons);
	
		//update map icons
		$this->saved_category_map_icons[$term_id] = ! empty( $_POST['gmw_category_map_icon'] ) ? strip_tags( $_POST['gmw_category_map_icon'] ) : '';
		update_option( 'gmw_category_map_icons', $this->saved_category_map_icons );

		// collect new icons into database
		gmw_ps_collect_icons();
	}
	
	/**
	 * Delete category icons
	 * 
	 * @param  int $term_id term ID
	 * 
	 * @return void 
	 */
	public function delete_category_icons( $term_id ) {

		//Delete category icon
		unset( $this->saved_category_icons[$term_id] );
		update_option( 'gmw_category_icons', $this->saved_category_icons );
		 
		//Delete map icon
		unset( $this->saved_category_map_icons[$term_id] );
		update_option( 'gmw_category_map_icons', $this->saved_category_map_icons );		

		// coolect new icons into database
		gmw_ps_collect_icons();
	}

	/**
	 * Table column label
	 * 
	 * @param  [type] $columns [description]
	 * 
	 * @return [type]          [description]
	 */
	public function table_column_name( $columns ){
		
		//hide map icons column if using same as category icons
		if ( empty( $this->options['post_types_settings']['per_category_icons']['same_icons'] ) ) {
			$columns['gmw_category_map_icon'] = __( 'Map Icon','gmw-premium-settings' );
			$columns['gmw_category_icon']     = __( 'Category Icon','gmw-premium-settings' );
		} else {
			$columns['gmw_category_icon'] = __( 'Category / Map Icon','gmw-premium-settings' );
		} 

		return $columns;
	}
	
	/**
	 * Add content to category table in add/new category page
	 * @param  [type] $content     [description]
	 * @param  [type] $column_name [description]
	 * @param  [type] $term_id     [description]
	 * @return [type]              [description]
	 */
	public function table_column_content( $content, $column_name, $term_id ) {

		// display category icon in term row
		if ( $column_name == 'gmw_category_icon' ) {
			
			if ( ! empty( $this->saved_category_icons[$term_id] ) ) {

				$url = $this->icons['pt_category_icons']['url'] . $this->saved_category_icons[$term_id];

				$content .=  '<img src="'.esc_url( $url ).'" />';
			
			} else {
				$content .= __( 'N/A', 'gmw-premium-settings' );
			}
		}
		
		// hide map icons column if using same as category icons
		if ( empty( $this->options['post_types_settings']['per_category_icons']['same_icons'] ) ) {
			
			if ( $column_name == 'gmw_category_map_icon' ) {	
				
				if ( ! empty( $this->saved_category_map_icons[$term_id] ) ) {	

					$url = $this->icons['pt_map_icons']['url'] . $this->saved_category_map_icons[$term_id];

					$content .= '<img src="'.esc_url( $url ).'" />';	
				
				} else {
				
					$content .= __( 'N/A','gmw-premium-settings' );
				}	
			}
		}

		return $content;
	}
		
	/**
	 * Display category icons to choose from in category page
	 * 
	 * @param  [type] $tag [description]
	 * @return [type]      [description]
	 */
	public function display_category_icons( $tag ) {
		
		$category_icons = $this->icons['pt_category_icons']['all_icons'];
		$map_icons 		= $this->icons['pt_map_icons']['all_icons'];
		
		// check if in edit taxonomy term page. We will generate the data below based on that.
		// Since in new term page there is no table wrapping the data which exists in the edit term page
		$edit_tag_page = ! empty( $_GET['tag_ID'] ) ? true : false;

		// show tag based on if map icons same as category icons
		$label = empty( $this->options['post_types_settings']['per_category_icons']['same_icons'] ) ?  __( 'Category Icons','GMW_PS' ) : __( 'Category / Map Icons','GMW_PS' );		
		?>

		<?php if ( $edit_tag_page ) { ?>

			<tr class="form-field term-category-icons-wrap">
			
				<th><?php echo $label; ?></th>
			
				<td>
		
		<?php } ?>
		
		<div class="form-field term-category-icons-wrap">
			
			<?php if ( ! $edit_tag_page ) { ?>

				<label for="gmw-category-icon"><?php echo $label; ?></label>
				
			<?php } ?>

			<div class="category-icons icons">
				<?php 
				$cic = 1;
				
				foreach ( $category_icons as $category_icon ) {
					
					$checked = '';
					
					// look for checked icon only if in edit tag page 
					if ( $cic == 1 || ( isset( $_GET['taxonomy'] ) && ! empty( $_GET['tag_ID'] ) && ! empty( $this->saved_category_icons[$tag->term_id] ) && $this->saved_category_icons[$tag->term_id] == $category_icon ) ) {
						$checked =  'checked="checked"';
					}

					echo '<label>';
					echo '<input type="radio" name="gmw_category_icon" value="'.esc_attr( $category_icon ).'" '.$checked.'/>';
					echo '<img src="'.esc_url( $this->icons['pt_category_icons']['url'].$category_icon ).'"/>';
					//echo '<span>'. esc_attr( pathinfo( $category_icon, PATHINFO_EXTENSION ) ) . '</span>';
					echo '</label>';
					$cic++;
				}					
			?>

			</div>
		
		</div>

		<?php if ( $edit_tag_page ) { ?>
			
				</td>
			</tr>
		<?php } ?>

		<!-- hide map icons column if using same as category icons -->
		<?php if ( empty( $this->options['post_types_settings']['per_category_icons']['same_icons'] ) ) { ?>
				
				<?php $label = __( 'Category Map Icons','gmw-premium-settings' ); ?>

				<?php if ( $edit_tag_page ) { ?>
		
					<tr class="form-field term-category-map-icons-wrap">
						
						<th><?php echo $label; ?></th>
						
						<td>

				<?php } ?>

				<div class="form-field term-category-map-icons-wrap">

					<?php if ( ! $edit_tag_page ) { ?>
						
						<label for="gmw-category-icon">
							<?php echo $label; ?>
						</label>

					<?php } ?>

					<div class="category-map-icons icons">
						<?php
						$cic = 1;	
						foreach ( $map_icons as $map_icon ) {
							$checked = '';

							// look for checked icon only if in edit tag page 
							if ( $cic == 1 || ( isset( $_GET['taxonomy'] ) && ! empty( $_GET['tag_ID'] ) && ! empty( $this->saved_category_map_icons[$tag->term_id] ) && $this->saved_category_map_icons[$tag->term_id] == $map_icon ) ) {
								$checked =  'checked="checked"';
							}

							echo '<label>';
							echo '<input type="radio" id="gmw-category-icon" name="gmw_category_map_icon" value="'.esc_attr( $map_icon ).'" '.$checked.'/>';
							echo '<img src="'.esc_url( $this->icons['pt_map_icons']['url'].$map_icon ).'" />';
							//echo '<span>'. esc_attr( pathinfo( $map_icon, PATHINFO_EXTENSION ) ) . '</span>';
							echo '</label>';
							$cic++;
						}		
						?>
					</div>

				</div>

				<?php if ( $edit_tag_page ) { ?>
			
						</td>

					</tr>

				<?php } ?>

			<?php } ?>

		<?php
	}

	/**
	 * Display category icons to choose from in category page
	 * @param  [type] $tag [description]
	 * @return [type]      [description]
	 */
	public function display_refresh_icons_button( $tag ) {
	 	
	 	$tax = ! empty( $_GET['taxonomy'] ) ? $_GET['taxonomy'] : 'category';

		gmw_refresh_map_icons_button( 'edit-tags.php?taxonomy='.$tax ); 
	}
}

function gmw_category_page_init() {
	new GMW_PS_Category_icons();
}
add_action( 'admin_init', 'gmw_category_page_init' );
?>