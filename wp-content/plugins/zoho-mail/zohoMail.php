<?php
//require_once('wp-load.php');
/*
Plugin Name: Zoho Mail
Version: 1.4.8
Plugin URI: http://mail.zoho.com
Author: Zoho Mail
Author URI: https://www.zoho.com/mail/
Description: Configure your zoho account to send email from your WordPress site
Text Domain: Zoho Mail
Domain Path: /languages
 */
  /*
    Copyright (c) 2015, ZOHO CORPORATION
    All rights reserved.

    Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

    2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/
 if( !defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}
else {
    define("ZM4WP","ZM4WP_PLUGIN_ACTIVATED");
    define("ZM4WP_VERSION","1.0");
    define("ZM4WP_ZM_PLUGIN_HOME_DIR",plugin_dir_path(__FILE__));
}

function zm_zmplugin_script() {
    wp_enqueue_style( 'zm_zohomail_style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', false, '1.0.0' );
}

add_action( 'admin_enqueue_scripts', 'zm_zmplugin_script');

    function zohomail_activate() {

       }
  register_activation_hook( __FILE__, 'zohomail_activate' );

  function zohomail_deactivate() {
    //--------------Clear the credentials once deactivated-------------------
    delete_option('zmail_integ_client_id');
    delete_option('zmail_integ_client_secret');
    delete_option('zmail_integ_from_email_id');
    delete_option('zmail_integ_domain_name');
    delete_option('zmail_access_token');
    delete_option('zmail_refresh_token');
    delete_option('zmail_account_id');
    delete_option('zmail_integ_from_name');
    delete_option('zmail_auth_code');
  }

  register_deactivation_hook( __FILE__, 'zohomail_deactivate' );

    

function zmail_integ_settings() {
   add_menu_page ( 
    'Welcome to Zoho mail',
    'Zoho Mail',
    'manage_options',
    'zmail-integ-settings',
    'zmail_integ_settings_callback' ,
    'dashicons-email'
    );
   add_submenu_page ( 
    'zmail-integ-settings',
    'Welcome to Zoho mail',
    'Configure Account',
    'manage_options',
    'zmail-integ-settings',
    'zmail_integ_settings_callback'
    );
   add_submenu_page (
            'zmail-integ-settings', 
            'Send Mail - Zoho', 
            'Test Mail', 
            'manage_options', 
            'zmail-send-mail',
            'zmail_send_mail_callback'
         );
   add_submenu_page (
            'zmail-integ-settings',
            'Troubleshoot - Zoho Mail',
            'Troubleshoot',
            'manage_options',
            'zmail-troubleshoot',
            'zmail_troubleshoot_callback'
          );
}


function zmail_troubleshoot_callback() {

  if(isset($_POST['zmail_invalid_secret']) && !empty($_POST)) {
    if(get_option('zmail_auth_code') != null && get_option('zmail_refresh_token') != null) {
      echo '<div class="error"><p><strong>Your configuration setup looks good. Try sending an email from the  <a href="'.admin_url('admin.php').'?page=zmail-send-mail">Test mail</a> page. If the test email sending fails, capture a screenshot of <a href="'.admin_url('admin.php').'?page=zmail-integ-settings">Configure Account</a> page and write to <a href="mailto: support@zohomail.com">our support</a></strong></p></div>';
    }
    if(get_option('zmail_refresh_token') == null) {
      delete_option('zmail_auth_code');
      delete_option('zmail_account_id');
      echo '<div class="error"><p><strong>This error occurs because of mismatch in Client ID, Client Secret and Domain field in the account  <a href="'.admin_url().'?page=zmail-integ-settings">Configure page</a>. Please click on the Authorize button in the configuration page</strong></p></div>';
    } 
    if(get_option('zmail_refresh_token') != null && get_option('zmail_account_id') == null) {
      echo '<div class="error"><p><strong>Please capture a screenshot of <a href="'.admin_url('admin.php').'?page=zmail-integ-settings">Configure Account</a> page and write to <a href="mailto: support@zohomail.com">our support</a></strong></p></div>';
    }
  }
  if(isset($_POST['zmail_invalid_from']) && !empty($_POST)) {
    if(get_option('zmail_account_id') == null) {
      echo '<div class="error"><p><strong>The ’From’ address mismatches with the ‘From’address of the authorized Zoho Mail account.Try to reauthorize in the account  <a href="'.admin_url().'?page=zmail-integ-settings">configuration paget</a> with the '.get_option('zmail_integ_from_email_id').' account</p></strong></div>';
    } else {
      echo '<div class="error"><p><strong>Your configuration setup looks good. Try sending an email from the  <a href="'.admin_url('admin.php').'?page=zmail-send-mail">Test mail</a> page. If the test email sending fails, capture a screenshot of <a href="'.admin_url('admin.php').'?page=zmail-integ-settings">Configure Account</a> page and write to <a href="mailto: support@zohomail.com">our support</a></strong></p></div>';
    }
  }
  ?>
  <head>
    <title>Troubleshoot- Zoho Mail - Wordpress</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  </head>
  <body>
        <div class="page">
        <div class="zmwpContent">
            <h1>Troubleshoot <img src= '<?php echo esc_url(plugins_url('assets/images/zoho.png',__FILE__))?>' title="Zoho" alt="Zoho" width="115"/> Mail </h1>
            <p class="zmwpTagline">Having trouble downloading the products? Try these solutions to resolve common browser, system, and connectivity problems that can interfere with sucessful downloads of products. </p>

            <div class="zmwpContainer">
                <h3>Common Errors</h3>
                <p>Explore the most common errors for your resoruce. Select Trouble shoot to run an automated troubleshooter, follow do-it-yoursself troubleshooting steps, or explore a wide range of troubleshooting tools.</p>
                <div class="zmwpCEBoxWra">
                    <div class="zmwpCEBox">
                        <h5>Invalid Client Secret</h5>
                        <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER["REQUEST_URI"]; ?>"><input type="hidden" id="zmail_invalid_secret" name="zmail_invalid_secret" value="true"/><input type="submit" name="zmail_troubleshoot_invalid_cs" id="zmail_troubleshoot_invalid_cs" class="tbtn" value="Troubleshoot"/></form>
                        <!--<a href="" target="_blank" class="zmwpLink"><b>Troubleshoot</b></a>-->
                    </div>
                    <div class="zmwpCEBox">
                        <h5>Invalid from Address</h5>
                        <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER["REQUEST_URI"]; ?>"><input type="hidden" id="zmail_invalid_from" name="zmail_invalid_from" value="true"/><input type="submit" name="zmail_troubleshoot_invalid_fn" id="zmail_troubleshoot_invalid_fn" class="tbtn" value="Troubleshoot"/></form>
                        <!--<a href="" target="_blank" class="zmwpLink"><b>Troubleshoot</b></a>-->
                    </div>
                </div>

            </div>
            <div class="zmwpContainer">
                <h3>More like this</h3>
                <ul>
                    <li><a href="https://www.zoho.com/mail/help/zohomail-plugin-for-wordpress.html#alink9" target="_blank" class="zmwpLink">Mod Security issue</a></li>
                    <li><a href="https://www.zoho.com/mail/help/zohomail-plugin-for-wordpress.html#alink8" target="_blank" class="zmwpLink">Mail not send in HTML format</a></li>
                    <li><a href="https://www.zoho.com/mail/help/zohomail-plugin-for-wordpress.html#alink5" target="_blank" class="zmwpLink">Reply To address not set in mail</a></li>
                </ul>

            </div>
            <div class="zmwpContainer">
                <h3>Still need help?</h3>
                <p>Visit the <a href="https://wordpress.org/support/plugin/zoho-mail/" target="_blank" class="zmwpLink">ZohoMail Wordpress plugin page</a> to see if other users have found solutions for similar issues or refer our help documentation. If you still need assistance from us, write to our <a href="mailto: support@zohomail.com" class="zmwpLink">Support</a> with details of your issue</p>
            </div>
        </div>
      </div>
    </body>
  <?php

}




 function zmail_integ_settings_callback() {


    if(isset($_GET['granted']) && check_admin_referer( 'redirect_uri','granted')) {
		   $option = get_option('zmail_access_token');
        if(empty($option)) {
          echo '<div class="error"><p><strong><a href="'.admin_url('admin.php').'?page=zmail-troubleshoot">'.esc_html__('Invalid Client Secret').'</a></strong></p></div>'."\n";
        } else {
			$accId = get_option('zmail_account_id');
        if(empty($accId)){
         echo '<div class="error"><p><strong><a href="'.admin_url('admin.php').'?page=zmail-troubleshoot">'.esc_html__('Invalid From Address.').'</strong></p></div>'."\n";
    } else {
        echo '<div class="updated"><p><strong>'.esc_html__('Access Granted.').'</strong></p></div>'."\n";
    }
  }
}
    if(isset($_GET['code'])) {
        ?>
       <head> <meta http-equiv="refresh" content="0; url=<?php echo wp_nonce_url(esc_url(admin_url().'admin.php?page=zmail-integ-settings&action=zmail_integ_oauth_grant'),'redirect_uri','granted');?>"/> </head>
<?php
       if(empty(get_option('zmail_auth_code'))) {
          update_option('zmail_auth_code',$_GET['code'], false);
          $state = wp_create_nonce('redirect_url');
          $code = sanitize_text_field($_GET['code']);
          $url = "https://accounts.zoho.".get_option('zmail_integ_domain_name')."/oauth/v2/token?code=".$code."&client_id=".get_option('zmail_integ_client_id')."&client_secret=".get_option('zmail_integ_client_secret')."&redirect_uri=".admin_url()."admin.php?page=zmail-integ-settings&scope=VirtualOffice.messages.CREATE,VirtualOffice.accounts.READ&grant_type=authorization_code&state=".$state;
          $bodyAccessTokandRefresh = wp_remote_retrieve_body(wp_remote_post( $url));
          $respoAtJs = json_decode($bodyAccessTokandRefresh);
          update_option('zmail_access_token', $respoAtJs->access_token, false);
          update_option('zmail_refresh_token', $respoAtJs->refresh_token, false);
          $accId = get_option('zmail_account_id');
          if(!empty($accId))
               delete_option('zmail_account_id');   
          $urlAccounts = 'https://mail.zoho.'.get_option('zmail_integ_domain_name').'/api/accounts';
          $headr = array();
          $accesstoken = get_option('zmail_access_token');
          $headr[] = 'Authorization: Zoho-oauthtoken '.$accesstoken;
          $args = array(
               'headers' => array(
                     'Authorization' => 'Zoho-oauthtoken '.$accesstoken
                )
              );
          $bodyAccounts = wp_remote_retrieve_body(wp_remote_get( $urlAccounts, $args));
          $jsonbodyAccounts = json_decode($bodyAccounts);
          for($i=0;$i<count($jsonbodyAccounts->data);$i++) 
          {
              for($j=0;$j<count($jsonbodyAccounts->data[$i]->sendMailDetails);$j++) {
                  if(strcmp($jsonbodyAccounts->data[$i]->sendMailDetails[$j]->fromAddress,get_option('zmail_integ_from_email_id')) == 0)
                      {
                          update_option('zmail_account_id', $jsonbodyAccounts->data[0]->accountId, false);
                      }
              } 
          }
       } else {
	       if(!empty(get_option('zmail_access_token'))) {
       		        $urlAccounts = 'https://mail.zoho.'.get_option('zmail_integ_domain_name').'/api/accounts';
          	        $headr = array();
          		$accesstoken = get_option('zmail_access_token');
         		 $headr[] = 'Authorization: Zoho-oauthtoken '.$accesstoken;
         		 $args = array(
              			 'headers' => array(
                    		 'Authorization' => 'Zoho-oauthtoken '.$accesstoken
               		 )
              		);
          		$bodyAccounts = wp_remote_retrieve_body(wp_remote_get( $urlAccounts, $args));
          		$jsonbodyAccounts = json_decode($bodyAccounts);
              foreach($jsonbodyAccounts->data as $element)
          		{
                    foreach($element->sendMailDetails as $smd) {
                  			if(strcmp($smd->fromAddress,get_option('zmail_integ_from_email_id')) == 0)
                      				{
                          				update_option('zmail_account_id', $jsonbodyAccounts->data[0]->accountId, false);
                      				}
              			}
          		}
      			}
	    //call to refresh token generation is already made
    }
    }
  if(current_user_can("administrator") || is_super_admin()) {
    if (isset($_POST['zmail_integ_submit']) && !empty($_POST)) {
      $nonce = sanitize_text_field($_REQUEST['_wpnonce']);
      if (!wp_verify_nonce($nonce, 'zmail_integ_settings_nonce')) {
                  echo '<div class="error"><p><strong>'.esc_html__('Reload the page again').'</strong></p></div>'."\n";
                } 
                else {
        $zmail_integ_client_id = sanitize_text_field($_POST['zmail_integ_client_id']);
        $zmail_integ_client_secret = sanitize_text_field($_POST['zmail_integ_client_secret']);
        $zmail_integ_from_email_id = sanitize_email($_POST['zmail_integ_from_email_id']);
        $zmail_integ_domain_name = sanitize_text_field($_POST['zmail_integ_domain_name']);
        $zmail_integ_from_name = sanitize_text_field($_POST['zmail_integ_from_name']);
	$zmail_content_type = sanitize_text_field($_POST['zmail_content_type']);
        update_option('zmail_integ_client_id',$zmail_integ_client_id, false);
        update_option('zmail_integ_client_secret',$zmail_integ_client_secret, false);
        update_option('zmail_integ_from_email_id',$zmail_integ_from_email_id, false);
        update_option('zmail_integ_from_name',stripslashes($zmail_integ_from_name), false);
        update_option('zmail_integ_domain_name',$zmail_integ_domain_name, false);
	update_option('zmail_content_type',$zmail_content_type, false);
         echo '<div class="updated"><p><strong>'.esc_html__('Settings saved.').'</strong></p></div>'."\n";
         ?>
         <head> <meta http-equiv="refresh" content="0; url=<?php $completeRedirectUrl=esc_url(admin_url().'admin.php?page=zmail-integ-settings'); $state = wp_create_nonce( 'redirect_url'); $test=esc_url("https://accounts.zoho.".get_option('zmail_integ_domain_name')."/oauth/v2/auth?response_type=code&client_id=".get_option('zmail_integ_client_id')."&scope=VirtualOffice.messages.CREATE,VirtualOffice.accounts.READ&redirect_uri=".$completeRedirectUrl."&prompt=consent&access_type=offline&state=".$state); echo $test;?>"/> </head>
         <?php

    }
    }
    $plugindata = get_plugin_data(__FILE__,false,false);
  if($plugindata['Version'] == "1.3.2") {
    delete_option('zmail_auth_code');
  }
}
    ?>
    <head>
    <meta charset="UTF-8">
    <title>Zoho Mail</title>
    </head>
    <body>
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <?php wp_nonce_field('zmail_integ_settings_nonce'); ?>
    <div class="page"><div class="page__content">
            <div class="page__header">
                <h1>Welcome to <img src=<?php echo esc_url(plugins_url('assets/images/zoho.png',__FILE__))?> title='Zoho' alt="Zoho" width="115" /> Mail!</h1>
                <p>Please visit the <a class="zm_a" href=<?php echo esc_url("https://accounts.zoho.com/developerconsole")?> target="_blank">Zoho OAuth Creation</a> documentation page for usage instructions.</p>
            </div>
            <div class="form__row">
                <label class="form--label">Where is your account hosted?</label>
                <div class="form__domain">
                  <span class="form__domain-info">mail.zoho</span>

                    <select class="form__domain-value" name="zmail_integ_domain_name">
                  			<option value="com" <?php if(get_option('zmail_integ_domain_name') == "com") {?> selected="true"<?php } ?>>.com</option>
                  			<option value="eu" <?php if(get_option('zmail_integ_domain_name') == "eu") {?> selected="true"<?php } ?>>.eu</option>
                  			<option value="in" <?php if(get_option('zmail_integ_domain_name') == "in") {?> selected="true"<?php }?>>.in</option>
                  			<option value="com.cn" <?php if(get_option('zmail_integ_domain_name') == "com.cn") {?>selected="true"<?php }?>>.com.cn</option>
                  			<option value="com.au" <?php if(get_option('zmail_integ_domain_name') == "com.au"){?>selected="true"<?php }?>>.com.au</option>
                        <option value="jp" <?php if(get_option('zmail_integ_domain_name') == "jp"){?>selected="true"<?php }?>>.jp</option>
                      </select>
                    
                  </div>
                  <div>
                      <i class="form__row-info">The name of the region the account is configured</i>
                  </div>
                </div>
                <div class="form__row">
                    <label class="form--label">Client Id</label>
                    <input type="text" value="<?php echo get_option('zmail_integ_client_id') ?>" name="zmail_integ_client_id" class="form--input" id="zmail_integ_client_id" required/> <i class="form__row-info">Created in the developer console</i> </div>
                <div class="form__row">
                    <label class="form--label">Client Secret</label>
                    <input type="text" value="<?php echo get_option('zmail_integ_client_secret') ?>" name="zmail_integ_client_secret" class="form--input" id="zmail_integ_client_secret"  required/> <i class="form__row-info">Created in the developer console</i> </div>
                <div class="form__row">
                    <label class="form--label">Authorization Redirect URI</label>
                    <input type="text" id="zmail_integ_authorization_uri" readonly="readonly" name="zmail_integ_authorization_uri" class="form--input" value="<?php echo esc_url(admin_url().'admin.php?page=zmail-integ-settings'); ?>" class="regular-text" readonly="readonly" required/> <i class="form__row-info">Copy this URL into Redirect URI field of your Client Id creation</i> </div>
                <div class="form__row">
                    <label class="form--label">From Email Address</label>
                    <input type="text" name="zmail_integ_from_email_id" value="<?php echo get_option('zmail_integ_from_email_id') ?>" class="form--input" id="zmail_integ_from_email_id" required/> <i class="form__row-info">The email address which will be used as the from Address when sending an email</i> </div>
                 <div class="form__row">
                    <label class="form--label">From Name</label>
                    <input type="text" name="zmail_integ_from_name" value="<?php echo get_option('zmail_integ_from_name') ?>" class="form--input" id="zmail_integ_from_name" required/> <i class="form__row-info">The name which will be used as the from name when sending an email</i> </div>
		<div class="form__row">
                    <label class="form--label">Mail Format</label>
		                  <select class="form--input form--input--select" name="zmail_content_type">
                                <option value="plaintext" <?php if(get_option('zmail_content_type') == "plaintext") {?> selected="true"<?php } ?>>Plaintext</option>
                                <option value="html" <?php if(get_option('zmail_content_type') == "html") {?> selected="true"<?php } ?>>HTML</option>
                    </select> <br><i class="form__row-info">The preferred email format for the body of your email.</i> </div>
                <div class="form__row form__row-btn">
                  <input type="submit" name="zmail_integ_submit" id="zmail_integ_submit" class="btn" value="Authorize"/> 
                  </div>
                  </div>
                </div>
               </div>
             </form>
           </body>
        <?php
        

 }
add_action('admin_menu','zmail_integ_settings');





function zmail_send_mail_callback() {

                        
                        

    $option = get_option('zmail_account_id'); 
    if(!empty($option)){
         
        if(is_admin() && current_user_can('administrator')) { 
        if(isset($_POST['zmail_integ_send_mail_submit']) && !empty($_POST)){
                $nonce = sanitize_text_field($_REQUEST['_wpnonce']);
                if (!wp_verify_nonce($nonce, 'zmail_send_mail_nonce')) {
                  echo '<div class="error"><p><strong>'.esc_html__('Reload the page again').'</strong></p></div>'."\n";
                } else {
                if(empty($option)){          
                    echo '<div class="error"><p><strong>'.esc_html__('Account not Configured').'</strong></p></div>'."\n";
                }
                $toAddressTest =sanitize_email($_POST['zmail_integ_to_address']);
                $subjectTest = sanitize_text_field($_POST['zmail_integ_subject']);
                $contentTest = sanitize_text_field($_POST['zmail_integ_content']);
                if(wp_mail($toAddressTest,$subjectTest,$contentTest,null, null)) {
                    echo '<div class="updated"><p><strong>'.esc_html__('Mail Sent Successfully').'</strong></p></div>'."\n";
                  } else {
                    echo '<div class="error"><p><strong>'.esc_html__('Mail Sending Failed').'</strong></p></div>'."\n";
                  }
        
                }
              }
              }
                            ?>
                         <head>
                         <meta charset="UTF-8">
                         <title>Zoho Mail</title>
                         </head>

                         <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
                         <?php wp_nonce_field('zmail_send_mail_nonce'); ?>
                         <body>
                          <div class="page"><div class="page__content">
                              <div class="page__header">
                              <h1>Send Mail <span class="ico-send"></span></h1>
                              </div>
                              <div class="form">
                                 <div class="form__row">
                                    <label class="form--label">To</label>
                                    <input type="text" class="form--input" name="zmail_integ_to_address" required = "required"/> </div>
                                    <div class="form__row">
                                    <label class="form--label">Subject</label>
                                    <input type="text" class="form--input" name="zmail_integ_subject" required = "required"/> </div>
                                <div class="form__row">
                               <label class="form--label">Content</label>
                               <input type="text" class="form--input" name="zmail_integ_content"/> </div>
                            <div class="form__row form__row-btn"> <input type="submit" class = "btn" name="zmail_integ_send_mail_submit" id="zmail_integ_send_mail_submit" value="<?php _e('Send Mail');?>">
                              
                            </div>
                          </div>
                         </div>
                        </div>
                        </body>
                        </form>
                      <?php
         
                           }
                      else {
                         echo '<div class="error"><p><strong>'.__('Configure Your Account.').'</strong></p></div>'."\n";
                         }
    
                    }


if(!function_exists('wp_mail')) {
  function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) { 
    
    $atts = apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) );

    if ( isset( $atts['to'] ) ) {
        $to = $atts['to'];
    }
    if ( !is_array( $to ) ) {
        $to = explode( ',', $to );
    }
    if ( isset( $atts['subject'] ) ) {
        $subject = $atts['subject'];
    }
    if ( isset( $atts['message'] ) ) {
        $message = $atts['message'];
    }
    if ( isset( $atts['headers'] ) ) {
        $headers = $atts['headers'];
    }
    if ( isset( $atts['attachments'] ) ) {
        $attachments = $atts['attachments'];
    }
    
    if ( ! is_array( $attachments ) ) {
        $attach[] = str_replace( "\r\n", "\n", $attachments );
        $attachments = implode( "\n", $attach );
    }

  $content_type = null;
    // Headers
    $cc = $bcc = $reply_to = array();
    if ( empty( $headers ) ) {
        $headers = array();
      } else {
         if ( !is_array( $headers ) ) {
            // Explode the headers out, so this function can take both
            // string headers and an array of headers.
            $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
        } else {
            $tempheaders = $headers;
        }
        $headers = array();
        // If it's actually got contents
        if ( !empty( $tempheaders ) ) {
            // Iterate through the raw headers
            foreach ( (array) $tempheaders as $header ) {
                if ( strpos($header, ':') === false ) {
                    if ( false !== stripos( $header, 'boundary=' ) ) {
                        $parts = preg_split('/boundary=/i', trim( $header ) );
                        $boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
                    }
                    continue;
                }
                // Explode them out
                list( $name, $content ) = explode( ':', trim( $header ), 2 );

                // Cleanup crew
                $name    = trim( $name    );
                $content = trim( $content );
		            $content_type = null;
                switch ( strtolower( $name ) ) {
                    case 'content-type':
                        if ( strpos( $content, ';' ) !== false ) {
                            list( $type, $charset_content ) = explode( ';', $content );
                            $content_type = trim( $type );
                            if ( false !== stripos( $charset_content, 'charset=' ) ) {
                                $charset = trim( str_replace( array( 'charset=', '"' ), '', $charset_content ) );
                            } elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
                                $boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset_content ) );
                                $charset = '';
                            }

                        // Avoid setting an empty $content_type.
                          } elseif ( '' !== trim( $content ) ) {
                                  $content_type = trim( $content );
                            }
                           break;
                        case 'cc':
                            $cc = array_merge( (array) $cc, explode( ',', $content ) );
                            break;
                        case 'bcc':
                            $bcc = array_merge( (array) $bcc, explode( ',', $content ) );
                            break;
                        case 'reply-to':
                            $reply_to = array_merge( (array) $reply_to, explode( ',', $content ) );
                            break;
                        default:
                            $headers[trim( $name )] = trim( $content );
                            break;
                        }
                     }
                  }
     		 }
		            $content_type = apply_filters( 'wp_mail_content_type', $content_type );    
                $data = array();
                if (!empty($from_name)) {
                     $data['fromAddress'] =$from_name.'<'.get_option('zmail_integ_from_email_id').'>';
                } else {
                  $data['fromAddress'] = get_option('zmail_integ_from_name').'<'.get_option('zmail_integ_from_email_id').'>';
                }
		$zmbcc = '';
                if (sizeof($bcc) > 0) {
                  $zmbcc = implode(',',$bcc);
                }
                if ($zmbcc != '') {
                  $data['bccAddress'] = $zmbcc;
		}
    $zmcc = '';
                if (sizeof($cc) > 0) {
                  $zmcc = implode(',',$cc);
                }
                if ($zmcc != '') {
                  $data['ccAddress'] = $zmcc;
    }
		if(!empty($reply_to)) {
			if(get_option('zmail_integ_from_email_id') == $to[0] && sizeof($to) == 1) {
                       $start = stripos($reply_to[0],'<');
                       $length = strlen($reply_to[0])-1-$start;
                       if ($start > 1) {
                          $shortString = substr($reply_to[0], $start+1, $length-1);
                       } else {
                        $shortString = $reply_to[0];
                       }
		                   $data['replyTo'] = $shortString;
                      }
		 }
		if (!base64_decode(get_option('zmail_refresh_token'),true)) {
         		 update_option('zmail_refresh_token', base64_encode(get_option('zmail_refresh_token')), false);
		}
		if(!empty(get_option('zmail_auth_code'))) {
                delete_option('zmail_auth_code');
   		 }
                $data['subject'] = $subject;
                $data['content'] = $message;
                $toAddresses = implode(',' ,$to);
                $data['toAddress'] = $toAddresses;
	       if(empty(get_option('zmail_integ_timestamp')) || time() - get_option('zmail_integ_timestamp') > 3000) {
                    update_option('zmail_integ_timestamp',time(), false);
                    $urlUsingRefreshToken ='https://accounts.zoho.'.get_option('zmail_integ_domain_name').'/oauth/v2/token?refresh_token='.base64_decode(get_option('zmail_refresh_token')).'&grant_type=refresh_token&client_id='.get_option('zmail_integ_client_id').'&client_secret='.get_option('zmail_integ_client_secret').'&redirect_uri='.admin_url().'admin.php?page=zmail-integ-settings&scope=VirtualOffice.messages.CREATE,VirtualOffice.accounts.READ';
                    $bodyAccessTok = wp_remote_retrieve_body(wp_remote_post( $urlUsingRefreshToken));
                    $respoJs = json_decode($bodyAccessTok);
                    update_option('zmail_access_token',$respoJs->access_token, false);
                }
		if(!empty($attachments)){
		    $attachmentJSONArr = array();
                    $data['attachments'] = $attachments;
                    $headers1 = array(
                         'Authorization' => 'Zoho-oauthtoken '.get_option('zmail_access_token'),
                         'Content-Type' => 'application/octet-stream'
                       );
                    $count = 0;
                    $flag = 'true';
                    foreach($attachments as $attfile) {
                      $fileName = basename($attfile);
                      $attachurl = 'https://mail.zoho.'.get_option('zmail_integ_domain_name').'/api/accounts/'.get_option('zmail_account_id').'/messages/attachments'.'?fileName='.$fileName;
                      $args = array(
                         'body' => file_get_contents($attfile),
                         'headers' => $headers1,
                         'method' => 'POST'
                      );
                      $resultatt = wp_remote_post($attachurl, $args);
                      $responseSending = wp_remote_retrieve_body($resultatt);
                      $http_code = wp_remote_retrieve_response_code($resultatt);
                      $attachmentupload = array();
                      if($http_code == '200') {
                         $responseattachjson = json_decode($responseSending);
                         $attachmentupload['storeName'] = $responseattachjson->data->storeName;
                         $attachmentupload['attachmentPath'] = $responseattachjson->data->attachmentPath;
                         $attachmentupload['attachmentName'] = $responseattachjson->data->attachmentName;
                         $attachmentJSONArr[$count] = $attachmentupload;
                         $count = $count + 1;
                      } else {
                        $flag = 'false';
                      }
                    }
                    if($flag == 'true') {
                      $data['attachments'] = $attachmentJSONArr;
                    }
                }   
                if( $content_type == 'text/html' || get_option('zmail_content_type') == 'html') {
                  $data['mailFormat'] = 'html';
                } else {
                  $data['mailFormat'] = 'plaintext';
                }   
                $headers1 = array(
                         'Authorization' => 'Zoho-oauthtoken '.get_option('zmail_access_token'),
                         'Content-Type' => 'application/json'
                       );
                
                $data_string = json_encode($data);
                $args = array(
                         'body' => $data_string,
                         'headers' => $headers1,
                         'method' => 'POST'
		 );
		$urlToSend = 'https://mail.zoho.'.get_option('zmail_integ_domain_name').'/api/accounts/'.get_option('zmail_account_id').'/messages';
                $responseSending = wp_remote_post( $urlToSend, $args );
                $http_code = wp_remote_retrieve_response_code($responseSending);
                if($http_code == '200') {
                  return true;
                }
                return false;

  }
  
}







