jQuery(document).ready(function(){
	var $j = jQuery;
	/**
	 * Login Popup code Start
	 */
	$j("#sign_in_label").on('click',function(){
		$j('#wishlist_input').val('');
		$j(".popupLoginModel").css({"opacity":"1","pointer-events":"auto"});
	});
	
	$j("#wishlist_label").on('click',function(){
		$j('#wishlist_input').val('wishlist');
		$j(".popupLoginModel").css({"opacity":"1","pointer-events":"auto"});
	});
	
	$j(".popupLoginModel .close").on('click',function(){
		$j('#wishlist_input').val('');
		$j(".popupLoginModel").css({"opacity":"0","pointer-events":"none"});
	});
	
	$j('.popupLoginModel #passwd').keypress(function (e) {
		 var key = e.which;
		 if(key == 13)
			 $j("#signin-btn-popup").trigger('click');
		 return false;  
	});
	
	$j("#signin-btn-popup").on('click',function(){
		 var myForm = new VarienForm('popup-login-form', false); 
		 if(myForm.validator.validate()){ 
			var usrname = jQuery('#username').val();
			var passwd = jQuery('#passwd').val();
			var wishlist= jQuery('#wishlist_input').val();
			var key=Allure.LoginFormKey;
			var request = {
					"usrname":usrname,
					"passwd":passwd,
					'form_key':key
			};
					
			$j.ajax({
				url : Allure.LoginUrlAjax,
				dataType : 'json',
				type : 'POST',
				data: {request:request},
		        beforeSend: function() { $j('#popup_loader').show(); },
		        complete: function() { $j('#popup_loader').hide(); },
				success : function(data){
					if(data.success){
						 $j('#login_msg_div').css('display','none');
						 $j(".modalDialog").css({"opacity":"0","pointer-events":"none"});
						 if(wishlist){
							 window.location.href = Allure.Wishlist;
						 }
						 else{
							 location.reload();
						}
					}else{
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
	});

	$j("#signup-btn-popup").on('click',function(){
		 var myForm = new VarienForm('popup-register-form'); 
		 if(myForm.validator.validate()){ 
			var firstname 		= $j('#firstname').val();
			var lastname 		= $j('#lastname').val();
			var email 			= $j('#email-register').val();
			var password 		= $j('#password').val();
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
				url :Allure.RegisterModelUrlAjax ,
				dataType : 'json',
				type : 'POST',
				data: {request:request},
		        beforeSend: function() { $j('#popup_loader_register').show(); },
		        complete: function() { $j('#popup_loader_register').hide(); },
				success : function(data){
					if(data.success){
						$j('#reg_msg_div').css('display','none');
						$j(".modalDialog").css({"opacity":"0","pointer-events":"none"});
						 location.reload();
					}else{
						console.log(data.error);
						$j('#reg_msg_div').css('display','block');
						$j('#register-msg').html(data.error);
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
	$j(".popupResetPasswordModel .close").on('click',function(){console.log('hiee');
		$j(".popupResetPasswordModel").css({"opacity":"0","pointer-events":"none"});
	});

	$j("#reset_pass_btn").on('click',function(){
	     var myForm = new VarienForm('popup-resetpassword-form', true);
		 if(myForm.validator.validate()){
			 var email 	= $j('#resetEmail').val();
	         var key	= Allure.ResetPassFormKey;
	         var request = {
	               "email":email,
	               "form_key":key
	         };
	         
	         $j.ajax({
				url : Allure.ResetPassUrlAjax,
				dataType : 'json',
				type : 'POST',
				data: {request:request},
	            beforeSend: function() { $j('#popup_loader_resetpassword').show(); },
	            complete: function() { $j('#popup_loader_resetpassword').hide(); },
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
		} //End of form Validate
	});
	/**
	 *Reset Password Popup End 
	 */
});


function openRegisterModal(){
	jQuery('#wishlist_input').val('');
	jQuery(".popupLoginModel").css({"opacity":"0","pointer-events":"none"});
	jQuery(".popupRegisterModel").css({"opacity":"1","pointer-events":"auto"});
	jQuery('.popupRegisterModel .amazon-pay-button img').attr('src','https://images-na.ssl-images-amazon.com/images/G/01/EP/offAmazonPayments/us/live/prod/image/lwa/lightgray/small/LwA.png');
};

function gotoLoginPage(){
	jQuery(".close").trigger('click');
	jQuery("#sign_in_label").trigger('click');
}

function openRestPasswordModal(){
	jQuery(".popupLoginModel").css({"opacity":"0","pointer-events":"none"});
	jQuery(".popupRegisterModel").css({"opacity":"0","pointer-events":"none"});
	jQuery(".popupResetPasswordModel").css({"opacity":"1","pointer-events":"auto"});
};