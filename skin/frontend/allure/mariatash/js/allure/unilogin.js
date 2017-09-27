jQuery(document).ready(function(){
	var $j = jQuery;
	/**
	 * Login Popup code Start
	 */
	
	$j("#sign_in_label").on('click',function(){
		$j(".popupLoginModel").css({"opacity":"1","pointer-events":"auto"});
	});

	$j(".popupLoginModel .close").on('click',function(){
		$j(".popupLoginModel").css({"opacity":"0","pointer-events":"none"});
	});

	$j("#signin-btn-popup").on('click',function(){
		 var myForm = new VarienForm('popup-login-form', false); 
		 if(myForm.validator.validate()){ 
			var usrname = $j('#popup-login-form #username').val();
			var passwd 	= $j('#popup-login-form #passwd').val();
			var key		= Allure.LoginFormKey;
			var request = {"usrname":usrname, "passwd":passwd, "form_key":key};
					
			$j.ajax({
				url : Allure.LoginUrlAjax,
				dataType : 'json',
				type : 'POST',
				data: {request:request},
				success : function(data){
					if(data.success){
						$j('#login_msg_div').css('display','none');
						$j(".popupLoginModel .modalDialog").css({"opacity":"0","pointer-events":"none"});
						if(data.redirect){
		                    window.location.replace(data.url);
		                }
		                else {
						    location.reload();
		                }
					}else{
						$j('#login_msg_div').css('display','block');
						$j('#login-msg').html(data.error);
					}
				}
			});
		 } 
	});
	/**
	 * Login popup code end
	 */
	
	/**
	 * Register popup code start
	 */
	$j(".popupRegisterModel .close").on('click',function(){
		$j(".popupRegisterModel").css({"opacity":"0","pointer-events":"none"});
	});

	$j("#signup-btn-popup").on('click',function(){
		 var myForm = new VarienForm('popup-register-form'); 
		 if(myForm.validator.validate()){ 
			var firstname 		= $j('#popup-register-form #firstname').val();
			var lastname 		= $j('#popup-register-form #lastname').val();
			var email 			= $j('#popup-register-form #email-register').val();
			var password 		= $j('#popup-register-form #password').val();
			var is_subscribed 	= $j('#popup_is_subscribed').is(":checked");
			var key				= Allure.RegisterModelFormKey;
			var request = {
							"firstname":firstname,
							"lastname":lastname,
							"email":email,
							"password":password,
							"is_subscribed":is_subscribed,
							"form_key":key
						};
				
			$j.ajax({
				url : Allure.RegisterModelUrlAjax,
				dataType : 'json',
				type : 'POST',
				data: {request:request},
				success : function(data){
					if(data.success){
						$j('#reg_msg_div').css('display','none');
						$j(".modalDialog").css({"opacity":"0","pointer-events":"none"});
						location.reload();
					}else{
						$j('#reg_msg_div').css('display','block');
						$j('#register-msg').html(data.error);
					}
				}
			});
		 } 
	});
	/**
	 *Register popup code end 
	 */
	
	/**
	 * Reset password popup start
	 */
	$j(".popupResetPasswordModel .close").on('click',function(){
    	jQuery(".popupResetPasswordModel").css({"opacity":"0","pointer-events":"none"});
	});

	$j("#reset_pass_btn").on('click',function(){
		var myForm = new VarienForm('popup-resetpassword-form', true);
		if(myForm.validator.validate()){
			var email 	= $j('#resetEmail').val();
			var key		= Allure.ResetPassFormKey;
			var request = {"email":email,"form_key":key};
			$j.ajax({
				url : Allure.ResetPassUrlAjax,
				dataType : 'json',
				type : 'POST',
				data: {request:request},
				success : function(data){
					if(data.success){
						$j('#resetpassword_msg_div').css('display','block');
						$j('#resetpassword-msg').html(data.msg);
						setTimeout(function(){
							$j(".popupResetPasswordModel").css({"opacity":"0","pointer-events":"none"});
						}, 5000);
					}else{
						$j('#resetpassword_msg_div').css('display','block');
						$j('#resetpassword-msg').html(data.msg);
					}
				}
			});
		} 
	});
	/**
	 *Reset password popup code end 
	 */
	
});


function openRegisterModal(){
	jQuery(".popupLoginModel").css({"opacity":"0","pointer-events":"none"});
	jQuery(".popupRegisterModel").css({"opacity":"1","pointer-events":"auto"});
};

function openRestPasswordModal(){
    jQuery(".popupLoginModel").css({"opacity":"0","pointer-events":"none"});
    jQuery(".popupRegisterModel").css({"opacity":"0","pointer-events":"none"});
    jQuery(".popupResetPasswordModel").css({"opacity":"1","pointer-events":"auto"});
};
