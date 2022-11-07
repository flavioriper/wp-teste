=== Asaas Gateway for WooCommerce ===
Contributors: asaas, aztecweb
Donate link:
Tags: asaas, payment, payment gateway, woocommerce, credit card, bank ticket
Requires at least: 4.4
Tested up to: 5.9.3
Requires PHP: 7.0
Stable tag: 2.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Take transparent credit card and bank ticket payment checkouts on your store using Asaas.

== Description ==

Use [Asaas](https://www.asaas.com) as payment method in your WooCommerce store.

This plugin is an implementation of [Asaas API v3](https://asaasv3.docs.apiary.io). The checkout mechanism is completely transparent. The customer will not go out of your store to finish the order. The data are sent to Asaas service, that process the payment and return its status.

For any doubt about the plugin installation and integration, please read the FAQ. If it doesn't solve, use the plugin Support area that we will help you as soon as possible.

== Installation ==

This gateway requires WooCommerce 2.6 and above.

= From your WordPress dashboard =

1. Visit _Plugins > Add New_.
1. Search for _Asaas Gateway for WooCommerce_.
1. Click in _Install Now_ and, after, in _Activate_.
1. Visit _WooCommerce > Settings > Checkout_.
1. Visit _Asaas Ticket_ and _Asaas Credit Card_ to enable, set up and integrate the gateway with Asaas system.

= From WordPress.org =

1. Download _Asaas Gateway for WooCommerce_
1. Unzip the file and upload the _woo-asaas_ directory to your _wp-content/plugins_ directory using your favorite method (ftp, sftp, scp, etc…). Or visit _Plugins > Add New > Upload Plugin_, select _woo-asaas.zip_ file, click in _Install Now_ and, after, in _Activate Plugin_.
1. Search for _Asaas Gateway for WooCommerce_.
1. Visit _WooCommerce > Settings > Checkout_.
1. Visit _Asaas Ticket_ and _Asaas Credit Card_ to enable, set up and integrate the gateway with Asaas system.

== Screenshots ==

1. Checkout example
2. Bank ticket settings
3. Credit card settings

== Changelog ==

= 2.0.1 =
* Fix - Fixes manual renewal message
* Tweak - Allows change to active status, when subscription has parent order paid or refunded

= 2.0.0 =
* Feature - WooCommerce Subscription compatibility, allow payment with subscription products
* Tweak - Allow pix expiration in minutes, hours or days

= 1.8.4 =
* Tweak - Change interest per installment label
* Tweak - Allow ticket installment until 60 installments

= 1.8.3 =
* Fix - First credit card purchase error

= 1.8.2 =
* Fix - PHP 7.3 compability fixes

= 1.8.1 =
* Fix - Accept empty installment interest value
* Fix - Add interest at checkout to the order with an installment, if set

= 1.8.0 =
* Feature - Remove anticipate receipt of payment confirmation setting
* Feature - Reduces the minimum amount of installments
* Fix - One click buy card selection render

= 1.7.0 =
* Tweak - Remove credit card number validation of plugin side
* Tweak - Update BINs credit card brands list
* Fix - Fouble customer create request to API on PIX checkout
* Fix - Customer neighborhood and company API integration

= 1.6.3 =
* Fix - PHP 7.0 compability

= 1.6.2 =
* Fix - Error on activate installments
* Fix - Notice when increase intallments number

= 1.6.1 =
* Fix - Syntax error for some PHP installations
* Fix - Boleto installments crash on thank you page for PHP 8

= 1.6.0 =
* Feature - Interest per installment
* Tweak - New default credit card payment method message
* Fix - Fix JS error on all pages
* Template - Improve PIX payment instructions template
* Localization - Remove official WordPress translation repository dependency

= 1.5.0 =
* Feature - New payment method PIX

= 1.4.0 =
* Feature - PIX payment is processed as boleto

= 1.3.5 =
* Tweak - Use WC_Order::payment_complete to set payment complete
* Tweak - Remove setting to approve a payment when it is confirmed on boleto

= 1.3.4 =
* Tweak - Translates webhook events order notes
* Fix - Refactor order taxes and discount on credit card payment processing
* Fix - Changes condition to don't process paymet if the order is considered paid

= 1.3.3 =
* Tweak - Return 200 on webhook when the event is unsupported
* Fix - Solve fatal error with WooCommerce Payments plugin integration
* Fix - Proccess webhook when the order status is on-hold

= 1.3.2 =
* Tweak - Allow disable overdue boleto on Asaas removal
* Fix - Remove only store overdue boleto on Asaas
* Fix - Doesn't change order value on receive boleto installment

= 1.3.1 =
* Fix - Add missing assets

= 1.3.0 =
* Feature - Added Hipercard credit card option
* Feature - Deposit and transfer payments are processed as boleto
* Feature - Boleto installments
* Feature - Setting to approve a payment when it is confirmed
* Tweak - Warning if WooCommerce isn't installed
* Tweak - Remove overdue boleto on Asaas automatically
* Fix - Credit Card purchase don't back anymore to processing when completed
* Fix - Payment using My Account orders pay options

= 1.2.4 =
* Fix - Mandatory installment error

= 1.2.3 =
* Fix - Request timeout was increaced

= 1.2.2 =
* Fix - Fix token validation

= 1.2.1 =
* Fix - Fix webhook return on request error
* Fix - Request timeout was increaced

= 1.2.0 =
* Feature - Show a warning message on admin if any type of person is set on WooCommerce Extra Checkout Fields for Brazil settings

= 1.1.0 =
* Tweak - Added `woocommerce_asaas_request_api_key` hook to manipulate the request api key
* Tweak - Added `woocommerce_asaas_set_customer_params` and `woocommerce_asaas_set_customer_action` hook to manipulate the customer request

= 1.0.13 =
* Fix - Fix webhook invalid billing type treatment

= 1.0.12 =
* Fix - Fix webhook return when the external reference isn't WooCommerce order

= 1.0.11 =
* Tweak - Update log settings to link with WooCommerce status log screen
* Tweak - Log webhook response

= 1.0.10 =
* Tweak - Update absent webhook event message
* Tweak - Return 200 code to webhook when the reference is a number but not a WooCommerce order

= 1.0.9 =
* Tweak - Return 200 code to webhook when the reference isn't WooCommerce order

= 1.0.8 =
* Tweak - Return 200 for unidentified events on webhook
* Tweak - Ignore non WooCommerce payments on webhook
* Tweak - Add uppercase to the first letter of payment status on order details
* Fix - Set payment as completed when the credit card status is RECEIVED or CONFIRMED
* Fix - Order total call on webhook class

= 1.0.7 =
* Fix - Min installment in checkout
* Fix - Checkout Fields integration to work with one person type setting
* Fix - Webhook endpoint rewrite rule

= 1.0.6 =
* Fix - Fix credit card form fields render

= 1.0.5 =
* Fix - Add missing assets files

= 1.0.4 =
* Tweak - Use plugin version as query string in CSS and JS files to clear browser cache
* Tweak - Update webhook message when integrate got Asaas API error status code
* Fix - Get the customer IP in gateway process payment function
* Fix - Webhook log messages

= 1.0.3 =
* Tweak - Add description to payment
* Tweak - Accept beetween 2 and 4 digits in card expiration year
* Fix - New customer notification wasn't the same setting value
* Fix - When change notification setting. The setting is applied just for registered users.

= 1.0.2 =
* Tweak - Validate if webhook payment status is the same of registered in Asaas
* Tweak - Set production environment as default
* Fix - Ticket URL on order inner customer account page

= 1.0.1 =
* Tweak – Change plugin name in respect to [plugin guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/#17-plugins-must-respect-trademarks-copyrights-and-project-names)
* Tweak – Hide credit card data in logs
* Tweak - Ensure the minimum ticket due date for bank ticket
* Tweak - Remove `woocommerce-` prefix from gateway ids
* Tweak - Improve API integration
* Fix - Fixed enable customer notification API

= 1.0.0 =
* First Release.

== Upgrade Notice ==

= 1.0.0 =
* First Release

