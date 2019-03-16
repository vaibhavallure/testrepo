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
            jQuery('#loaderAppointmentTime').prev().hide();
            jQuery('#loaderAppointmentTime').show();
        },
        complete: function() {
            jQuery('#loaderAppointmentTime').hide();
            jQuery('#loaderAppointmentTime').prev().show();
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
    var people = jQuery('#count').val();

    if (date != '') {
        getSlotAvailability(store_id, people, date);
    }
};

var updateSlotAvailability  = function () {
    var date = jQuery('#appointment_date').val();

    if (date != '') {
        loadSlotAvailability(date);
    }

};

var peopleCount = 1;

var increaseQty = function () {

    if (peopleCount < 5) {
        peopleCount++;
        jQuery("#count").val(peopleCount);

        updateSlotAvailability();
    }

    console.log(peopleCount);
}

var decreaseQty= function () {
    if (peopleCount > 1) {
        peopleCount--;
        jQuery("#count").val(peopleCount);

        updateSlotAvailability();
    }

    console.log(peopleCount);
}
