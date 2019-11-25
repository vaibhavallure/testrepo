jQuery.noConflict();

jQuery(document).ready(function () {
    jQuery('.btn-cart').click(function () {
        addToShoppingCart(this);
    });
});

function addToShoppingCart(button, formId, relode=false) {

    if (jQuery("div.t_Tooltip_customcart").length) {
        jQuery(".close-tooltip").trigger('click');
    }
    jQuery('.ajax-error-messages').html('');

    var clicked = button;
    if(jQuery("#product_addtocart_form_sample").attr('action')) {
        var myString = jQuery("#product_addtocart_form_sample").attr('action');
        var myArray = myString.split('/product/');
        if(jQuery(clicked).parent('#product_addtocart_form_sample').length == 1 && myArray.length < 2) return;
    }
    if (jQuery(clicked).parent('#product_addtocart_form_sample').length == 1) {
        var datos = "";
        var stringJSON = '{"qty":"' + jQuery(clicked).parent().find("#qty_sample").val() + '"' + datos + ',"related_product":""}';
        var dataSend = JSON.parse(stringJSON);
        var urlAdd2Cart = jQuery("#product_addtocart_form_sample").attr('action');
    } else {
        if (formId.id == 'product_addtocart_giftcard_form') {
            var datos = "";
            var amount;

            if (jQuery("select#cart-amount option:selected").val() == 'other') {
                amount = jQuery('input#card-amount').val();
            } else {
                amount = jQuery('select#cart-amount').val();
            }
            datos += ',"card_amount":"' + amount +
            '","card_type":"email"' +
            ',"mail_to":"' + jQuery('input#mail-to').val() +
            '","mail_to_email":"' + jQuery('input#mail-to-email').val() +
            '","mail_from":"' + jQuery('input#mail-from').val() +
            '","mail_from_email":"' + jQuery('input#mail-from-email').val() +
            '","mail_message":"' + jQuery('textarea#mail-message').val() +
            '","mail_delivery_date":"' + jQuery('input#date-input').val() +
            '","mail_delivery_option":"' + jQuery('input#mail_delivery_option:checked').val() +
            '","offline_city":"","offline_country":"","offline_phone":"","offline_state":"","offline_street":"","offline_zip":""';

            var stringJSON = '{"qty":"' + jQuery("#" + formId.id + " #qty").val() + '"' + datos + ',"related_product":""}';
            var dataSend = JSON.parse(stringJSON);
            var urlAdd2Cart = formId.action;
        } else {
            if (jQuery(clicked).parents('#product_addtocart_form').length == 1) {
                if ((jQuery('#qty').length == 1 && jQuery('#qty').val() * 1) == 0) return;
                var datos = "";
                jQuery("#product-options-wrapper select").each(function (index) {
                    if (this.value == "")
                        mibandera = false;
                    datos += ',"' + this.name + '":"' + this.value + '"';
                });

				/*gift message code here added*/
				var specialInstruction = jQuery("#" + formId.id +" .special-inst-product").val();


                datos += '' + ',"gift-special-instruction":"'+jQuery.trim(specialInstruction)+'"';



                var purchased_from = jQuery("#" + formId.id +" .puchsed-from-cat").val();
                datos += '' + ',"purchased_from_cat":"'+jQuery.trim(purchased_from)+'"';

              //allure code start
                var flagP = true;
                if(jQuery('#parent-child-product').length){
                	var checkParentChild = jQuery('#parent-child-product').val();
                	if(checkParentChild == 1){
                		flagP = true;
                	}
                }

                if(flagP){
	                var super_attribute = {};
	                var options= {};
	                var optionStr = '';
	                var formData = jQuery('#product_addtocart_form').serializeArray();
	                for(var i = 0; i<formData.length; i++){
	                	var record = formData[i];
	                    if(record.name.indexOf("super_attribute") >= 0){
	                    	var index = record.name.match(/\[(.*?)\]/)[1];
	                        super_attribute[index]=record.value;
	                    }
	                    if(record.name.indexOf("options") >= 0){
	                    	var index = record.name.match(/\[(.*?)\]/)[1];
	                        options[index]=record.value;
	                    }
	                 }
	                 if(Object.keys(super_attribute).length>0){
	                	 optionStr = optionStr + '"super_attribute":'+JSON.stringify(super_attribute);
	                	 if(Object.keys(options).length>0)
	                		 optionStr = optionStr + ',';
	                 }
	                 if(Object.keys(options).length>0){
	                	 optionStr = optionStr + '"options":'+JSON.stringify(options);
	                 }

	                 stringJSON = '{"qty":"' + jQuery(clicked).parent().find("#qty").val() + '"' + datos + ',"related_product":"",'+optionStr+'}';
                }else{
                	var stringJSON = '{"qty":"' + jQuery(clicked).parent().find("#qty").val() + '"' + datos + ',"related_product":""}';
                }
                 //allure code end

                //var stringJSON = '{"qty":"' + jQuery(clicked).parent().find("#qty").val() + '"' + datos + ',"related_product":""}';
                var dataSend = JSON.parse(stringJSON);
                var urlAdd2Cart = jQuery("#product_addtocart_form").attr('action');
            } else {
                if (jQuery("#" + formId.id).attr('class') == "product_addtocart_celebrities_form") {
                    var datos = "";
                    jQuery("#" + formId.id + " #product-options-wrapper select").each(function (index) {
                        if (this.value == "")
                            mibandera = false;
                        datos += ',"' + this.name + '":"' + this.value + '"';
                    });
                    var stringJSON = '{"qty":"' + jQuery(clicked).parent().find("#qty").val() + '"' + datos + ',"related_product":""}';
                    var dataSend = JSON.parse(stringJSON);
                    urlAdd2Cart = formId.action;
                } else {
                    var dataSend = {};
                    var urlAdd2Cart = jQuery(clicked).attr('href');
                }
            }
        }
    }

    jQuery("div.top-cart").css("background", "url('" + SKIN_URL + "frontend/mt/default/images/loading.gif') no-repeat scroll 0 0 transparent");

    jQuery('html, body').animate({
        scrollTop: 0
    }, 'fast');
    var jqxhr = jQuery.get(urlAdd2Cart, dataSend, function (data) {
        if('The requested quantity' == data.substr(0, 22)) { //expecting string 'The requested quantity for "%s" is not available' from /app/code/local/Mage/CatalogInventory/Model/Stock/Item.php #590
            alert(data);
        } else {
            jQuery('#ajax_cart_content').html(data);
            //jQuery('#just_added').slideDown(500).delay( 5000 ).slideUp( 600 );
            if(jQuery(window).width() >= 768) {
                jQuery('#just_added').slideDown(1000);
            }
            if (formId.id == 'product_addtocart_giftcard_form') {
                jQuery('input#mail-to').val("");
                jQuery('input#mail-to-email').val("");
                jQuery('textarea#mail-message').val("");
                jQuery('input#mail-from').val("");
                jQuery('input#mail-from-email').val("");
            }
                jQuery('#topcart-popup').addClass('just_added');


            if(relode)
            location.reload();

            setTimeout(function() {
                jQuery('#just_added').slideUp(1000);
                jQuery('#topcart-popup').removeClass('just_added');
            }, 5000);
        }
    });
//.done(function() { alert("second success"); })
//.fail(function() { alert("error"); })
//.always(function() { alert("finished"); });
//jqxhr.always(function(){ alert("second finished"); });
};

function addToShoppingCartFromQuickView(button, formId) {
    if (parent.jQuery("div.t_Tooltip_customcart").length) {
        parent.jQuery(".close-tooltip").trigger('click');
    }
    jQuery('.ajax-error-messages').html('');
    var clicked = button;
    if (jQuery(clicked).parent('#product_addtocart_form_sample').length == 1) {
        var datos = "";
        var stringJSON = '{"qty":"' + jQuery(clicked).parent().find("#qty_sample").val() + '"' + datos + ',"related_product":""}';
        var dataSend = JSON.parse(stringJSON);
        var urlAdd2Cart = jQuery("#product_addtocart_form_sample").attr('action');

    } else {
        if (jQuery(clicked).parents('#product_addtocart_form').length == 1) {
            if ((jQuery('#qty').length == 1 && jQuery('#qty').val() * 1) == 0) return;
            var datos = "";
            jQuery("#product-options-wrapper select").each(function (index) {
                if (this.value == "")
                    mibandera = false;
                datos += ',"' + this.name + '":"' + this.value + '"';
            });


            var super_attribute = {};
            var options= {};
            var optionStr = '';
            var formData = jQuery('#product_addtocart_form').serializeArray();
            for(var i = 0; i<formData.length; i++){
            	var record = formData[i];
                if(record.name.indexOf("super_attribute") >= 0){
                	var index = record.name.match(/\[(.*?)\]/)[1];
                    super_attribute[index]=record.value;
                }
                if(record.name.indexOf("options") >= 0){
                	var index = record.name.match(/\[(.*?)\]/)[1];
                    options[index]=record.value;
                }
             }
             if(Object.keys(super_attribute).length>0){
            	 optionStr = optionStr + '"super_attribute":'+JSON.stringify(super_attribute);
            	 if(Object.keys(options).length>0)
            		 optionStr = optionStr + ',';
             }
             if(Object.keys(options).length>0){
            	 optionStr = optionStr + '"options":'+JSON.stringify(options);
             }



            var specialInstruction = jQuery("#" + formId.id +" .special-inst-product").val();
            datos += '' + ',"gift-special-instruction":"'+jQuery.trim(specialInstruction)+'"';

            var purchased_from = jQuery("#" + formId.id +" .puchsed-from-cat").val();
            datos = '' + ',"purchased_from_cat":"'+jQuery.trim(purchased_from)+'"';

            var qty = parent.document.getElementById(parent.jQuery('iframe.fancybox-iframe').attr('id')).contentWindow.document.getElementById('qty').value;
            var stringJSON = '{"qty":"' + qty + '"' + datos + ',"related_product":"",'+optionStr+'}';
            var dataSend = JSON.parse(stringJSON);
            var urlAdd2Cart = jQuery("#product_addtocart_form").attr('action');
        } else {
            var dataSend = {};
            var urlAdd2Cart = jQuery(clicked).attr('href');
        }
    }

    parent.jQuery("div.top-cart").css("background", "url('" + parent.SKIN_URL + "frontend/mt/default/images/loading.gif') no-repeat scroll 0 0 transparent");
    var jqxhr = jQuery.get(urlAdd2Cart, dataSend, function (data) {
        if('The requested quantity' == data.substr(0, 22)) { //expecting string 'The requested quantity for "%s" is not available' from /app/code/local/Mage/CatalogInventory/Model/Stock/Item.php #590
            alert(data);
        } else {
            parent.jQuery("html, body").animate({
                scrollTop: 0
            }, "slow");
            parent.jQuery('#ajax_cart_content').html(data);
            if(parent.jQuery('#ajax_cart_content').hasClass('checkout_cart'))
                parent.window.location.reload();
            if(jQuery(window).width() >= 768) {
                parent.jQuery('#just_added').slideDown(1000);
            }
            parent.jQuery('#topcart-popup').addClass('just_added');
            parent.setTimeoutDtn();
            parent.jQuery.fancybox.close();
        }
    });

}
function setTimeoutDtn(){
    setTimeout(function() {
            jQuery('#just_added').slideUp(1000);
            jQuery('#topcart-popup').removeClass('just_added');
    }, 5000);
}
function getBaseURL() {
    var url = location.href;  // entire url including querystring - also: window.location.href;
    var baseURL = url.substring(0, url.indexOf('/', 14));


    if (baseURL.indexOf('http://localhost') != -1) {
        // Base Url for localhost
        var url = location.href;  // window.location.href;
        var pathname = location.pathname;  // window.location.pathname;
        var index1 = url.indexOf(pathname);
        var index2 = url.indexOf("/", index1 + 1);
        var baseLocalUrl = url.substr(0, index2);

        return baseLocalUrl + "/";
    } else if (baseURL.indexOf('http://184.106.64.100') != -1) {
        return 'http://184.106.64.100/mariatash_mc_1702/';
    }
    else {
        // Root Url for domain name
        return baseURL + "/";
    }

}

function sendEmailToNotification(url, product, email) {
    jQuery('.showMessages').hide();
    var stringJSON = '{"product_id":"' + product + '","email":"' + email + '"}';
    var dataSend = JSON.parse(stringJSON);

    jQuery.getJSON(url, dataSend, function (data) {
        if (data.redirect) {
            location.href = data.redirect_url;
        } else {
            jQuery('div.content-email-panel div.close-tooltip').trigger('click');
            jQuery('.showMessages').show();
            jQuery('.showMessages').html(data.message);
        }
    });
}

;
