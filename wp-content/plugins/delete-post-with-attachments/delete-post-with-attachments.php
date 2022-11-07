<?php
/**
 *
 * @link              https://www.alsvin-tech.com/
 * @since             1.0.0
 * @package           Alsvin_Delete_Post_With_Attachments
 *
 * @wordpress-plugin
 * Plugin Name:       Delete Post with Attachments
 * Plugin URI:        https://www.alsvin-tech.com/
 * Description:       A simple plugin to delete attached media files e.g. images/videos/documents, when the post is deleted.
 * Version:           1.1.2
 * Requires at least: 4.1
 * Requires PHP:      5.6
 * Author:            Alsvin
 * Author URI:        https://profiles.wordpress.org/alsvin/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       alsvin-dpwa
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if( !class_exists('Alsvin_Delete_Post_With_Attachments') ) {
	class Alsvin_Delete_Post_With_Attachments {

		/**
		 * Alsvin_Delete_Post_With_Attachments constructor.
		 */
		public function __construct() {
			add_action( 'before_delete_post', [$this, 'before_delete_post_cb' ] );
		}

		/**
		 * @param $post_id
		 */
		public function before_delete_post_cb( $post_id ) {
			$attachments = get_attached_media( '', $post_id );

			foreach ($attachments as $attachment) {
				$attachment_used_in = $this->get_posts_by_attachment_id($attachment->ID);

				$is_parent = $attachment->post_parent === $post_id;

				if( $is_parent ) {

					$other_posts_exits_content = array_diff( $attachment_used_in['content'],[$post_id]);
					$other_posts_exits_thumb = array_diff( $attachment_used_in['thumbnail'],[$post_id]);
					$other_posts_exits = array_merge($other_posts_exits_content, $other_posts_exits_thumb);
					if( !empty($other_posts_exits) ) {
						wp_update_post([
							'ID' => $attachment->ID,
							'post_parent' => $other_posts_exits[0]
						]);
					} else {
						wp_delete_attachment( $attachment->ID, 'true' );
					}
				}
			}
		}

		/**
		 * @param $attachment_id
		 *
		 * @return array
		 */
		private function get_posts_by_attachment_id( $attachment_id ) {
			$used_as_thumbnail = array();

			if ( wp_attachment_is_image( $attachment_id ) ) {
				$thumbnail_query = new WP_Query( array(
					'meta_key'       => '_thumbnail_id',
					'meta_value'     => $attachment_id,
					'post_type'      => 'any',
					'fields'         => 'ids',
					'no_found_rows'  => true,
					'posts_per_page' => - 1,
					'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash')
				) );

				$used_as_thumbnail = $thumbnail_query->posts;
			}

			$attachment_urls = array( wp_get_attachment_url( $attachment_id ) );

			if ( wp_attachment_is_image( $attachment_id ) ) {
				foreach ( get_intermediate_image_sizes() as $size ) {
					$intermediate = image_get_intermediate_size( $attachment_id, $size );
					if ( $intermediate ) {
						$attachment_urls[] = $intermediate['url'];
					}
				}
			}

			$used_in_content = array();

			foreach ( $attachment_urls as $attachment_url ) {
				$content_query = new WP_Query( array(
					's'              => $attachment_url,
					'post_type'      => 'any',
					'fields'         => 'ids',
					'no_found_rows'  => true,
					'posts_per_page' => - 1,
					'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash')
				) );

				$used_in_content = array_merge( $used_in_content, $content_query->posts );
			}

			$used_in_content = array_unique( $used_in_content );

			return array(
				'thumbnail' => $used_as_thumbnail,
				'content'   => $used_in_content,
			);
		}
	}

	$alsvin_dpwa = new Alsvin_Delete_Post_With_Attachments;
}