var country_code;

jQuery(document).ready(function () {

    jQuery('.minusBtn').on('click',function(){
        if(jQuery('#customer_info_div').length !== 0 && jQuery('#customer_info_div').length !== null) {
            var cnt = jQuery('#count').val();
            cnt++;
            if(cnt > 1) {
                console.log('Remove');
                console.log(cnt);
                removeCustomer(cnt);
            }
        }
    });

    jQuery('.plusBtn').on('click',function(){
        if(jQuery('#customer_info_div').length !== 0 && jQuery('#customer_info_div').length !== null) {
            var cnt = jQuery('#count').val();
            if(cnt > 1) {
                addCustomer(cnt);
            }
        }
    })

    telephoneAdd('#phone_1');



});

var telephoneAdd = function (id) {


    Allure.UtilPath = "js/phone_validation/utils.js";

    //initialize itel tel input
    var input = document.querySelector(id)
    var iti = window.intlTelInput(input, {
        autoFormat: false,
        autoHideDialCode: false,
        autoPlaceholder: false,
        nationalMode: false,
        utilsScript: Allure.UtilPath
    });

    // take phone number and check if it exist
    // if it does not exist then
    // first check if we can get Geo Location based Country Code if its there then set it
    // otherwise set default as country as 'us'
    // Magento module - Allure/GeoLocation
    var phone = jQuery(id).val();
    //console.log('[signing_register.phtml] length',phone.length)
    if(phone.length <= 0){

        if (country_code == undefined || country_code == null || country_code == "") {
            country_code = "us"
        }

        iti.setCountry(country_code.toLowerCase());
    } else if (phone.charAt(0) != '+') {
        iti.setCountry(country_code.toLowerCase());
    }

    //custom validator for itel tel input
    //FileName - intel-tel-validation.js
    allureIntlTelValidate(jQuery(id),iti);
}
var removeCustomer = function (id) {
    jQuery('#customer_info_'+id).remove();
}

var addCustomer = function (id) {
    var customerDiv =    `<div id="customer_info_${id}">
    <h6>Customer: ${id}</h6>
<div class="fieldDiv">
<label>First Name*</label><br/>
<input class="ele_width apt_firstname required-entry" type="text" name="customer[${id}][firstname]" id="firstname_${id}" placeholder="First Name*" value="">
    </div>
    <div class="fieldDiv">
<label>Last Name*:</label><br/>
<input class="ele_width apt_lastname required-entry" type="text" name="customer[${id}][lastname]" id="lastname_${id}" placeholder="Last Name*" value="">
    </div>
    <div class="fieldDiv">
<label>Email*:</label><br/>
<input class="ele_width apt_email required-entry" type="text" name="customer[${id}][email]" id="email_${id}" placeholder="Email" value="">
    </div>
    <div class="fieldDiv">
<label>Phone*:</label><br/>
<input class="ele_width apt_phone required-entry" type="text" name="customer[${id}][phone]" id="phone_${id}" placeholder="Phone" value="">
    </div>

    <div class="fieldDiv">
<label>Notify Me:</label><br/>
<div><input type="checkbox" name="customer[${id}][noti_email]" title="" value="1" class="notifyRadio" checked><p>By Mail</p></div>
<div><input type="checkbox" name="customer[${id}][noti_sms]" title="" value="2" class="notifyRadio" ><p>By Text Message - Message and Data Rates May Apply</p></div>
</div>
</div>`;


    if(jQuery('#customer_info_'+id).length === 0 ) {
        jQuery('#customer_info_div').append(customerDiv);
        telephoneAdd('#phone_'+id);
    }
}