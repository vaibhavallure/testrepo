jQuery(document).ready(function() {
	jQuery('body').css('margin-top',jQuery('.store-notice.top').height());
	jQuery('body').css('margin-bottom',jQuery('.store-notice.bottom').height());
	jQuery(".close").click(function(){
		 jQuery(this).hide()
		 jQuery(".store-notice").css("opacity","0");
		 jQuery("body").css("margin-top","0");
	 });
});