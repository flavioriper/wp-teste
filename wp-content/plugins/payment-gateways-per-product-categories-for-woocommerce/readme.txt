=== Payment Gateways Per Products/Categories/Tags for WooCommerce ===
Contributors: omardabbas
Tags: woocommerce, payment gateway, woo commerce
Requires at least: 4.4
Tested up to: 6.0
Stable tag: 1.7.1
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html


Increase profit margins for your products by deciding which payment gateways to show in WooCommerce checkout page depending on selected product, product category, or product tag being in cart.

== Description ==

If you want to control (show / hide) specific payment gateways for some products, this plugin will give you a full control on what payments to be used based on product tag, category.

**Customize Payment Gateways per Products** plugin lets you show WooCommerce gateway only if there is product of selected **category or tag** in cart. Alternatively you can hide gateway by selected category or tag.

The plugin becomes very handy when you have specific product / category that has low profit margins, and specific payment gateways (PayPal for example) that charge high transaction fees, you can then force buyers to select payment gateways that gets you better profit.

If you want more control over specific products (rather than tags or category), then our premium [Payment Gateways per Products for WooCommerce Pro](https://wpfactory.com/item/payment-gateways-per-product-for-woocommerce/) plugin is for you, this version has options to show/hide the gateways on **per product** basis (i.e. instead of on per product category / tag basis).

= Demo Store =

If you want to try the plugin features, play around with its settings before installing it on your live website, feel free to do so on this demo store:
URL: https://wpwhale.com/demo/wp-admin/
User: demo
Password: G6_32e!r@

= Feedback =
* We are open to your suggestions and feedback. Thank you for using or trying out one of our plugins!
* Please visit [Payment Gateways per Products for WooCommerce plugin page](https://wpfactory.com/item/payment-gateways-per-product-for-woocommerce/).

== Screenshots ==

1. Main Page
2. Specify settings per category
3. Specify settings per tag

== Installation ==

1. Upload the entire plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Start by visiting plugin settings at "WooCommerce > Settings > Payment Gateways per Products".

== Changelog ==

= 1.7.1 - 12/06/2022 =
* Verified compatibily with WordPress 6.0 & WooCommerce 6.5

= 1.7 - 18/04/2022 =
* Fixed an uncaught error related to a JS file (select2)
* Fixed a bug in client area where gateways were hidden without products in cart
* Verified compatibily with WooCommerce 6.4

= 1.6.4 - 28/01/2022 =
* Allowed mixing includes/excludes while giving priority to product-defined settings over category/attribute
* Verified compatibily with WooCommerce 6.2 

= 1.6.3 - 28/01/2022 =
* Verified compatibily with WordPress 5.9 & WooCommerce 6.1

= 1.6.2 - 10/11/2021 =
* Fixed a bug in WPML compatibility when switching between languages settings were lost
* Verified compatibility with WooCommerce 5.9

= 1.6.1 - 29/10/2021 =
* Fixed a bug in showing category IDs instead of names for some users after 1.4.5

= 1.6 - 26/10/2021 =
* Fixed a bug in 1.4.5 preventing Pro users from using Pro features
* Verified compatibility with WooCommerce 5.8

= 1.4.5 - 19/10/2021 =
* Fixed a bug showing category IDs instead of category names
* Allowed choosing payment method from product edit page directly

= 1.4.4 - 16/10/2021 =
* Fixed multiple issues (error 500) for stores with thousands of products
* Verified compatibility with WooCommerce 5.7

= 1.4.3 - 30/08/2021 =
* Checked & verified compatibility with WooCommerce 5.6

= 1.4.2 - 16/08/2021 =
* Fixed a bug not showing specific custom gateways
* Added an integration to manually added orders emails to show/hide gateways as in store

= 1.4.1 - 25/07/2021 =
* Tested compatibilty with WC 5.5 & WP 5.8

= 1.4 - 16/05/2021 =
* New feature: Fallback gateway to show a selected gateway if mixed products (with different gateways) are in cart.
* Verified compatibility with WooCommerce 5.3

= 1.3.4 - 20/04/2021 =
* Tested compatibilty with WC 5.1 & WP 5.7

= 1.3.3 - 28/02/2021 =
* Tested compatibilty with WC 5.0

= 1.3.2 - 27/01/2021 =
* Tested compatibility with WP 5.6 & WC 4.9

= 1.3.1 - 21/11/2020 =
* Tested compatibility with WC 4.7

= 1.3 - 15/08/2020 =
* Tested compatibility with WP 5.5
* Tested compatibility with WC 4.3

= 1.2.1 - 20/11/2019 =
* Dev - Code refactoring.
* WC tested up to: 3.8.
* Tested up to: 5.3.
* Plugin author changed.

= 1.2.0 - 12/07/2019 =
* Dev - Advanced Options - Add filter - Default value set to `On "init" action`.
* Dev - Per Products - Adding product ID to the list of products in settings.
* Dev - Code refactoring.

= 1.1.1 - 24/05/2019 =
* Dev - Admin Settings - "Your settings have been reset" notice added.
* Tested up to: 5.2.
* WC tested up to: 3.6.

= 1.1.0 - 29/11/2018 =
* Fix - Text domain fixed.
* Dev - Products - "Add variations" option added.
* Dev - Admin settings restyled: divided into separate ("Categories", "Tags" and "Products") sections (and "Enable section" options added).
* Dev - Plugin renamed from "Payment Gateways per Product Categories for WooCommerce" to "Payment Gateways per Products for WooCommerce".
* Dev - Advanced Options - "Add filter" option added.
* Dev - Code refactoring.
* Dev - Plugin URI updated.

= 1.0.0 - 28/08/2017 =
* Initial Release.

== Upgrade Notice ==

= 1.0.0 =
This is the first release of the plugin.
