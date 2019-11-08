function getCardType(cardNum) {

    /*if(!luhnCheck(cardNum)){
    	return "";
    }*/
    var payCardType = "";
    var regexMap = [
      {regEx: /^4[0-9]{12}(?:[0-9]{3})?$/,cardType: "VI"},
      {regEx: /^(5[1-5][0-9]{14}|2221[0-9]{12}|222[2-9][0-9]{12}|22[3-9][0-9]{13}|2[3-6][0-9]{14}|27[01][0-9]{13}|2720[0-9]{12})$/,cardType: "MC"},
      {regEx: /^3[47][0-9]{13}$/,cardType: "AE"},
      {regEx:/^6(?:011|5[0-9]{2}|4[4-9][0-9]{1}|(22(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[01][0-9]|92[0-5]$)[0-9]{10}$))[0-9]{12}$/,cardType:"DI"}
    ];
    
    for (var j = 0; j < regexMap.length; j++) {
      if (cardNum.match(regexMap[j].regEx)) {
        payCardType = regexMap[j].cardType;
        break;
      }
    }
    
    return payCardType;

    if (cardNum.indexOf("50") === 0 || cardNum.indexOf("60") === 0 || cardNum.indexOf("65") === 0) {
      var g = "508500-508999|606985-607984|608001-608500|652150-653149";
      var i = g.split("|");
      for (var d = 0; d < i.length; d++) {
        var c = parseInt(i[d].split("-")[0], 10);
        var f = parseInt(i[d].split("-")[1], 10);
        if ((cardNum.substr(0, 6) >= c && cardNum.substr(0, 6) <= f) && cardNum.length >= 6) {
         payCardType = "RUPAY";
          break;
        }
      }
    }
    return payCardType;
	
}

function luhnCheck(cardNum){
    // Luhn Check Code from https://gist.github.com/4075533
    // accept only digits, dashes or spaces
    var numericDashRegex = /^[\d\-\s]+$/
    if (!numericDashRegex.test(cardNum)) return false;

    // The Luhn Algorithm. It's so pretty.
    var nCheck = 0, nDigit = 0, bEven = false;
    var strippedField = cardNum.replace(/\D/g, "");

    for (var n = strippedField.length - 1; n >= 0; n--) {
        var cDigit = strippedField.charAt(n);
        nDigit = parseInt(cDigit, 10);
        if (bEven) {
            if ((nDigit *= 2) > 9) nDigit -= 9;
        }

        nCheck += nDigit;
        bEven = !bEven;
    }

  	return (nCheck % 10) === 0;
}

jQuery(document).ready(function(){
	jQuery(document).on('keyup blur','input[name="payment[cc_number]"]',function() {
		var num = jQuery(this).val();
		var cardType = getCardType(num);
		jQuery('select[name="payment[cc_type]"]').val(cardType);
	});
}); 