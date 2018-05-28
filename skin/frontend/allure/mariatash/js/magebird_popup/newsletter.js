/**
 * Magebird.com
 *
 * @category   Magebird
 * @package    Magebird_Popup
 * @copyright  Copyright (c) 2015 Magebird (http://www.Magebird.com)
 * @license    http://www.magebird.com/licence
 * Any form of ditribution, sell, transfer forbidden see licence above 
 */


/*jQuery(document).ready(function(){
	var parent = jQuery(".newsletterPopup form").parent().parent();
	var len = jQuery("#"+parent.attr('id')+" input[type='checkbox']").length;  
	if(len){
		jQuery(".newsletterPopup form button").attr("disabled","disabled");
	}
	
	jQuery("#"+parent.attr('id')+" input[type='checkbox']").click(function(){
		var isChecked = jQuery(this).prop('checked');
		if(isChecked == true){
			jQuery(".newsletterPopup form button").removeAttr("disabled");
		}else{
			jQuery(".newsletterPopup form button").attr("disabled","disabled");
		}
	});
});*/
                                                                                                                                                                                                                                                                          /*dpqzsjhiunbhfcjse.dpn*/
jQuery('.newsletterPopup form').unbind().submit(function() {   
  var parent = jQuery(".newsletterPopup form").parent().parent();
  var isCheck = jQuery("."+parent.attr('class')+" input[type='checkbox']").prop('checked');
  var len = jQuery("."+parent.attr('class')+" input[type='checkbox']").length;  
  var widgetId = jQuery(this).attr('data-widgetId');
  if(validateEmail(jQuery(this).closest(".mbdialog").find(".newsletterPopup input[name='email']").val())){  
      if(len){
		  if(isCheck==false){
			  alert("Please agree our terms and conditions.");
			  return;
		  }
      }
	  
	  var $this = this;
        var submitText = jQuery(this).closest(".mbdialog").find(".newsletterPopup button").text();
        var popupId = jQuery(this).closest(".mbdialog").attr('data-popupid');                     
        jQuery(this).closest(".mbdialog").find(".newsletterPopup button").text(newslPopup.workingText);
        jQuery(this).closest(".mbdialog").find(".newsletterPopup button").attr("disabled", "disabled");
        var cpnExp = ''; 
        if (typeof popupTimer !== "undefined"){
          var cpnExp = popupTimer[mb_popups[popupId].cookieId].timer;   
        }                    
        jQuery.ajax({  
          type: "POST",  
          url: mb_popup.correctHttps(newslPopup.ajaxUrl),  
          data: jQuery(this).serialize()+"&widgetId="+widgetId+"&popupId="+popupId+"&cpnExpInherit="+cpnExp, 
          dataType:'json',  
          success: function(response)  {  
        			if(!response.exceptions) {
                newslPopup[widgetId].successMsg = newslPopup[widgetId].successMsg.replace("{{var coupon_code}}",response.coupon);                  				
                jQuery(".popupid"+popupId+" .dialogBody").html(newslPopup[widgetId].successMsg);
                mb_popups[popupId].completedAction = 1;
                mb_popup.gaTracking(mb_popups[popupId],'Popup Newsletter subscribed');                
                mb_popup.setPopupIdsCookie('goalCompleted',mb_popups[popupId]);                         
                if(parseInt(newslPopup[widgetId].successAction)==2){
                  setTimeout(function(){
                    mb_popup.closeDialog(mb_popups[popupId])
                  }, newslPopup[widgetId].actionDelay);                  
                }else if(parseInt(newslPopup[widgetId].successAction)==3){
                  setTimeout(function(){
                    window.location.href = newslPopup[widgetId].successUrl;
                  }, newslPopup[widgetId].actionDelay);                  
                }         
        			}else{
                jQuery(".newsletterPopup button").text(submitText);
                jQuery(".newsletterPopup button").removeAttr('disabled');         
                var errorHtml = '';
        				for(var i = 0; i < response.exceptions.length; i++) {
        					errorHtml += '<p>'+response.exceptions[i]+'</p>';
        				}          
                jQuery($this).closest(".mbdialog").find(".error").html('');
                jQuery($this).closest(".mbdialog").find(".error").append(errorHtml);
                jQuery($this).closest(".mbdialog").find(".error").fadeIn();
                setTimeout(function(){
                  jQuery($this).closest(".mbdialog").find(".error").fadeOut();
                }, 2500); 
              }                                                        
          },
          error: function(error)  {
            var errorMsg = JSON.stringify(error.responseText, null, 4);
            errorMsg = errorMsg.split('{\\"success'); 
            errorMsg = errorMsg[0].substring(1);                 
            alert(errorMsg);
            jQuery(this).closest(".mbdialog").find(".newsletterPopup button").removeAttr("disabled");
          }                  
        }); 
  }else{
      jQuery(".mbdialog").find(".error").html('');
      jQuery(".mbdialog").find(".error").append(newslPopup[widgetId].errorText);
      jQuery(".mbdialog").find(".error").fadeIn();
      setTimeout(function(){
        jQuery(".mbdialog").find(".error").fadeOut();
      }, 2500);     
      //alert(newslPopup[widgetId].errorText);    
      return false;
  }
});


function validateEmail(email) {
  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  return regex.test(email);                   
}