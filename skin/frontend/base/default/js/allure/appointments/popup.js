

if (typeof Allure == "undefined") {
    var Allure = {};
}

Number.prototype.pad = function(size) {
    var s = String(this);
    while (s.length < (size || 2)) {s = "0" + s;}
    return s;
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
//LOAD WORKING DAYS FOR PARTICULAR STORE
var loadWorkingDays  = function(workingDayUrl)
{
    var storeid = document.getElementById("store_id").value;
    var availableDateArr=[];
    jQuery.ajax({
        url: workingDayUrl,
        async:false,
        // dataType: 'json',
        type: 'POST',
        data: {storeid:storeid,id:Allure.appointmentId},

        success: function (response) {
            response = JSON.parse(response);
            availableDateArr = response.available_dates;

        },
    });
    return availableDateArr;
}
var loadSlotAvailability = function (date) {
    var store_id = document.getElementById("store_id").value;
    var people = jQuery('#count').val();

    if (date != '') {
        getSlotAvailability(store_id, people, date);
    }
};

var updateSlotAvailability  = function () {
    var date = jQuery('#appointment_date').val();

    console.log(date);

    if (date != '') {
        loadSlotAvailability(date);
    }

};

var peopleCount = 1;

var increaseQty = function () {

    if (peopleCount < 4) {
        peopleCount++;
        jQuery("#count").val(peopleCount);

        updateSlotAvailability();
    }
    else {
        alert('For bookings of 5 or more, please contact rsvp@mariatash.com')
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

jQuery(document).ready(function() {
    var $ =  jQuery;

    $('.close-nav').on('click', function(){console.log('m in');
        $('#navbar-icon').removeClass('active');
        $('#navbar-menu').css('display','none');
    });

    jQuery('a#btnPriceList').click(function(){
        if ($(this).hasClass('active')) {
            $('#price-list').hide();
            $(this).removeClass('active');
        } else {
            $('#price-list').show();
            $(this).addClass('active');
        }
    });

    jQuery('#icon-price-close').click(function(){
        jQuery('a#btnPriceList').removeClass('active');
        $('#price-list').hide();
    });

    jQuery('.navbar-icon').click(function() {
        if (window.outerWidth < 768) {
            if ($(this).hasClass('active')) {
                $('.navbar-icon').removeClass('active');
                jQuery('ul#navbar-menu').hide();
            } else {
                $('.navbar-icon').addClass('active');
                jQuery('ul#navbar-menu').show();
            }
        }
    });

    if (jQuery("#count").length) {
        updateSlotAvailability();
    }

    if (jQuery(window).outerWidth() <= 767) {

        jQuery( window ).scroll(function() {
            var scrollTop = jQuery(window).scrollTop();
            var offsetHeight = jQuery('.pop-up-div').position().top - jQuery('.navbar-brand').height();
            console.log(scrollTop+'::'+offsetHeight);
            if (scrollTop >= offsetHeight) {
                jQuery( "#navbar" ).css( "background-color",'#2c2c2c' );
            } else {
                jQuery( "#navbar" ).css( "background-color",'transparent' );
            }
        });

        jQuery('.faq-question').click(function() {
            console.log(jQuery(this));
            console.log(jQuery(this).attr('area-expanded'));
            if (!jQuery(this).hasClass('data-expanded')) {
                jQuery('.faq-question.data-expanded').removeClass('data-expanded');
                jQuery('.faq-answer.data-expanded').removeClass('data-expanded').slideUp();
                jQuery(this).next().slideDown();
                jQuery(this).addClass('data-expanded');
                jQuery(this).next().addClass('data-expanded');
            } else {
                jQuery(this).removeClass('data-expanded');
                jQuery(this).next().removeClass('data-expanded');
                jQuery(this).next().slideUp();
            }
        });
    }
});
