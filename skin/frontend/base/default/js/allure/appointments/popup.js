if (typeof Allure == "undefined") {
    var Allure = {};
}

var setSlotAvailability = function (response) {
    console.log(response);
    jQuery("#pick_ur_time_div").html(response.output);
};

var getSlotAvailability = function (store_id, people, date) {
    var request = {
        "qty": people,
        "store": store_id,
        "date": date
    };

  	jQuery.ajax({
        url : Allure.ajaxGetTimeUrl,
        dataType : 'json',
        type : 'POST',
        data: {request:request},
        beforeSend: function() {
            jQuery('#appointment_date').datepicker('hide');
            jQuery('#appointment_loader').show();
        },
        complete: function() {
            jQuery('#appointment_loader').hide();
        },
        timeout: 30000,
        error: function(jqXHR) {
            if(jqXHR.status==0) {
                alert(" fail to connect, please check your internet connection");
            }
        },
        success : setSlotAvailability
    });
};

var loadSlotAvailability = function (date) {
    var store_id = 2;
    var people = 1;//jQuery('#count').val();
    getSlotAvailability(store_id, people, date);
};
