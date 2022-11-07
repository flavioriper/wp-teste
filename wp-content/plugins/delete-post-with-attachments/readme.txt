=== Delete Post with Attachments ===
Contributors: alsvin
Tags: delete, post, attachment, media, file, page, product
Requires at least: 5.1
Tested up to: 6.0
Stable tag: 1.1.2
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple plugin to delete attached media files e.g. images/videos/documents, when the post is deleted.

== Description ==

By default when you delete a post or page, any associated media files or attachments to that post do not get deleted. Keeping these orphan files to your server will eat up a lot of precious web space for no reason.

Using this plugin when you delete a post, any associated attachments will also get deleted automatically.

Before deleting any media file or attachment the plugin smartly checks that the attachment is not in use elsewhere, i.e. on any other post, page, or product.

**Features:**

* No configuration required
* Just activate and use
* Save your precious server storage
* Works automatically on post/page deletion


== Installation ==

1. Upload the plugin directory to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress


== Frequently Asked Questions ==

= Is this plugin compatible with the latest version of WordPress? =

Yes, all our add-ons are compatible with the latest version of WordPress.

= Does this plugin require any configuration after installation?

No, there is absolutely no configuration required, just activate the plugin and it will start working.

= Does this delete any associated media with the post?

Yes, when you delete a page/post this plugin will check if there is any associated media or attachment to this post/page it will be deleted also.

= Does this plugin work with custom post types also?

Yes, it works with all types, posts, pages, products etc

= What if a single attachment is used on multiple posts?

If a single attachment is used on multiple posts, the attachment will not get deleted until all associated posts are deleted.

= What if attachment is used as a featured image, does it get also deleted on post deletion?

Yes, the plugin will check if the image is not used in any other post then it will get deleted along with the current post.


== Changelog ==

= 1.1.2 =
* Change - Compatibility with WordPress 6.0

= 1.1.1 =
* Change - Compatibility with WordPress 5.9

= 1.1.0 =
* New - Implement featured image deletion

= 1.0.0 =
* Initial release


== Upgrade Notice ==
