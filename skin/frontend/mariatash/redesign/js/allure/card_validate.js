jQuery(document).ready(function(){
	applyCard(jQuery('input[name="payment[cc_number]"]'));
	jQuery(document).on('keyup blur','input[name="payment[cc_number]"]',function() {
		applyCard(jQuery(this));
	});
}); 

function applyCard(evt){
	var cardNumber = jQuery(evt);
	var creditCards = jQuery('#credit_cards'); 
	
	cardNumber.payform('formatCardNumber');
	
	if (jQuery.payform.validateCardNumber(cardNumber.val()) == false) {
		cardNumber.addClass('has-card-error');
		cardNumber.removeClass('has-card-success');
    } else {
    	cardNumber.removeClass('has-card-error');
    	cardNumber.addClass('has-card-success');
    }
	
	creditCards.find('.payment-card').removeClass("active");
	
	var cardType = '';
	if (jQuery.payform.parseCardType(cardNumber.val()) == 'visa') {
		cardType = 'VI';
		creditCards.find('#vi-card').addClass("active");
	} else if (jQuery.payform.parseCardType(cardNumber.val()) == 'amex') {
		cardType = 'AE';
		creditCards.find('#ae-card').addClass("active");
	} else if (jQuery.payform.parseCardType(cardNumber.val()) == 'mastercard') {
		cardType = 'MC';
		creditCards.find('#mc-card').addClass("active");
	}else if(jQuery.payform.parseCardType(cardNumber.val()) == 'discover'){
		cardType = 'DI';
		creditCards.find('#di-card').addClass("active");
	}else{
		cardType = '';
	}
	jQuery('select[name="payment[cc_type]"]').val(cardType);
}