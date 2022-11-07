!function(e){var n={};function t(r){if(n[r])return n[r].exports;var a=n[r]={i:r,l:!1,exports:{}};return e[r].call(a.exports,a,a.exports,t),a.l=!0,a.exports}t.m=e,t.c=n,t.d=function(e,n,r){t.o(e,n)||Object.defineProperty(e,n,{enumerable:!0,get:r})},t.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},t.t=function(e,n){if(1&n&&(e=t(e)),8&n)return e;if(4&n&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(t.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&n&&"string"!=typeof e)for(var a in e)t.d(r,a,function(n){return e[n]}.bind(null,a));return r},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,n){return Object.prototype.hasOwnProperty.call(e,n)},t.p="/",t(t.s=257)}({257:function(e,n,t){e.exports=t(258)},258:function(e,n){function t(e,n){var t="undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(!t){if(Array.isArray(e)||(t=function(e,n){if(!e)return;if("string"==typeof e)return r(e,n);var t=Object.prototype.toString.call(e).slice(8,-1);"Object"===t&&e.constructor&&(t=e.constructor.name);if("Map"===t||"Set"===t)return Array.from(e);if("Arguments"===t||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t))return r(e,n)}(e))||n&&e&&"number"==typeof e.length){t&&(e=t);var a=0,i=function(){};return{s:i,n:function(){return a>=e.length?{done:!0}:{done:!1,value:e[a++]}},e:function(e){throw e},f:i}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var d,o=!0,s=!1;return{s:function(){t=t.call(e)},n:function(){var e=t.next();return o=e.done,e},e:function(e){s=!0,d=e},f:function(){try{o||null==t.return||t.return()}finally{if(s)throw d}}}}function r(e,n){(null==n||n>e.length)&&(n=e.length);for(var t=0,r=new Array(n);t<n;t++)r[t]=e[t];return r}!function(e){var n=!1,r=e(".defender-biometric-wrap"),a=r.find(".register-authenticator-box"),i=r.find("button"),d=r.find("#authenticator-identifier"),o=r.find("input[name='authenticator-type']"),s=r.find(".process-desc"),l=r.find(".process-auth-desc"),c=r.find(".records"),f=r.find(".no-record");function u(e,n){var t=arguments.length>2&&void 0!==arguments[2]&&arguments[2],r=arguments.length>3&&void 0!==arguments[3]&&arguments[3],a="";!0===t?a='<span class="loading"></span>':"success"===e?a='<span class="dashicons dashicons-yes-alt"></span>':"error"===e&&(a='<span class="dashicons dashicons-warning"></span>');var i=r?'<button type="button" class="notice-dismiss"></button>':"";return'<div class="notice notice-'+e+'"><p>'+a+'<span class="message">'+n+"</span>"+i+"</p></div>"}function p(e){return'<tr data-key="'+e.key+'" data-label="'+e.label+'"><td><span class="wpdef-field-label">'+e.label+'</span><input type="text" class="wpdef-field-rename-identifier regular-text" value="'+e.label+'" style="display:none;"/><div class="field-error" style="display:none;">'+wp.i18n.__("Add an authenticator identifier.","wpdef")+'</div><button type="button" class="toggle-row-content"></button></td><td><span class="col-name">'+wp.i18n.__("Type","wpdef")+"</span>"+("platform"===e.auth_type?wp.i18n.__("Platform","wpdef"):wp.i18n.__("Roaming","wpdef"))+'</td><td><span class="col-name">'+wp.i18n.__("Date Registered","wpdef")+"</span>"+e.added+'</td><td><span class="rename-control"><span class="wpdef-rename-btn">'+wp.i18n.__("Rename","wpdef")+'</span><span class="button button-primary wpdef-rename-update-btn" style="display:none;"><span class="label-btn">'+wp.i18n.__("Save","wpdef")+'</span><span class="loading" style="display:none;"></span></span><span class="wpdef-tbl-sep sep-rename" style="display:none;">|</span><span class="wpdef-rename-cancel-btn" style="display:none;">'+wp.i18n.__("Cancel","wpdef")+'</span></span><span class="wpdef-tbl-sep sep-delete">|</span><span class="wpdef-delete-btn">'+wp.i18n.__("Delete","wpdef")+"</span></td></tr>"}function b(){n=!0,i.attr("disabled","disabled"),d.attr("disabled","disabled"),o.attr("disabled","disabled")}function h(){n=!1,i.removeAttr("disabled"),d.removeAttr("disabled").val(""),o.removeAttr("disabled","disabled")}function w(){r.find("#defender-biometric-tbl .wpdef-rename-update-btn").hide(),r.find("#defender-biometric-tbl .wpdef-rename-cancel-btn").hide(),r.find(".sep-rename").hide(),r.find("#defender-biometric-tbl .loading").hide(),r.find("#defender-biometric-tbl .wpdef-field-rename-identifier").hide().removeClass("required"),r.find("#defender-biometric-tbl .field-error").hide(),r.find("#defender-biometric-tbl .wpdef-rename-btn").show(),r.find("#defender-biometric-tbl .wpdef-field-label").show(),r.find(".sep-delete").show(),r.find(".wpdef-delete-btn").show()}!function(e){if(Array.isArray(e)){var n="";e.forEach((function(e,t){n+=p(e)})),""!==n?(f.hide(),c.show().html(n)):(f.show(),c.hide())}}(webauthn.registered_auths),e("body").on("click",".defender-biometric-wrap .notice-dismiss",(function(n){n.preventDefault(),e(this).closest(".notice").remove()})),e("body").on("click",".defender-biometric-wrap .wpdef-new-btn",(function(e){e.preventDefault(),a.show(),w()})),e("body").on("click",".register-authenticator-box #wpdef-register-authenticator-close-btn",(function(e){e.preventDefault(),!0!==n&&(a.hide(),a.find("input.required").removeClass("required"),a.find(".field-error").hide(),s.html(""))})),e("body").on("click",".register-authenticator-box #wpdef-register-authenticator-btn",(function(t){if(t.preventDefault(),!0!==n){var r=o.filter(":checked").val(),i=e.trim(d.val());if(a.find("input.required").removeClass("required"),a.find(".field-error").hide(),!r||!i)return r||(o.addClass("required"),o.closest("tr").find(".field-error").show()),void(i||(d.addClass("required"),d.siblings(".field-error").show()));b(),s.html(u("info",webauthn.i18n.registration_start,!0)),e.ajax({url:webauthn.admin_url,type:"GET",data:{action:"defender_webauthn_create_challenge",type:r,_def_nonce:webauthn.nonce},success:function(n){if(!1===n.success||void 0===n.data.challenge)return s.html(u("error",webauthn.i18n.authenticator_reg_failed,!1,!0)),void h();var t=n.data,a=Uint8Array.from(window.atob(wpdefBase64Url2Base64(t.challenge)),(function(e){return e.charCodeAt(0)})),d=Uint8Array.from(window.atob(wpdefBase64Url2Base64(t.user.id)),(function(e){return e.charCodeAt(0)})),o={challenge:a,rp:{id:t.rp.id,name:t.rp.name},user:{id:d,name:t.user.name,displayName:t.user.displayName},pubKeyCredParams:t.pubKeyCredParams,authenticatorSelection:t.authenticatorSelection,timeout:t.timeout};t.excludeCredentials&&(o.excludeCredentials=t.excludeCredentials.map((function(e){return e.id=Uint8Array.from(window.atob(wpdefBase64Url2Base64(e.id)),(function(e){return e.charCodeAt(0)})),e})));var l=t.clientID;delete t.clientID,navigator.credentials.create({publicKey:o}).then((function(e){return{id:e.id,type:e.type,rawId:wpdefArrayToBase64String(new Uint8Array(e.rawId)),response:{clientDataJSON:wpdefArrayToBase64String(new Uint8Array(e.response.clientDataJSON)),attestationObject:wpdefArrayToBase64String(new Uint8Array(e.response.attestationObject))}}})).then(JSON.stringify).then((function(n){e.ajax({url:webauthn.admin_url,type:"POST",data:{action:"defender_webauthn_verify_challenge",data:window.btoa(n),name:i,usernameless:"false",client_id:l,type:r,_def_nonce:webauthn.nonce},success:function(e){!0===e.success?(f.hide(),c.show().append(p(e.data)),s.html(u("success",webauthn.i18n.authenticator_reg_success,!1,!0))):s.html(u("error",webauthn.i18n.authenticator_reg_failed,!1,!0)),h()},error:function(){s.html(u("error",webauthn.i18n.authenticator_reg_failed,!1,!0)),h()}})})).catch((function(e){var n=webauthn.i18n.authenticator_reg_failed;void 0!==e.message&&-1!==e.message.indexOf("already registered")&&(n=webauthn.i18n.multiple_reg_attempt),s.html(u("error",n,!1,!0)),h()}))},error:function(){s.html(u("error",webauthn.i18n.authenticator_reg_failed,!1,!0)),h()}})}})),e("body").on("click","#defender-biometric-tbl .wpdef-delete-btn",(function(t){if(!0!==n){var r=e(t.currentTarget),a=r.closest("tr"),i=r.html(),d=a.attr("data-key");!0===confirm(webauthn.i18n.remove_auth)&&(b(),r.html('<span class="loading"></span>'),e.ajax({url:webauthn.admin_url,type:"POST",data:{action:"defender_webauthn_remove_authenticator",key:d,_def_nonce:webauthn.nonce},success:function(e){!0===e.success?(a.remove(),0===c.find("tr").length&&(c.hide(),f.show())):(r.html(i),alert(e.data)),h()}}))}})),e("body").on("click","#defender-biometric-tbl .wpdef-rename-btn",(function(t){if(!0!==n){var r=e(t.currentTarget),a=r.closest("tr"),i=a.attr("data-label");w(),r.hide().siblings(".wpdef-rename-update-btn,.wpdef-rename-cancel-btn").show(),a.find(".wpdef-field-label").hide(),a.find(".sep-delete").hide(),a.find(".wpdef-delete-btn").hide(),a.find(".wpdef-field-rename-identifier").val(i).show().focus(),a.find(".sep-rename").show()}})),e("body").on("click","#defender-biometric-tbl .wpdef-rename-cancel-btn",(function(t){if(!0!==n){var r=e(t.currentTarget),a=r.closest("tr"),i=a.attr("data-label");r.hide().siblings(".wpdef-rename-update-btn").hide(),a.find(".sep-rename").hide(),a.find(".wpdef-field-rename-identifier").hide().val(i),a.find(".field-error").hide(),r.siblings(".wpdef-rename-btn").show(),a.find(".wpdef-field-label").show(),a.find(".sep-delete").show(),a.find(".wpdef-delete-btn").show()}})),e("body").on("click","#defender-biometric-tbl .wpdef-rename-update-btn",(function(t){if(!0!==n){var r=e(t.currentTarget),a=r.closest("tr"),i=a.find(".wpdef-field-rename-identifier"),d=a.attr("data-key"),o=e.trim(i.val());if(!o)return i.addClass("required"),void i.siblings(".field-error").show();i.removeClass("required"),i.siblings(".field-error").hide(),b(),r.find(".label-btn").html(wp.i18n.__("Saving","wpdef")),a.find(".loading").show(),e.ajax({url:webauthn.admin_url,type:"POST",data:{action:"defender_webauthn_rename_authenticator",key:d,label:o,_def_nonce:webauthn.nonce},success:function(e){!0===e.success?(a.attr("data-label",o),i.siblings(".wpdef-field-label").html(o)):alert(e.data),h(),r.hide().find(".label-btn").html(wp.i18n.__("Save","wpdef")),r.find(".loading").hide(),r.siblings(".wpdef-rename-cancel-btn").hide(),r.siblings(".sep-rename").hide(),i.hide(),i.siblings(".wpdef-field-label").show(),r.siblings(".wpdef-rename-btn").show(),a.find(".wpdef-delete-btn").show(),a.find(".sep-delete").show()}})}})),e("body").on("click",".defender-biometric-wrap .wpdef-verify-btn",(function(r){r.preventDefault(),!0!==n&&(b(),l.html(u("info",webauthn.i18n.authentication_start,!0)),e.ajax({url:webauthn.admin_url,type:"POST",data:{action:"defender_webauthn_get_option",username:webauthn.username,_def_nonce:webauthn.nonce},success:function(n){if(!1===n.success||void 0===n.data.challenge){var r="";return r="undefined"!==n.data.message&&"undefined"!==n.data.code&&0<n.data.code?n.data.message:webauthn.i18n.authenticator_verification_failed,l.html(u("error",r,!1,!0)),void h()}var a=n.data;if(a.challenge=Uint8Array.from(window.atob(wpdefBase64Url2Base64(a.challenge)),(function(e){return e.charCodeAt(0)})),a.allowCredentials){var i,d=t(a.allowCredentials);try{for(d.s();!(i=d.n()).done;){var o=i.value;o.id=Uint8Array.from(window.atob(wpdefBase64Url2Base64(o.id)),(function(e){return e.charCodeAt(0)}))}}catch(e){d.e(e)}finally{d.f()}}var s=a.clientID;delete a.clientID,navigator.credentials.get({publicKey:a}).then((function(e){return{id:e.id,type:e.type,rawId:wpdefArrayToBase64String(new Uint8Array(e.rawId)),response:{authenticatorData:wpdefArrayToBase64String(new Uint8Array(e.response.authenticatorData)),clientDataJSON:wpdefArrayToBase64String(new Uint8Array(e.response.clientDataJSON)),signature:wpdefArrayToBase64String(new Uint8Array(e.response.signature)),userHandle:e.response.userHandle?wpdefArrayToBase64String(new Uint8Array(e.response.userHandle)):null}}})).then(JSON.stringify).then((function(n){e.ajax({url:webauthn.admin_url,type:"POST",data:{action:"defender_webauthn_verify_response",data:window.btoa(n),client_id:s,username:webauthn.username,_def_nonce:webauthn.nonce},success:function(e){var n="";n=!0===e.success?u("success",webauthn.i18n.authenticator_verification_success,!1,!0):u("error",webauthn.i18n.authenticator_verification_failed,!1,!0),l.html(n),h()},error:function(){l.html(u("error",webauthn.i18n.authenticator_verification_failed,!1,!0)),h()}})})).catch((function(e){l.html(u("error",webauthn.i18n.authenticator_verification_failed,!1,!0)),h()}))},error:function(){l.html(u("error",webauthn.i18n.authenticator_verification_failed,!1,!0)),h()}}))})),e("body").on("click","#defender-biometric-tbl .toggle-row-content",(function(n){e(n.currentTarget).closest("tr").toggleClass("expanded")}))}(jQuery)}});