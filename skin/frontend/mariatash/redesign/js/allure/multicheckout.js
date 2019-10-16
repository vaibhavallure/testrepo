/**
 * It's custom js file created for
 * multicheckout step's handle the behaviours.
 *
 * Created by Allure Software, Inc.
 */

var blockCheckoutUi = function(){
	jQuery.fancybox.showLoading();
    jQuery.fancybox.helpers.overlay.open({parent: $('body'),closeClick : false});
}

var unblockCheckoutUi = function(){
	jQuery.fancybox.hideLoading();
	jQuery('.fancybox-overlay.fancybox-overlay-fixed').hide();
}