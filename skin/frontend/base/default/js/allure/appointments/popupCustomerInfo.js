;
jQuery(document).ready(function () {

    jQuery('.minusBtn').on('click',function(){
        if(jQuery('#customers_info_yes').length !== 0 && jQuery('#customers_info_yes').length !== null) {
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
        if(jQuery('#customers_info_yes').length !== 0 && jQuery('#customers_info_yes').length !== null) {
            var cnt = jQuery('#count').val();
            if(cnt > 1) {
                addCustomer(cnt);
            }
        }
    })
});


var removeCustomer = function (id) {
    jQuery('#customer_info_'+id).remove();
}

var addCustomer = function (id) {
    var customerDiv =    `<div id="customer_info_${id}">
    <h6>Customer: ${id}</h6>
<div class="fieldDiv">
<label>First Name*</label><br/>
<input class="ele_width apt_firstname" type="text" name="customer[${id}][firstname]" id="firstname_${id}" placeholder="First Name*" value="">
    </div>
    <div class="fieldDiv">
<label>Last Name*:</label><br/>
<input class="ele_width apt_lastname" type="text" name="customer[${id}][lastname]" id="lastname_${id}" placeholder="Last Name*" value="">
    </div>
    <div class="fieldDiv">
<label>Email*:</label><br/>
<input class="ele_width apt_email" type="text" name="customer[${id}][email]" id="email_${id}" placeholder="Email" value="">
    </div>
    <div class="fieldDiv">
<label>Phone*:</label><br/>
<input class="ele_width apt_phone" type="text" name="customer[${id}][phone]" id="phone_${id}" placeholder="Phone" value="">
    </div>

    <div class="fieldDiv">
<label>Notify Me:</label><br/>
<div><input type="checkbox" name="customer[${id}][noti_email]" title="" value="1" class="notifyRadio" checked><p>By Mail</p></div>
<div><input type="checkbox" name="customer[${id}][noti_sms]" title="" value="2" class="notifyRadio" ><p>By Text Message - Message and Data Rates May Apply</p></div>
</div>
</div>`;
    console.log(id);
    jQuery('#customer_info_div').append(customerDiv);
}