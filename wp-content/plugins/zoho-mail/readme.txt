=== Zoho Mail for WordPress ===
Contributors: Zoho Mail
Tags: mail,mailer,phpmailer,wp_mail,email,zoho,zoho mail
Donate link: none
Requires at least: 4.8
Tested up to: 5.9.3
Requires PHP: 5.6
Stable tag: 1.4.8
License: BSD
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Zoho Mail Plugin lets you configure your Zoho Mail account on your WordPress site enabling you to send the email via Zoho Mail API.

== Description ==

= Zoho Mail for WordPress =

Zoho Mail Plugin helps you to configure your Zoho Mail account in your WordPress site, to send emails from your Site.
It is recommended to use authorized server for sending emails from websites, instead of using generic hosting servers. It is possible to misuse unauthorized and unauthenticated configuration and harm the reputation of your domain/ website when using generic servers. 
Zoho Mail plugin can help to ensure that the emails are sent from your account using Zoho Mail API's.

== PRE-REQUISITES ==
- A Zoho Mail Account
- A self-hosted WordPress site
- PHP 5.6 or later

== ADVANTAGES OF ZOHO MAIL PLUGIN ==
- Zoho Mail plugin makes use of **OAuth 2.0** protocol to access Zoho Mail API. This ensures a highly secure authentication process where the Username or password is not stored so cannot be misused.
- Zoho Mail plugin has customized the **PHPMailer’s** code library, used in WordPress for sending email.
- By using **’wp_mail’** function of WordPress, Zoho Mail plugin handles the custom send mail action anywhere from the entire site, without having to change/ configure in every occurrence.

== ZOHO MAIL API FEATURES ==
- Zoho Mail API is authenticated using OAuth 2.0 protocol.
- You can configure your Zoho Mail account in your website to send email using Zoho Mail API.
- The emails sent will be available in the corresponding Zoho Mail account's Sent folder.

== INSTALLATION ==
1) Login to your self-hosted WordPress account and navigate to the Zoho Mail plugin Account Configuration page.
2) Copy the **Authorized Redirect URI** from the configuration page.
3) Login to your Zoho Mail account and access the [Zoho Mail Developer Console](https://accounts.zoho.com/developerconsole) .
4) Click **Add Client ID** and provide the **Client Name** , **Client Domain** and the **Authorized Redirect URI** to generate a new OAuth Client ID and Client secret.Enter the generated Client ID and Client Secret in the Account configuration page.
5) Enter the From **Email Address** and From **Name**.
6) Click **Authorize** .
7) An authorization screen is displayed. 
8) Click the **Accept** button to allow access of data from your Zoho Account.
9) Once the authentication process is done, Zoho Mail Plugin will be able to send emails from your website using Zoho Mail

== ZOHO MAIL PLUGIN PARAMETERS ==
- **Where is your account hosted?** :The region where your Zoho Account data resides.
- **Client ID** :The Client ID of your Zoho Mail API.
- **Client Secret** : The Client secret of your API.
- **Authorized Redirect URI** : Authorized Redirect URL obtained from your website that is used to create Client ID.
- **From Email Address** :The Email address that will be used to send all the outgoing emails from your website.
- **From Name** :The Name that will be shown as the display name while sending all emails from your website.
== ZOHO MAIL PLUGIN TEST EMAIL ==

After configuration, you can test the plugin. Navigate to the Zoho Mail - Test Email page in your Website settings.
- **To** : Email address of the recipient.
- **Subject** : Subject of the email.
- **Content** :The message or body of the email.

For in detail instructions on how to set up Zoho Mail plugin, visit [Zoho Mail plugin page](https://www.zoho.com/mail/help/zohomail-plugin-for-wordpress.html) .
**Note** :
Sending emails through Zoho Mail is subjective to our Usage Policy restrictions. Please refer to our Usage Policy details [here](https://www.zoho.com/mail/help/usage-policy.html).

== Frequently Asked Questions ==
Can I send the email via ZohoMail from my website using this plugin? 
Yes
= Where do I go for help with any issues? =
In case, you are not sure on how to proceed with the Zoho Mail plugin, feel free to contact support@zohomail.com.
= What should I do if I get 'Invalid Client Secret' issue? =
 - While configuring your plugin, ensure you have entered the correct information in the Domain field. Select the region in which your Zoho account is hosted (in, com etc).
 - Verify if the Client ID and Client Secret used in the configuration page matches with the Client created for the plugin in Zoho Developer Console. 
 - If all the above-given troubleshooting methods do not resolve the issue, reach out to our Customer support (support@zohomail.com) with the screenshot of the configuration settings page for a solution. 
== Screenshots ==
1. Configure Account(screenshot-1.png)
2. Test Mail(screenshot-2.png)

== Changelog ==
= 1.0.1 =
* Added notification for successfull authentication while configuring.
* Empty configuration form issue has been handled.
* Support for html content type.
* [Issue fix for Back end font problem](https://wordpress.org/support/topic/back-end-font-issue/).
= 1.0.2 = 
* [Fix for support](https://wordpress.org/support/topic/does-not-work-and-support-does-not-answer-any-emails/) - Support for lower PHP version.
= 1.1 =
* support for customizable From Name in configuration.
= 1.1.1 =
* support for Bcc email field
* Fixed an issue related to style
* Zoho Mail logo change 
* [Fix for Support](https://wordpress.org/support/topic/it-does-not-send-html-emails/) - Support html content type in mails
= 1.1.2 =
* support for .in region
* Bug Fix for .com region
= 1.2 =
* [support for Reply to](https://wordpress.org/support/topic/contact-form-7-reply-to-not-working-properly/) - Support reply to in mails
= 1.2.1 =
* Security Update
= 1.2.2 =
* WooCommerce Fix for replyTo
= 1.2.3 =
* Support for .com.cn region
= 1.2.4 =
* Support third party extension from name
= 1.3 =
* Support for attachments
= 1.3.1 =
* Fix for Invalid Client Secret issue
= 1.3.2 =
* Fix for Missing scope issue while authentication
= 1.3.3 =
* Fix for .cn region
= 1.3.4 =
* Fix for Invalid From Address
= 1.3.5 =
* [CSS fix for <a> tag](https://wordpress.org/support/topic/plugin-changes-link-color-of-admin-dashboard/)
= 1.3.7 =
* Support for .com.au region
= 1.3.8 =
* Issue fix for [Php Warnings](https://wordpress.org/support/topic/bugs-in-newest-version/)
= 1.3.9 =
* Attachment fix
= 1.4.0 =
* Supporting special characters in From Name.
= 1.4.1 =
* Adding troubleshooting inside plugin.
= 1.4.2 =
* Providing option for default content type.
= 1.4.3 = 
* Added admin notice for transmail plugin
= 1.4.4 = 
* Admin notice displayed only in configuration page
= 1.4.5 = 
* Zeptomail Notice and Warning message bug fix
= 1.4.6 =
* Tested for Wordpress 5.8
= 1.4.7 =
* bug fix for cc emailid
= 1.4.8 =
* jp DC support added, domain param changed for clearer ux.


== Upgrade Notice ==
none







