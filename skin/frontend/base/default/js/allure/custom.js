
var isConvertFname = true;
var isConvertLname = true;
jQuery( document ).ready(function() {

    /*CONVERT FIRSTNAME AND LASTNAME IN CAPITAL LETTERS*/
    jQuery('input[name="firstname"], input[name="billing[firstname]"], input[name="shipping[firstname]"]').keyup(function (evt) {
       convert(jQuery(this),'firstname',evt);
    });
    jQuery('input[name="mail_to"],input[name="mail_from"],input[name="lastname"],input[name="billing[lastname]"],input[name="shipping[lastname]"]').keyup(function (evt) {
        convert(jQuery(this),'lastname',evt);
    });

});

var convert = function (element,convertFlag,evt) {
    var cp_value = element.val();

    /*KEY CODE
    * 8 = backspace
    * 46 = delete
    * */
    var flag = isConvertLname;
    if(convertFlag === 'firstname') {
    flag = isConvertFname;
    }
    if (((evt.keyCode !== 8) && (evt.keyCode !== 46)) && (flag)) {
        if (cp_value.length > 0) {
            cp_value = ucfirst(cp_value, true);
            element.val(cp_value);
        }
    } else {
        if (cp_value.length === 0) {
            if(convertFlag === 'firstname') {
                isConvertFname = true;
            }else{
                isConvertLname = true;
            }
        } else {
            if(convertFlag === 'firstname') {
                isConvertFname = false;
            }else{
                isConvertLname = false;
            }
        }
    }
}
// to capitalize first letter
var ucfirst = function(str,force){
    str=force ? str.toLowerCase() : str;
    return str.replace(/(\b)([a-zA-Z])/,
        function(firstLetter){
            return   firstLetter.toUpperCase();
        });
}
// to capitalize all words
var ucwords = function(str,force){
    str=force ? str.toLowerCase() : str;
    return str.replace(/(\b)([a-zA-Z])/g,
        function(firstLetter){
            return   firstLetter.toUpperCase();
        });
}