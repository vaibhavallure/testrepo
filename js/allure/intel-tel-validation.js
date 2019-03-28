/**
 * Custom validator for itel-tel-input library
 * For Vairan form validation
 * @param telInput selector
 * @param iti iti object
 */

function allureIntlTelValidate(telInput, iti) {
    Validation.add('validate-intl-telephone', 'Please enter a valid Telephone number', function (v) {

        if (jQuery.trim(telInput.val())) {              //trims input value
            if (iti.isValidNumber()) {                  //check if the telephone number is proper or not
                return true;
            } else {
                return false;
            }
        }
        return false;
    });
}