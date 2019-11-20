if ( typeof Allure == 'undefined' ) var Allure = {};
if ( typeof Allure.ApplePay == 'undefined' ) Allure.ApplePay = {};
if ( typeof Allure.ApplePay.flag == 'undefined' ) Allure.ApplePay.flag = {};
if ( typeof Allure.ApplePay.data == 'undefined' ) Allure.ApplePay.data = {};


Allure.ApplePay.flag.available = false;

Allure.ApplePay.flag.enabled = false;

Allure.ApplePay.flag.active = false;

Allure.ApplePay.flag.sandbox = false;

if (window.ApplePaySession) {

	Allure.ApplePay.flag.available = true;

	Allure.ApplePay.flag.enabled = false;

	Allure.ApplePay.session = null;

	Allure.ApplePay.action = {};

	Allure.ApplePay.event = {};

	if (typeof Allure.ApplePay.data.response == 'undefined')
		Allure.ApplePay.data.response = {};
	if (typeof Allure.ApplePay.data.total == 'undefined')
		Allure.ApplePay.data.total = { type: 'final', label: 'Maria Tash', amount: 0.00};
	if (typeof Allure.ApplePay.data.lineItems == 'undefined')
		Allure.ApplePay.data.lineItems = [];
	if (typeof Allure.ApplePay.data.shippingMethods == 'undefined')
		Allure.ApplePay.data.shippingMethods = [];
	if (typeof Allure.ApplePay.data.currencyCode == 'undefined')
		Allure.ApplePay.data.currencyCode = 'USD';

	Allure.ApplePay.data.request = {
	  	countryCode: 'US',
	  	currencyCode: 'USD', //Allure.ApplePay.data.currencyCode,
	  	supportedNetworks: ['visa', 'masterCard', 'amex', 'discover'],
	  	merchantCapabilities: ['supports3DS','supportsCredit', 'supportsDebit'], // Make sure NOT to include supportsEMV here
	  	total: function(){
	  		return { label: 'Maria Tash', amount: Allure.ApplePay.data.lineTotal };
	  	},
	  	shippingMethods: [{
		    "label": "International Flat Rate",
		    "detail": "Arrives in 5 to 7 days",
		    "amount": 80,
		    "identifier": "flatrate_flatrate"
		}],
		shippingType: 'delivery',
		requiredBillingContactFields: [
			//"givenName",
			//"familyName",
			"postalAddress"
		],
		requiredShippingContactFields: [
		    "postalAddress",
		    "name",
		    "phone",
		    "email"
		]
	};

	jQuery.ajaxSetup({timeout: 0});

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

			if (typeof ga == "function") {
				ga('ec:setAction', 'checkout', {
					'step': 1
				});
				ga('send', 'pageview');
			}

			console.log('END ACTION: completeMerchantValidation');
		});
		console.log('END EVENT: onValidateMerchant');
	};

	Allure.ApplePay.event.onPaymentMethodSelected = function(event) {
		console.log('START EVENT: onPaymentMethodSelected');
		console.log(event);
		var newTotal = Allure.ApplePay.data.total;
		var newLineItems = Allure.ApplePay.data.lineItems;

		console.log({newTotal : newTotal, newLineItems: newLineItems});

		if (typeof ga == "function") {
			ga('ec:setAction', 'checkout', {
				'step': 4,
				'option': 'Apple Pay'
			});
			ga('send', 'pageview');
		}

		Allure.ApplePay.session.completePaymentMethodSelection( {newTotal : newTotal, newLineItems: newLineItems});//, Allure.ApplePay.data.lineItems

		console.log('END EVENT: onPaymentMethodSelected');
	};

	Allure.ApplePay.event.onPaymentAuthorized = function (event) {
		console.log('START EVENT: onPaymentAuthorized');
		console.log(event);

		var shippingContact = event.payment.shippingContact;

        /*validation code for last name---*/
        if(shippingContact.familyName=="") {
            Allure.ApplePay.session.abort();
            alert('Error: Last name is required');
            Allure.ApplePay.flag.active = false;
            return false;
        }
        /*----validation code ended-------*/

		console.log('START ACTION: sendPaymentToken');
		var promise = Allure.ApplePay.action.sendPaymentToken(event.payment.token, shippingContact);
		console.log('END ACTION: sendPaymentToken');

		Allure.ApplePay.flag.lastStatus = false;

		promise.then(function (success) {
			var status;
			if (success) {
				status = ApplePaySession.STATUS_SUCCESS;
				console.log('Apple Pay Payment SUCCESS ');
				Allure.ApplePay.flag.lastStatus = true;
			} else {
				status = ApplePaySession.STATUS_FAILURE;
			}

			console.log( "sendPaymentToken =  " + success );

			console.log('START ACTION: completePayment');
			Allure.ApplePay.session.completePayment(status);
			console.log('END ACTION: completePayment');

			if (success) {

				Allure.ApplePay.modal.modal('hide');

				location.href = '/checkout/onepage/success';
			}

			Allure.ApplePay.flag.active = false;
		});

		console.log('END EVENT: onPaymentAuthorized');
	};

	Allure.ApplePay.event.onShippingContactSelected = function(event) {
		console.log('START EVENT: onShippingContactSelected');
		console.log(event);

		var shippingContact = event.shippingContact;

		var quote_id = null;

		if (typeof Allure.ApplePay.data.response.addProduct != 'undefined') {
			quote_id = Allure.ApplePay.data.response.addProduct.quote_id;
		}

		if (typeof ga == "function") {
			ga('ec:setAction', 'checkout', {
				'step': 2
			});
			ga('send', 'pageview');
		}

		Allure.ApplePay.data.response.saveBilling = Allure.ApplePay.action.sendRequest('saveBilling', {
			'billing[firstname]': 'ApplePay',
			'billing[lastname]': 'Customer',
			'billing[company]': '',
			'billing[email]':'applepay@mariatash.com',
			'billing[country_id]': shippingContact.countryCode,
			'billing[street][0]': 'ApplePay',
			'billing[street][1]': '',
			'billing[city]': shippingContact.locality,
			'billing[region_id]': '',
			'billing[region]': shippingContact.administrativeArea,
			'billing[postcode]': shippingContact.postalCode,
			'billing[telephone]': '9999999999',
			'billing[fax]': '',
			'billing[use_for_shipping]': 1
		});

		//Allure.ApplePay.data.response.shippingMethods = Allure.ApplePay.action.sendRequest('loadShippingMethods');

		if (Allure.ApplePay.data.response.saveBilling) {
			if (typeof Allure.ApplePay.data.response.saveBilling.shipping_methods != 'undefined') {
				Allure.ApplePay.data.shippingMethods = [];
				jQuery.each(Allure.ApplePay.data.response.saveBilling.shipping_methods, function(shippingCode, shippingData){
						Allure.ApplePay.data.shippingMethods.push({
							identifier: shippingCode,
							label: shippingData.method_title,
							detail: shippingData.method_title+(shippingData.method_description ? ' - '+shippingData.method_description : ''),
							amount: shippingData.price
					});
				});
			}

			if (typeof Allure.ApplePay.data.response.saveBilling.totals != 'undefined') {
				Allure.ApplePay.action.refreshLineItems(Allure.ApplePay.data.response.saveBilling.totals);
			} else {
				var totalData = Allure.ApplePay.action.sendRequest('refreshTotals');

				Allure.ApplePay.action.refreshLineItems(totalData);

				console.log('totalData::');
				console.log(totalData);
			}

			if (typeof Allure.ApplePay.data.response.saveBilling.currency != 'undefined') {
				Allure.ApplePay.data.currencyCode = Allure.ApplePay.data.response.saveBilling.currency;
			}
		}

		/*Allure.ApplePay.action.sendRequest('saveShipping', {
			'shipping[firstname]': 'ApplePay',
			'shipping[lastname]': 'Customer',
			'shipping[company]': '',
			'shipping[email]':'applepay@mariatash.com',
			'shipping[country_id]': shippingContact.countryCode,
			'shipping[street][0]': 'ApplePay',
			'shipping[street][1]': '',
			'shipping[city]': shippingContact.locality,
			'shipping[region_id]': '',
			'shipping[region]': shippingContact.administrativeArea,
			'shipping[postcode]': shippingContact.postalCode,
			'shipping[telephone]': '9999999999',
			'shipping[fax]': ''
		});*/

		var newTotal = Allure.ApplePay.data.total;
		var newLineItems = Allure.ApplePay.data.lineItems;

		console.log({newTotal : newTotal, newLineItems: newLineItems});

		Allure.ApplePay.session.completeShippingContactSelection( {newShippingMethods: Allure.ApplePay.data.shippingMethods, newTotal : newTotal, newLineItems: newLineItems});//, Allure.ApplePay.data.lineItems

		console.log('END EVENT: onShippingContactSelected');
	};

	Allure.ApplePay.event.onShippingMethodSelected = function(event) {
		console.log('START EVENT: onShippingMethodSelected');
		console.log(event);

		if (typeof ga == "function") {
			ga('ec:setAction', 'checkout', {
				'step': 3,
				'option': event.shippingMethod.identifier
			});

			ga('send', 'pageview');
		}

		if (event.shippingMethod) {
			Allure.ApplePay.action.saveShippingMethod(event.shippingMethod.identifier);
		}

		var newTotal = Allure.ApplePay.data.total;
		var newLineItems = Allure.ApplePay.data.lineItems;

		console.log({newTotal : newTotal, newLineItems: newLineItems});

		Allure.ApplePay.session.completeShippingMethodSelection( { newTotal : newTotal, newLineItems: newLineItems});//, Allure.ApplePay.data.lineItems

		console.log('END EVENT: onShippingMethodSelected');
	};

	Allure.ApplePay.event.onCancel = function(event) {
		console.log('START EVENT: onCancel');
		console.log(event);
		console.log('END EVENT: onCancel');

		Allure.ApplePay.flag.active = false;
	};


	Allure.ApplePay.action.initProduct = function() {
		Allure.ApplePay.modal.data('type','product')
		Allure.ApplePay.modal.attr('data-type','product')

		if (typeof Allure.ApplePay.data.response.addProduct == 'undefined' || !Allure.ApplePay.data.response.addProduct) {
			Allure.ApplePay.action.addProduct();
		}

		if (typeof Allure.ApplePay.flag.addingProduct == 'undefined') {
			Allure.ApplePay.flag.addingProduct = false;
		}

		if (!Allure.ApplePay.flag.addingProduct) {
			Allure.ApplePay.flag.addingProduct = true;
			Allure.ApplePay.action.addProduct();
			Allure.ApplePay.flag.addingProduct = false;
		}

		if (!Allure.ApplePay.flag.addingProduct) {
			Allure.ApplePay.action.init();
		}
		//Allure.ApplePay.modal.modal('show');
		return false;
	};

	Allure.ApplePay.action.init = function() {

		if (Allure.ApplePay.flag.active) {
			return false;
		} else {
			Allure.ApplePay.flag.active = true;
		}

		if (typeof ga == "function") {
			var gAction = 'INIT';
			ga('send', 'event', 'ApplePay', gAction, location.hostname + ' / ' + Allure.ApplePay.data.checkoutType + ' / ' + gAction);
		}

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

			if (typeof Allure.ApplePay.data.lineTotal == 'undefined' || Allure.ApplePay.data.lineTotal == null) {
				console.log('Error: No Product Selected');
				alert('Error: No Product Selected');

				Allure.ApplePay.flag.active = false;
				return false;
			}

			Allure.ApplePay.session = new ApplePaySession(3, Allure.ApplePay.data.request);

			Allure.ApplePay.session.onvalidatemerchant = Allure.ApplePay.event.onValidateMerchant;

			Allure.ApplePay.session.onpaymentmethodselected = Allure.ApplePay.event.onPaymentMethodSelected;

			Allure.ApplePay.session.onshippingcontactselected = Allure.ApplePay.event.onShippingContactSelected;

			Allure.ApplePay.session.onshippingmethodselected = Allure.ApplePay.event.onShippingMethodSelected;

			Allure.ApplePay.session.onpaymentauthorized = Allure.ApplePay.event.onPaymentAuthorized;

			Allure.ApplePay.session.oncancel = Allure.ApplePay.event.onCancel;

			Allure.ApplePay.session.begin();
		//} catch (e) {
		//	console.log(e);
		//}

		return false;

	};

	Allure.ApplePay.action.performValidation = function (valURL) {

		return new Promise(function(resolve, reject) {
			var xhr = new XMLHttpRequest();
			xhr.onload = function() {
				//console.log(this.responseText);
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
			  	currencyCode: 'USD',//(Allure.ApplePay.flag.sandbox ? 'USD' : Allure.ApplePay.data.currencyCode),
			  	supportedNetworks: ['visa', 'masterCard', 'amex', 'discover'],
			  	merchantCapabilities: ['supports3DS','supportsCredit', 'supportsDebit'], // Make sure NOT to include supportsEMV here
			  	total: { label: Allure.ApplePay.data.merchantName, amount: (Allure.ApplePay.flag.sandbox ? 1 : Allure.ApplePay.data.total.amount) },
			  	shippingMethods: Allure.ApplePay.data.shippingMethods,
				requiredShippingContactFields: [
				    "postalAddress",
				    "name",
				    "phone",
				    "email"
				]
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

			if (typeof Allure.ApplePay.data.response.addProduct == 'undefined' || !Allure.ApplePay.data.response.addProduct) {
				Allure.ApplePay.action.addProduct();
			}
		}

		return requestData;
	}

	Allure.ApplePay.action.addProduct = function () {

		if (typeof ga == "function") {
			var gAction = 'addProduct';
			ga('send', 'event', 'ApplePay', gAction, location.hostname + ' / ' + Allure.ApplePay.data.checkoutType + ' / ' + gAction);
		}

		if (jQuery('#product_addtocart_giftcard_form').length) {
			if (!productAddToCartForm.validator.validate()) {
				return;
			}
			Allure.ApplePay.data.response.addProduct = Allure.ApplePay.action.sendRequest('addProduct', jQuery('#product_addtocart_giftcard_form').serialize())
		} else {
			Allure.ApplePay.data.response.addProduct = Allure.ApplePay.action.sendRequest('addProduct', jQuery('#qty').parents('form').serialize());
		}

		console.log(Allure.ApplePay.data.response.addProduct);

		if (Allure.ApplePay.data.response.addProduct) {
			if (typeof Allure.ApplePay.data.response.addProduct.total != 'undefined') {
				Allure.ApplePay.data.lineTotal = Allure.ApplePay.data.response.addProduct.total;

				if (!Allure.ApplePay.flag.sandbox) {
					Allure.ApplePay.data.total.amount = Allure.ApplePay.data.lineTotal;
				} else {
					Allure.ApplePay.data.total.amount = 1;
				}

				Allure.ApplePay.data.lineItems = [{type: 'final', label: 'Sub Total', amount: Allure.ApplePay.data.lineTotal }];
			}

			if (typeof Allure.ApplePay.data.response.addProduct.currency != 'undefined') {
				Allure.ApplePay.data.currencyCode = Allure.ApplePay.data.response.addProduct.currency;
			}

//			if (typeof Allure.ApplePay.data.response.addProduct.totals != 'undefined') {
//				Allure.ApplePay.data.lineItems = [];
//
//				jQuery.each(Allure.ApplePay.data.response.addProduct.totals, function(totalCode, totalData){
//					if (totalCode == 'grand_total') {
//						Allure.ApplePay.data.total.amount = totalData.value;
//						return;
//					}
//
//					Allure.ApplePay.data.lineItems.push({
//						    label: totalData.title,
//						    amount: totalData.value,
//						    type: "final"
//					});
//				});
//			}
			jQuery('#applepay-discount-coupon-form #coupon_code2').removeAttr('readonly');
			jQuery('#applepay-discount-coupon-form #coupon_code2').val('');
			jQuery('#applepay-discount-coupon-form #coupon_code2').attr('data-action','apply').data('action','apply');
			jQuery('#applepay-btn-coupon span span').text('Apply');
		}
	};

	Allure.ApplePay.action.sendRequest = function (requestType, requestData, requestCallback) {
		console.log('sendRequest START::'+requestType);
		var responseData = null;
		jQuery.ajax({
			url: 		Allure.ApplePay.data.baseUrl+requestType,
			async: 		false,
			cache: 		false,
			method: 	'POST',
			dataType: 	'json',
			data: 		requestData,
			xhrFields: {
				withCredentials: true
			},
			timeout: 30000 // 20 seconds

		}).done(function(data){
			responseData = data;
			console.log(requestType+'::DONE');
			console.log(data);

			if (typeof requestCallback != 'undefined') {
				requestCallback();
			}
			console.log('sendRequest END::'+requestType);

		}).fail(function(xhr, status, error) {
			Allure.ApplePay.flag.active = false;
			console.log(requestType+'::FAIL => '+error);
			console.log('sendRequest END::'+requestType);
		})

		return responseData;
	};

	Allure.ApplePay.action.createTransaction = function(dataObj) {

		if (typeof ga == "function") {
			var gAction = 'createTransaction';
			ga('send', 'event', 'ApplePay', gAction, location.hostname + ' / ' + Allure.ApplePay.data.checkoutType + ' / ' + gAction);
		}

		console.log('starting createTransaction');
		console.log(dataObj);

		let objJsonStr = JSON.stringify(dataObj);
		//console.log(objJsonStr);
	    let objJsonB64 = window.btoa(objJsonStr);
		//console.log(objJsonB64);

	    var status = false;

	    var shippingContact = Allure.ApplePay.data.shippingContact;

	    var data = {
//			'billing[firstname]': shippingContact.givenName,
//			'billing[lastname]': shippingContact.familyName,
//			'billing[company]': '',
//			'billing[email]': shippingContact.emailAddress,
//			'billing[country_id]': shippingContact.countryCode,
//			'billing[street][0]': shippingContact.addressLines[0],
//			'billing[street][1]': (shippingContact.addressLines.length > 1 ? shippingContact.addressLines[1] : ''),
//			'billing[city]': shippingContact.locality,
//			'billing[region_id]': '',
//			'billing[region]': shippingContact.administrativeArea,
//			'billing[postcode]': shippingContact.postalCode,
//			'billing[telephone]': shippingContact.phoneNumber,
//			'billing[fax]': '',
//			'billing[use_for_shipping]': 1,
    		amount: Allure.ApplePay.data.total.amount,
    		dataDesc: 'COMMON.APPLE.INAPP.PAYMENT',
    		dataValue: objJsonStr,
    		dataBinary: objJsonB64
	    };

		var response = Allure.ApplePay.action.sendRequest('saveApplePayTransaction', data);
	    //var response = Allure.ApplePay.action.sendRequest('saveOrderTransaction', data);
	    //var response = Allure.ApplePay.action.sendRequest('saveTransaction', data);

		console.log('saveTransaction response::');
		console.log(response);

	    status = response.success;

		console.log('TransactionStatus::'+status);

		return status;
	};

	Allure.ApplePay.action.sendPaymentToken = function (paymentToken, shippingContact) {

		if (typeof ga == "function") {
			var gAction = 'sendPaymentToken';
			ga('send', 'event', 'ApplePay', gAction, location.hostname + ' / ' + Allure.ApplePay.data.checkoutType + ' / ' + gAction);
		}

		Allure.ApplePay.data.shippingContact = shippingContact;

		Allure.ApplePay.data.response.saveBilling = Allure.ApplePay.action.sendRequest('saveBilling', {
			'billing[firstname]': shippingContact.givenName,
			'billing[lastname]': shippingContact.familyName,
			'billing[company]': '',
			'billing[email]': shippingContact.emailAddress,
			'billing[country_id]': shippingContact.countryCode,
			'billing[street][0]': shippingContact.addressLines[0],
			'billing[street][1]': (shippingContact.addressLines.length > 1 ? shippingContact.addressLines[1] : ''),
			'billing[city]': shippingContact.locality,
			'billing[region_id]': '',
			'billing[region]': shippingContact.administrativeArea,
			'billing[postcode]': shippingContact.postalCode,
			'billing[telephone]': shippingContact.phoneNumber,
			'billing[fax]': '',
			'billing[use_for_shipping]': 1
		});

		if (Allure.ApplePay.data.response.saveBilling) {
			if (typeof Allure.ApplePay.data.response.saveBilling.shipping_methods != 'undefined') {
				Allure.ApplePay.data.shippingMethods = [];
				jQuery.each(Allure.ApplePay.data.response.saveBilling.shipping_methods, function(shippingCode, shippingData){
						Allure.ApplePay.data.shippingMethods.push({
							identifier: shippingCode,
							label: shippingData.carrier_title+' / '+shippingData.method_title,
							detail: shippingData.method_title+(shippingData.method_description ? ' - '+shippingData.method_description : ''),
							amount: shippingData.price
					});
				});
			}

			if (typeof Allure.ApplePay.data.response.saveBilling.totals != 'undefined') {
				Allure.ApplePay.action.refreshLineItems(Allure.ApplePay.data.response.saveBilling.totals);
			} else {
				var totalData = Allure.ApplePay.action.sendRequest('refreshTotals');

				Allure.ApplePay.action.refreshLineItems(totalData);

				console.log('totalData::');
				console.log(totalData);
			}

			if (typeof Allure.ApplePay.data.response.saveBilling.currency != 'undefined') {
				Allure.ApplePay.data.currencyCode = Allure.ApplePay.data.response.saveBilling.currency;
			}
		}

		if (typeof ga == "function") {
			ga('ec:setAction', 'checkout', {
				'step': 5
			});
			ga('send', 'pageview');
		}

		return new Promise(function(resolve, reject) {
			console.log(paymentToken);

			/* Send Payment token to Payment Gateway, here its defaulting to True just to mock that part */

			returnFromGateway = Allure.ApplePay.action.createTransaction(paymentToken.paymentData);
			console.log(returnFromGateway);
			console.log("defaulting to successful payment by the Token");

			if ( returnFromGateway == true ) {
				resolve(true);
			    //review.save();
			} else {
				reject;
			}
		});
	};

	Allure.ApplePay.action.saveShippingMethod = function (shipping_method){
		Allure.ApplePay.data.response.saveShippingMethod = Allure.ApplePay.action.sendRequest('saveShippingMethod', {
			'shipping_method': shipping_method
		});

		if (Allure.ApplePay.data.response.saveShippingMethod) {
			if (typeof Allure.ApplePay.data.response.saveShippingMethod.totals != 'undefined') {
				Allure.ApplePay.action.refreshLineItems(Allure.ApplePay.data.response.saveShippingMethod.totals);
			}

			if (typeof Allure.ApplePay.data.response.saveShippingMethod.currency != 'undefined') {
				Allure.ApplePay.data.currencyCode = Allure.ApplePay.data.response.saveShippingMethod.currency;
			}
		}
	};

	Allure.ApplePay.action.refreshLineItems  = function (totals) {
		Allure.ApplePay.data.lineItems = [];
		jQuery.each(totals, function(totalCode, totalData){
			if (totalCode == 'grand_total') {
				if (!Allure.ApplePay.flag.sandbox) {
					Allure.ApplePay.data.total.amount = totalData.value;
				} else {
					Allure.ApplePay.data.total.amount = 1;
				}

				return;
			} else {
				Allure.ApplePay.data.total.amount = totalData.value;
			}

			Allure.ApplePay.data.lineItems.push({
				    label: totalData.title,
				    amount: totalData.value,
				    type: "final"
			});
		});
	};

	Allure.ApplePay.action.addGiftCard = function() {
		console.log(this);
		return false;
	};

	Allure.ApplePay.action.removeGiftCard = function(cardNumber) {
		console.log(cardNumber);
		return false;
	};

	Allure.ApplePay.action.toggleCouponCode = function () {

		console.log(jQuery('#applepay-btn-coupon'));

		if (ApplePayDiscountForm.validator.validate()) {

			Allure.ApplePay.data.response.applyCoupon = Allure.ApplePay.action.sendRequest('applyCoupon', {
				'coupon_code': jQuery('#applepay-discount-coupon-form #coupon_code2').val(),
				"action" : jQuery('#applepay-btn-coupon').data('action')
			});

			if (Allure.ApplePay.data.response.applyCoupon) {
				if (!Allure.ApplePay.data.response.applyCoupon.error && jQuery('#applepay-btn-coupon').data('action') == 'remove') {
					jQuery('#applepay-discount-coupon-form #coupon_code2').removeAttr('readonly');
					jQuery('#applepay-discount-coupon-form #coupon_code2').val('');
					jQuery('#applepay-discount-coupon-form #coupon_code2').attr('data-action','apply').data('action','apply');
					jQuery('#applepay-btn-coupon span span').text('Apply');
				}  else if (!Allure.ApplePay.data.response.applyCoupon.error) {
					jQuery('#applepay-discount-coupon-form #coupon_code2').attr('readonly','readonly');
					jQuery('#applepay-discount-coupon-form #coupon_code2').attr('data-action','remove').data('action','remove');
					jQuery('#applepay-btn-coupon span span').text('Remove');
				}
			}
		}

		return false;
	};
} else {
	Allure.ApplePay.data.available = false;
	console.log("Apple Pay not available in this browser");
}
