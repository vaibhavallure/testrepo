jQuery(document).ready(function(){
	var $j = jQuery;
	var $ = jQuery;

	/**
	 * Login Popup code Start
	 */
	$j("#sign_in_label").on('click',function(){
		$j('#wishlist_input').val('');
		$j(".popupLoginModel").css({"opacity":"1","pointer-events":"auto"});
		unScrollBody();
	});

	$j("#wishlist_label").on('click',function(){
		$j('#wishlist_input').val('wishlist');
		$j(".popupLoginModel").css({"opacity":"1","pointer-events":"auto"});
		unScrollBody();
	});

	$j(".popupLoginModel .close").on('click',function(){
		$j('#wishlist_input').val('');
		$j(".popupLoginModel").css({"opacity":"0","pointer-events":"none"});
		scrollBody();
	});
    $j(".checkoutPopupLoginModel .close").on('click',function(){
        $j(".checkoutPopupLoginModel").css({"opacity":"0","pointer-events":"none"});
        scrollBody();
    });

	$j('.popupLoginModel #passwd-login').keypress(function (e) {
		 var key = e.which;
		 if(key == 13)
			 $j("#signin-btn-popup").trigger('click');
	});

	$j("#signin-btn-popup").on('click',function(){
		 var myForm = new VarienForm('popup-login-form', false);

		 if (myForm.validator.validate()) {

			var key = Allure.LoginFormKey;

		   	var requestData = $j('#popup-login-form').serialize();

			$j.ajax({
				url : Allure.LoginUrlAjax,
				dataType : 'json',
				type : 'POST',
				data: requestData,
		        beforeSend: function() { $j('#popup_loader').show(); },
		        complete: function() { $j('#popup_loader').hide(); },
				success : function(data){
					if (data.success) {
						 $j('#login_msg_div').css('display','none');
						 $j(".modalDialog").css({"opacity":"0","pointer-events":"none"});
						 location.reload();
					} else {
						$j('#login_msg_div').css('display','block');
						$j('#login-msg').html(data.error);
					}
				}
			});
		 } //End of form Validate
	});
	/**
	 * Login Popup code End
	 */


	/**
	 * Resgister Model Start
	 */

	$j(".popupRegisterModel .close").on('click',function(){
		$j('#wishlist_input').val('');
		$j(".popupRegisterModel").css({"opacity":"0","pointer-events":"none"});
		scrollBody();
	});

    $j(".popupSignUpConfirmationModel .close").on('click',function(){
         location.reload();
    });

	$j("#signup-btn-popup").on('click',function(){
		 var myForm = new VarienForm('popup-register-form');

		 var privacyPolicySelector = $j("#popup-register-form #popup_is_privacy_agree");
		 var isChecked = privacyPolicySelector.prop("checked");
		 if(isChecked == false){
			 myForm.validator.validate();
			 privacyPolicySelector.addClass("checkbox-error-validate");
			 privacyPolicySelector.parent().addClass("label-error-validate");
			 return;
		 }else{
			 privacyPolicySelector.removeClass("checkbox-error-validate");
			 privacyPolicySelector.parent().removeClass("label-error-validate");
		 }

		 if(myForm.validator.validate()){
			var firstname 		= $j('#firstname').val();
			var lastname 		= $j('#lastname').val();
			var email 			= $j('#email-register').val();
			var password 		= $j('#password').val();
			var is_subscribed 	= $j('#popup_is_subscribed').is(":checked");
			var key				= Allure.RegisterModelFormKey;

			var requestData = {
				"firstname":firstname,
				"lastname":lastname,
				"email":email,
				"password":password,
				"is_subscribed":is_subscribed,
				"form_key":key
			};

			$j.ajax({
				url :Allure.RegisterModelUrlAjax ,
				dataType : 'json',
				type : 'POST',
				data: requestData,
		        beforeSend: function() { $j('#popup_loader_register').show(); },
		        complete: function() { $j('#popup_loader_register').hide(); },
				success : function(data) {
					if (data.success) {
						$j('#reg_msg_div').css('display','none');
						$j(".modalDialog").css({"opacity":"0","pointer-events":"none"});
                        $j(".popupRegisterModel").css({"opacity":"0","pointer-events":"none"});
                        $j(".popupSignUpConfirmationModel").css({"opacity":"1","pointer-events":"auto"});
					} else {
						$j('#reg_msg_div').css('display','block');
						$j('#register-msg').html(data.msg);
					}
				}
			});
		 } //End of form Validate
	});
	/**
	 * Register Model End
	 */

	/**
	 * Reset Password Popup Start
	 */
	$j(".popupResetPasswordModel .close").on('click',function(){
		$j(".popupResetPasswordModel").css({"opacity":"0","pointer-events":"none"});
        scrollBody();
	});

	$j("#reset_pass_btn").on('click',function(){
	    var myForm = new VarienForm('popup-resetpassword-form', true);
		if (myForm.validator.validate()) {
			var email 	= $j('#resetEmail').val();
			var key	= Allure.ResetPassFormKey;

			var requestData = {
			   "email":email,
			   "form_key":key
			};

	         $j.ajax({
				url : Allure.ResetPassUrlAjax,
				dataType : 'json',
				type : 'POST',
				data: requestData,
	            beforeSend: function() { $j('#popup_loader_resetpassword').show(); },
	            complete: function() { $j('#popup_loader_resetpassword').hide(); },
				success : function(data){
					if (data.success) {
						$j('#resetpassword_msg_div').css('display','block');
						$j('#resetpassword-msg').html(data.msg);
	                     setTimeout(function(){
	                    	 $j(".popupResetPasswordModel").css({"opacity":"0","pointer-events":"none"});
	                    }, 5000);

					} else {
						$j('#resetpassword_msg_div').css('display','block');
						$j('#resetpassword-msg').html(data.msg);
					}
				}
			});
		} //End of form Validate
	});
	/**
	 *Reset Password Popup End
	 */

	//Account delete

    $j("#popupcheckbox_delmyacc_confirm").on('click',function(){

    	if ($j('#popupcheckbox_delmyacc_confirm').is(":checked"))
    	{
    		$j('#delmyacc-btn-popup').css('color','#FFF');
    	}else{
    		$j('#delmyacc-btn-popup').css('color','#6f6b5a');
    	}
   	// alert($('#popupcheckbox_delmyacc_confirm').val());
    });

    $j("#delmyacc-btn-popup").on('click',function(){

		if ($j('#popupcheckbox_delmyacc_confirm').is(":checked")) {
			var email =$j('#del_acc_email').val();
			var id =$j('#del_acc_id').val();

			var requestData = {
				"email":email,
				"id":id,
				"form_key":Allure.DeleteAccountModelFormKey
			};

	        $j.ajax({
			   url : Allure.DeleteAccuntURL,
			   dataType : 'json',
			   type : 'POST',
			   data: requestData,
	           beforeSend: function() { $j('.please-wait-popup-del').show(); },
	           complete: function() { $j('.please-wait-popup-del').hide(); },
		       success : function(data){
				   if (data.success) {
                        location.reload();
				   } else {
						console.log(data.error);
						$j('#reg_msg_div').css('display','block');
						$j('#register-msg').html(data.error);
				   }
				}
			});
		}
    });

    $j(".popupDelMyAccModel .close").on('click',function(){
    	$j(".popupDelMyAccModel").css({"opacity":"0","pointer-events":"none"});
    });
});

function openRegisterModal(){
	jQuery('#wishlist_input').val('');
	jQuery(".popupLoginModel").css({"opacity":"0","pointer-events":"none"});
	jQuery(".popupRegisterModel").css({"opacity":"1","pointer-events":"auto"});
	jQuery('.popupRegisterModel .amazon-pay-button img').attr('src','https://images-na.ssl-images-amazon.com/images/G/01/EP/offAmazonPayments/us/live/prod/image/lwa/lightgray/small/LwA.png');
    unScrollBody();
};

function gotoLoginPage(){
	jQuery("#popup-resetpassword-form .close").trigger('click');
    jQuery("#popup-register-form .close").trigger('click');
	jQuery("#sign_in_label").trigger('click');
    unScrollBody();
}

function openRestPasswordModal(){
	jQuery(".popupLoginModel").css({"opacity":"0","pointer-events":"none"});
	jQuery(".popupRegisterModel").css({"opacity":"0","pointer-events":"none"});
	jQuery(".popupResetPasswordModel").css({"opacity":"1","pointer-events":"auto"});
    unScrollBody();
};

function openDelMyAccountModal(){
	jQuery(".popupLoginModel").css({"opacity":"0","pointer-events":"none"});
	jQuery(".popupRegisterModel").css({"opacity":"0","pointer-events":"none"});
	jQuery(".popupResetPasswordModel").css({"opacity":"0","pointer-events":"none"});
	jQuery(".popupDelMyAccModel").css({"opacity":"1","pointer-events":"auto"});
    unScrollBody();
};
function scrollBody() {
    jQuery("body").css({"position":"static","overflow":"auto","width":"auto"});
}
function unScrollBody() {
    jQuery("body").css({"position":"fixed","overflow":"hidden","width":"100%"});
}
function checkoutLogin(redirectUrls) {
    jQuery(".checkoutPopupLoginModel").css({"opacity":"1","pointer-events":"auto"});
    jQuery("#redirectUrl").val(redirectUrls);
    unScrollBody();
}