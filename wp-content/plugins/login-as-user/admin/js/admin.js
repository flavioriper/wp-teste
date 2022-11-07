/* ======================================================
 # Login as User for WordPress - v1.4.4 (free version)
 # -------------------------------------------------------
 # For WordPress
 # Author: Web357
 # Copyright @ 2014-2022 Web357. All rights reserved.
 # License: GNU/GPLv3, http://www.gnu.org/licenses/gpl-3.0.html
 # Website: https:/www.web357.com
 # Demo: https://demo.web357.com/wordpress/login-as-user/wp-admin/
 # Support: support@web357.com
 # Last modified: Tuesday 14 June 2022, 06:08:05 PM
 ========================================================= */
 
jQuery(function ($) {
	'use strict';

    $('.w357-login-as-user-btn').bind('contextmenu', function(e) {
		alert('The right click is disabled. Please, just click on the button.');
		return false;
    }); 

    // Do not show the button (activate License key) on typing
    $('#license_key').on('input', function() {
        $("#apikey-container").html('<p style="color: red; margin-top: 15px;">Please, save the plugin settings.</p>');
    });

	// Restore to Defaults
	$(document).on("click", ".web357-activate-api-key-btn", function(e){
        e.preventDefault();

        var nonce = $(this).data('nonce');
        var key = $(this).data('key');
        var domain = $(this).data('domain');

        $.ajax({
            type : "POST",
            dataType : "json",
            cache: false,
            url : loginasuserAjax.loginasuser_ajaxurl,
            data : {action: "web357_license_key_validation", key : key, domain: domain, nonce: nonce},

            success: function (response) {
                console.log( "RESPONSE TYPE: " + response.type );
                if(response.type == "success") {
                    $('.web357_apikey_activation_html').html(response.message);
                }
                else {
                    alert("There is a problem. Your key could not be validated. Please, contact us at support@web357.com")
                }
			},
			error: function(response) {

                $('.web357_apikey_activation_html').html(response.message);
            },
            beforeSend: function () {

                $(".web357-loading-gif").show();
                
               $('#w357-activated-successfully-msg').hide();
               $('#w357-activated-successfully-msg-ajax').hide();

			},
			complete: function () {

				$(".web357-loading-gif").hide();
                $('#w357-activated-successfully-msg').css('display', 'none');
                $('#w357-activated-successfully-msg-ajax').css('display', 'block');

            }
        })          
    });

});

/* Compatible with User Insights Plugin */
if(typeof angular !== 'undefined') // angular is loaded successfully
{
    angular.module('usinApp').run(['$templateCache', function($templateCache) {
        'use strict';
    
        $templateCache.put('views/user-list/profile-editable-field.html',
        "<div ng-class=\"['usin-editable-field', {'usin-field-editing': editing}]\">\n" +
        "	<span class=\"field-name\">{{field.name}}: </span>\n" +
        "	<span class=\"field-value\" ng-hide=\"editing\" ng-bind-html=\"user[field.id]\" >{{user[field.id] | optionKeyToVal:field.options || '-'}}</span>\n" +
    //   "	<span class=\"field-value\" ng-hide=\"editing\">{{user[field.id] | optionKeyToVal:field.options || '-'}}</span>\n" + // original
        "	\n" +
        "	<span ng-if=\"canUpdateUsers\">\n" +
        "		<input type=\"text\" ng-if=\"field.type=='text' || field.type=='date'\" ng-model=\"user[field.id]\" ng-show=\"editing\" ng-keyup=\"$event.keyCode==13 && updateField()\">\n" +
        "		<input type=\"number\" usin-string-to-number ng-if=\"field.type=='number'\" ng-model=\"user[field.id]\" ng-show=\"editing\" ng-keyup=\"$event.keyCode==13 && updateField()\">\n" +
        "		<span ng-if=\"field.type=='select'\" ng-show=\"editing\" class=\"usin-editable-select-wrap\">\n" +
        "			<div class=\"usin-profile-select-wrap\">\n" +
        "				<usin-select-field ng-model=\"user[field.id]\" options=\"field.options\" ng-keyup=\"$event.keyCode==13 && updateField()\"></usin-select-field>\n" +
        "				<div class=\"usin-btn-close usin-icon-close\" ng-click=\"clearSelection()\">\n" +
        "					<md-tooltip md-direction=\"top\">{{strings.clearSelection}}</md-tooltip>\n" +
        "				</div>\n" +
        "			</div>\n" +
        "		</span>\n" +
        "		\n" +
        "		<div class=\"usin-btn-edit usin-icon-edit alignright\" ng-click=\"toggleEdit()\" ng-show=\"!editing && !settings.editing\"></div>\n" +
        "		<div ng-class=\"['usin-btn-apply', 'alignright', {'usin-icon-apply':!loading, 'usin-icon-simple-loading':loading}]\" ng-click=\"updateField()\" ng-show=\"editing\">\n" +
        "			<md-tooltip md-direction=\"top\">{{strings.saveChanges}}</md-tooltip>\n" +
        "		</div>\n" +
        "		<div class=\"usin-error\" ng-show=\"errorMsg\">{{errorMsg}}</div>\n" +
        "	</span>\n" +
        "	<div class=\"clear\"></div>\n" +
        "</div>"
        );
    
    }]);
}