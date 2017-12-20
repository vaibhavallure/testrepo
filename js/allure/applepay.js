if ( typeof Allure == 'undefined' ) var Allure = {};

if ( typeof Allure.ApplePay == 'undefined' ) Allure.ApplePay = {};

Allure.ApplePay.available = false;

Allure.ApplePay.enabled = false;

if (window.ApplePaySession) {

	Allure.ApplePay.available = true;
	
	Allure.ApplePay.enabled = false;

	Allure.ApplePay.session = null;
	
	Allure.ApplePay.request = {
	  	countryCode: 'US',
	  	currencyCode: 'INR',
	  	supportedNetworks: ['visa', 'masterCard', 'amex', 'discover'],
	  	merchantCapabilities: ['supports3DS','supportsCredit', 'supportsDebit'], // Make sure NOT to include supportsEMV here
	  	total: { label: 'Venus By Maria Tash', amount: Allure.ApplePay.lineTotal },
	  	shippingMethods: [{    
		    "label": "International Flat Rate",
		    "detail": "Arrives in 5 to 7 days",
		    "amount": 49.95,
		    "identifier": "flatrate_flatrate"
		},
		{
		    "identifier": "express_shipping",
		    "label": "Fedex Express",
		    "detail": "1-2 working days",
		    "amount": 100.0
		}],
		shippingType: 'delivery',
		requiredBillingContactFields: [
		    "postalAddress"
		],
		requiredShippingContactFields: [
		    "postalAddress",
		    "name",
		    "phone",
		    "email"
		],
		applicationData: {
			"order_id": 101010
		}
	};
	
	Allure.ApplePay.merchantId = 'merchant.com.mariatash.authorizenet';
	
	var promise = ApplePaySession.canMakePaymentsWithActiveCard(Allure.ApplePay.merchantId);
   
	promise.then ( function (canMakePayments) {
      
		if (canMakePayments) {
		   
		   console.log("Apple Pay Payment Available");

			Allure.ApplePay.enabled = true;
			
	      	jQuery("#applePayButton").prop('disabled', false);
		} else {

			Allure.ApplePay.enabled = false;
			
		   	console.log("Apple Pay is available but not activated yet");
		}
	});
} else {
	Allure.ApplePay.available = false;
	console.log("Apple Pay not available in this browser");
}

function createTransaction(dataObj) {
	
	console.log('starting createTransaction');
	console.log(dataObj);
	
	let objJsonStr = JSON.stringify(dataObj);
	//console.log(objJsonStr);
    let objJsonB64 = window.btoa(objJsonStr);
	//console.log(objJsonB64);
    
	jQuery.ajax({
		url: Allure.ApplePay.BaseUrl+'saveTransaction',
		data: {amount: '15.00', dataDesc: 'COMMON.APPLE.INAPP.PAYMENT', dataValue: dataObj,  dataBinary: objJsonB64},
		method: 'POST',
		timeout: 5000
		
	}).done(function(data){
		console.log(data);
		console.log('Success');
		
	}).fail(function(){
		console.log('Error');
	})
	
	return true;
}

function initApplePay(){

	console.log('Apple Pay Initiated');
	
	Allure.ApplePay.session = new ApplePaySession(3, Allure.ApplePay.request);

	// Merchant Validation
	Allure.ApplePay.session.onvalidatemerchant = function (event) {
		console.log(event);
		var promise = performValidation(event.validationURL);
		promise.then(function (merchantSession) {
			Allure.ApplePay.session.completeMerchantValidation(merchantSession);
		}); 
	}

	function performValidation(valURL) {
		return new Promise(function(resolve, reject) {
			var xhr = new XMLHttpRequest();
			xhr.onload = function() {
				console.log(this.responseText);
				var data = JSON.parse(this.responseText);
				console.log(data);
				resolve(data);
			};
			xhr.onerror = reject;
			xhr.open('POST', Allure.ApplePay.BaseUrl+'validateMerchant', true);
			xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhr.send('validationUrl='+valURL);
		});
	}

	Allure.ApplePay.session.onpaymentmethodselected = function(event) {
		console.log('starting onpaymentmethodselected');
		console.log(event);
		var newTotal = { type: 'final', label: 'Venus By Maria Tash', amount: Allure.ApplePay.lineTotal };
		var newLineItems =[{type: 'final',label: 'Spice #202', amount: '15.00' }]
		Allure.ApplePay.session.completePaymentMethodSelection( newTotal, Allure.ApplePay.lineItems);
	}

	Allure.ApplePay.session.onpaymentauthorized = function (event) {
		console.log('starting session.onpaymentauthorized');
		console.log(event);
		var promise = sendPaymentToken(event.payment.token);
		promise.then(function (success) {	
			var status;
			if (success){
				status = ApplePaySession.STATUS_SUCCESS;
				console.log('Apple Pay Payment SUCCESS ');
			} else {
				status = ApplePaySession.STATUS_FAILURE;
			}		
			console.log( "result of sendPaymentToken() function =  " + success );
			Allure.ApplePay.session.completePayment(status);
		});
	}

	function sendPaymentToken(paymentToken) {
		return new Promise(function(resolve, reject) {
			console.log('starting function sendPaymentToken()');
			console.log(paymentToken);
			
			/* Send Payment token to Payment Gateway, here its defaulting to True just to mock that part */
			
			returnFromGateway = createTransaction(paymentToken.paymentData);	
			console.log(returnFromGateway);
			console.log("defaulting to successful payment by the Token");

			if ( returnFromGateway == true ) {
				resolve(true);
			    review.save();
			} else {
				reject;
			}
		});
	}

	
	Allure.ApplePay.session.oncancel = function(event) {
		console.log('starting session.cancel');
		console.log(event);
	}
	
	Allure.ApplePay.session.begin();

}