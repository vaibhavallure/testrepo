if (window.ApplePaySession) {
   var merchantIdentifier = 'merchant.com.mariatash.authorizenet';
   var promise = ApplePaySession.canMakePaymentsWithActiveCard(merchantIdentifier);
   promise.then( function (canMakePayments) {
      if (canMakePayments){
      	console.log("Apple Pay Payment Available");
      	jQuery("#applePayButton").prop('disabled', false);
      }else{
      	console.log("Apple Pay is available but not activated yet");
      }
	});
}
else{
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
		url: AllureApplePay.BaseUrl+'saveTransaction',
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
	
	AllureApplePay.session = new ApplePaySession(3, AllureApplePay.request);

	// Merchant Validation
	AllureApplePay.session.onvalidatemerchant = function (event) {
		console.log(event);
		var promise = performValidation(event.validationURL);
		promise.then(function (merchantSession) {
			AllureApplePay.session.completeMerchantValidation(merchantSession);
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
			xhr.open('POST', AllureApplePay.BaseUrl+'validateMerchant', true);
			xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhr.send('validationUrl='+valURL);
		});
	}

	AllureApplePay.session.onpaymentmethodselected = function(event) {
		console.log('starting onpaymentmethodselected');
		console.log(event);
		var newTotal = { type: 'final', label: 'Venus By Maria Tash', amount: AllureApplePay.lineTotal };
		var newLineItems =[{type: 'final',label: 'Spice #202', amount: '15.00' }]
		AllureApplePay.session.completePaymentMethodSelection( newTotal, AllureApplePay.lineItems);
	}

	AllureApplePay.session.onpaymentauthorized = function (event) {
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
			AllureApplePay.session.completePayment(status);
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

	
	AllureApplePay.session.oncancel = function(event) {
		console.log('starting session.cancel');
		console.log(event);
	}
	
	AllureApplePay.session.begin();

}