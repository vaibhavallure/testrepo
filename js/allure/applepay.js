if ( typeof Allure == 'undefined' ) var Allure = {};
if ( typeof Allure.ApplePay == 'undefined' ) Allure.ApplePay = {};
if ( typeof Allure.ApplePay.flag == 'undefined' ) Allure.ApplePay.flag = {};
if ( typeof Allure.ApplePay.data == 'undefined' ) Allure.ApplePay.data = {};

Allure.ApplePay.flag.available = false;

Allure.ApplePay.flag.enabled = false;

if (window.ApplePaySession) {

	Allure.ApplePay.flag.available = true;
	
	Allure.ApplePay.flag.enabled = false;

	Allure.ApplePay.session = null;
	
	Allure.ApplePay.action = {};
	
	Allure.ApplePay.event = {};
	
	Allure.ApplePay.data.request = {
	  	countryCode: 'US',
	  	currencyCode: 'USD',
	  	supportedNetworks: ['visa', 'masterCard', 'amex', 'discover'],
	  	merchantCapabilities: ['supports3DS','supportsCredit', 'supportsDebit'], // Make sure NOT to include supportsEMV here
	  	total: function(){
	  		return { label: 'Maria Tash', amount: Allure.ApplePay.data.lineTotal };
	  	},
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
	
	Allure.ApplePay.data.merchantId = 'merchant.com.mariatash.authorizenet';
	
	var promise = ApplePaySession.canMakePaymentsWithActiveCard(Allure.ApplePay.data.merchantId);
   
	promise.then ( function (canMakePayments) {
      
		if (canMakePayments) {
		   
			console.log("Apple Pay Payment Available");

			Allure.ApplePay.flag.enabled = true;
			
	      	jQuery("#applePayButton").prop('disabled', false);
		} else {

			Allure.ApplePay.flag.enabled = false;
			
		   	console.log("Apple Pay is available but not activated yet");
		}
	});
	
	Allure.ApplePay.event.onValidateMerchant = function (event) {
		console.log('START EVENT: onValidateMerchant');
		console.log(event);
		console.log('START ACTION: performValidation');
		var promise = Allure.ApplePay.action.performValidation(event.validationURL);
		console.log('END ACTION: performValidation');
		promise.then(function (merchantSession) {
			console.log('START ACTION: completeMerchantValidation');
			Allure.ApplePay.session.completeMerchantValidation(merchantSession);
			console.log('END ACTION: completeMerchantValidation');
		}); 
		console.log('END EVENT: onValidateMerchant');
	};
	
	Allure.ApplePay.event.onPaymentMethodSelected = function(event) {
		console.log('START EVENT: onPaymentMethodSelected');
		console.log(event);
		var newTotal = { type: 'final', label: 'Maria Tash', amount: Allure.ApplePay.data.cartyResponse.total };
		var newLineItems = [{type: 'final', label: 'Spice #202', amount: '15.00' }]
		Allure.ApplePay.session.completePaymentMethodSelection( {newTotal : newTotal, newLineItems: Allure.ApplePay.data.lineItems});//, Allure.ApplePay.data.lineItems

		console.log('END EVENT: onPaymentMethodSelected');
	};
	
	Allure.ApplePay.event.onPaymentAuthorized = function (event) {
		console.log('START EVENT: onPaymentAuthorized');
		console.log(event);
		console.log('START ACTION: sendPaymentToken');
		var promise = Allure.ApplePay.action.sendPaymentToken(event.payment.token);
		console.log('END ACTION: sendPaymentToken');
		promise.then(function (success) {	
			var status;
			if (success) {
				status = ApplePaySession.STATUS_SUCCESS;
				console.log('Apple Pay Payment SUCCESS ');
			} else {
				status = ApplePaySession.STATUS_FAILURE;
			}		
			console.log( "result of sendPaymentToken() function =  " + success );

			console.log('START ACTION: completePayment');
			Allure.ApplePay.session.completePayment(status);
			console.log('END ACTION: completePayment');
		});
		console.log('END EVENT: onPaymentAuthorized');
	};
	
	Allure.ApplePay.event.onShippingContactSelected = function(event) {
		console.log('START EVENT: onShippingContactSelected');
		console.log(event);

		console.log('END EVENT: onShippingContactSelected');
	};
	
	Allure.ApplePay.event.onShippingMethodSelected = function(event) {
		console.log('START EVENT: onShippingMethodSelected');
		console.log(event);
		console.log('END EVENT: onShippingMethodSelected');
	};
	
	Allure.ApplePay.event.onCancel = function(event) {
		console.log('START EVENT: onCancel');
		console.log(event);
		console.log('END EVENT: onCancel');
	};
	

	Allure.ApplePay.action.init = function(){

		console.log('Apple Pay Initiated');
		
		switch(Allure.ApplePay.data.checkoutType) {
			case 'ApplePayButtonProduct':
				break;
			case 'ApplePayButtonCart':
				break;
			case 'ApplePayButtonCheckout':
				break;
		}
		
		//try {
			if (typeof Allure.ApplePay.data.lineTotal == "undefined") Allure.ApplePay.data.lineTotal = 1;
			
			Allure.ApplePay.data.request = Allure.ApplePay.action.prepareRequest({});
			
			Allure.ApplePay.session = new ApplePaySession(3, Allure.ApplePay.data.request);
	
			Allure.ApplePay.session.onvalidatemerchant = Allure.ApplePay.event.onValidateMerchant;
	
			Allure.ApplePay.session.onpaymentmethodselected = Allure.ApplePay.event.onPaymentMethodSelected
	
			Allure.ApplePay.session.onpaymentauthorized = Allure.ApplePay.event.onPaymentAuthorized;
			
			Allure.ApplePay.session.onshippingcontactselected = Allure.ApplePay.event.onShippingContactSelected;
			
			Allure.ApplePay.session.onshippingmethodselected = Allure.ApplePay.event.onShippingMethodSelected;
			
			Allure.ApplePay.session.oncancel = Allure.ApplePay.event.onCancel;
			
			Allure.ApplePay.session.begin();
		//} catch (e) {
		//	console.log(e);
		//}

	};
	
	Allure.ApplePay.action.performValidation = function (valURL) {
		return new Promise(function(resolve, reject) {
			var xhr = new XMLHttpRequest();
			xhr.onload = function() {
				console.log(this.responseText);
				var data = JSON.parse(this.responseText);
				console.log(data);
				resolve(data);
			};
			xhr.onerror = reject;
			xhr.open('POST', Allure.ApplePay.data.baseUrl+'validateMerchant', true);
			xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhr.send('validationUrl='+valURL);
		});
	};
	
	Allure.ApplePay.action.prepareRequest = function(requestData) {
		var requestData = {
			  	countryCode: 'US',
			  	currencyCode: 'USD',
			  	supportedNetworks: ['visa', 'masterCard', 'amex', 'discover'],
			  	merchantCapabilities: ['supports3DS','supportsCredit', 'supportsDebit'], // Make sure NOT to include supportsEMV here
			  	total: { label: 'Maria Tash', amount: Allure.ApplePay.data.lineTotal },
			  	shippingMethods: Allure.ApplePay.data.request.shippingMethods,
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
		
		if (typeof Allure.ApplePay.data.shippingAddress != 'undefined') {
			requestData.shippingContact = {
					givenName : Allure.ApplePay.data.shippingAddress.firstname,
					familyName : Allure.ApplePay.data.shippingAddress.lastname,
					emailAddress : Allure.ApplePay.data.shippingAddress.email,
					phoneNumber : Allure.ApplePay.data.shippingAddress.telephone,
					addressLines : [
						Allure.ApplePay.data.shippingAddress.street,
					],
					locality : Allure.ApplePay.data.shippingAddress.city,
					//subAdministrativeArea: '',
					administrativeArea: Allure.ApplePay.data.shippingAddress.region,
					postalCode: Allure.ApplePay.data.shippingAddress.postcode,
					//country: '',
					countryCode: Allure.ApplePay.data.shippingAddress.country_id
			};
		}
		
		if (typeof Allure.ApplePay.data.billingAddress != 'undefined' && Allure.ApplePay.data.checkoutType != 'ApplePayButtonCheckout') {
			requestData.billingContact = {
					givenName : Allure.ApplePay.data.billingAddress.firstname,
					familyName : Allure.ApplePay.data.billingAddress.lastname,
					emailAddress : Allure.ApplePay.data.billingAddress.email,
					phoneNumber : Allure.ApplePay.data.billingAddress.telephone,
					addressLines : [
						Allure.ApplePay.data.billingAddress.street,
					],
					locality : Allure.ApplePay.data.billingAddress.city,
					//subAdministrativeArea: '',
					administrativeArea: Allure.ApplePay.data.billingAddress.region,
					postalCode: Allure.ApplePay.data.billingAddress.postcode,
					//country: '',
					countryCode: Allure.ApplePay.data.billingAddress.country_id
			};
		}
		
		if (Allure.ApplePay.data.checkoutType  != 'undefined' && Allure.ApplePay.data.checkoutType == 'ApplePayButtonProduct') {
			
			Allure.ApplePay.data.cartyResponse = Allure.ApplePay.action.addProductToCart(jQuery('#pid-hidden').val(), jQuery('#qty').val());
			
			requestData.total = { label: 'Maria Tash', amount: Allure.ApplePay.data.cartyResponse.total };
		}
		
		return requestData;
	}
	
	Allure.ApplePay.action.addProductToCart = function (product, qty) {
		var cartResponse = {};
		jQuery.ajax({
			url: 	Allure.ApplePay.data.baseUrl+'addProduct',
			async: 	false,
			dataType: 'json',
			data: 	{
				product: product, 
				qty: qty
			},
			method: 	'POST',
			timeout:	5000
			
		}).done(function(data){
			console.log(data);
			cartResponse = data;
			console.log('Success');
			
		}).fail(function() {
			console.log('Error');
		})
		
		return cartResponse;
	};
	
	Allure.ApplePay.action.createTransaction = function(dataObj) {
		
		console.log('starting createTransaction');
		console.log(dataObj);
		
		let objJsonStr = JSON.stringify(dataObj);
		//console.log(objJsonStr);
	    let objJsonB64 = window.btoa(objJsonStr);
		//console.log(objJsonB64);
	    
		jQuery.ajax({
			url: Allure.ApplePay.data.baseUrl+'saveTransaction',
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
	};

	Allure.ApplePay.action.sendPaymentToken = function (paymentToken) {
		return new Promise(function(resolve, reject) {
			console.log('starting function sendPaymentToken()');
			console.log(paymentToken);
			
			/* Send Payment token to Payment Gateway, here its defaulting to True just to mock that part */
			
			returnFromGateway = Allure.ApplePay.action.createTransaction(paymentToken.paymentData);	
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
} else {
	Allure.ApplePay.data.available = false;
	console.log("Apple Pay not available in this browser");
}