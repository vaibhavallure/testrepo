
jQuery(document).ready(function () {
    jQuery(window).bind('orientationchange', function (event) {
        location.reload(true);
    });

    peopleCount = jQuery('#count').attr('value');
    jQuery(document).on('change','.people input,.people select',function () {
        jQuery('#pick_ur_slot input').attr("value","");
        jQuery("#slots_section div").removeClass("active");
        calculateTime();
    });
    jQuery(document).on('change','.checkup_select,.piercing_select',function () {
     var no= jQuery(this).data('no');
     if(jQuery('#piercing_select_'+no+':checked').length!=0 || jQuery('#checkup_select_'+no+':checked').length!=0) {
         jQuery('#customer_select_' + no + '').attr("value", 1);
         jQuery('#customer_select_' + no + '').focus();
         jQuery('#customer_select_' + no + '').focusout();

     }else
         jQuery('#customer_select_'+no+'').attr("value","");
    });




    jQuery(document.body).on('keydown', '.phonenumber', function (event) {
        var key= (event.keyCode ? event.keyCode : event.which);

            if (key == 0 || key == 229) { //for android chrome keycode fix
                if (!jQuery(this).hasClass('allure_only_number'))
                    jQuery(this).addClass('allure_only_number');
            }
            if (key == 35 || key==187 || key == 36 || key == 37 || key == 38 || key == 39 || key == 40 || key == 8 || key == 9 || key == 46 || (key >= 96 && key <= 107) || (key >= 109 && key <= 111)) { // end / home/Left / Up / Right / Down Arrow, Backspace,Tab, Delete keys
                return;
            }

            var regex = new RegExp("^[0-9]+$");
            var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
            if (!regex.test(key)) {
                event.preventDefault();
                return false;
            }

    });


});

function __(text) {
    return text;
}
if (typeof Allure == "undefined") {
    var Allure = {};
}

Number.prototype.pad = function (size) {
    var s = String(this);
    while (s.length < (size || 2)) {
        s = "0" + s;
    }
    return s;
}

//LOAD WORKING DAYS FOR PARTICULAR STORE
var loadWorkingDays = function (workingDayUrl,storeid) {
    var availableDateArr = [];
    var currentDate = '';
    jQuery.ajax({
        url: workingDayUrl,
        async: false,
        dataType: 'json',
        type: 'POST',
        data: {storeid: storeid, id: Allure.appointmentId},

        success: function (response) {
            availableDateArr = response.available_dates;
            currentDate = formatDate(new Date(response.current_date));
        },
    });
    return {dates: availableDateArr, today: currentDate};
}

var changePiercingQty=function () {

};
var addCustomer = function (srno) {
    var newCustomer =
        ` <div id="customer${srno}" style="display: none">
               <div id="name-box" class="col-md-12 name-box">
                    <div class="">
                            <h6 class=" translate-popup para-bold color-6">${__('Guest')} ${srno}:</h6>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <input class="input-box required-entry firstname translate-popup select-type-one" type="text" name="customer[${srno}][firstname]" id="firstname${srno}" placeholder="${__('First Name*')}" value=""required>
                               </div>
                                <div class="col-md-6 form-group">
                                    <input class="input-box required-entry lastname translate-popup select-type-one" type="text" name="customer[${srno}][lastname]" id="lastname${srno}" placeholder="${__('Last Name*')}" value="" required>
                                </div>
                        </div>
               </div>
               <div class="col-12">
                 <div class="row">
                   <div id="email-box" class="col-md-6 form-group">
                        <input class="input-box required-entry email translate-popup select-type-one"  type="email" name="customer[${srno}][email]" id="email${srno}" placeholder="${__('Email*')}" value="" required>
                   </div>
                   <div id="phone-box" class="col-md-6 form-group">
                       <input class="input-box required-entry validate-intl-telephone phonenumber translate-popup select-type-one" type="search" name="customer[${srno}][phone]" id="phonenumber${srno}" placeholder="${__('Phone Number')}" value="" required autocomplete="off" Try ="disabled" autocorrect="off">
                   </div>
                 </div>
                 </div>
               <div id="notify-box" class="col-md-12 form-group">
               <div class="row">
                    <div class="col-12 notify-text-line">
                      <p class="label notify-label translate-popup para-normal">${__('I would like to be notified by:')}</p>
                    </div>
                    <div class="col-md-3 col-12 notify-label">
                      <label class="label translate-popup custom-checkbox para-normal" for="em${srno}">${__('Email')}
                        <input type="checkbox" id="em${srno}" name="" checked>
                        <span class="checkmark"></span>
                      </label>
                    </div>
                    <div class="col-md-9 col-12 notify-label">
                        <label class="label translate-popup custom-checkbox para-normal" for="c${srno}">${__('Text Message (Message and data rates may apply)')}
                          <input type="checkbox" class="noti_sms" id="c${srno}" data-section_id="${srno}" =name="customer[${srno}][noti_sms]">
                          <span class="checkmark"></span>
                        </label>
                    </div>
                    </div>
                </div>
        </div>`;


    jQuery('.customer_section').append(newCustomer);
    jQuery('#customer' + srno).show('slow');


   var iti= window.intlTelInput(document.querySelector("#phonenumber" + srno), {
        // initialCountry: 'fr',
        autoFormat: false,
        autoHideDialCode: false,
        autoPlaceholder: false,
        nationalMode: false,
        utilsScript:  Allure.UtilPath
    });
    allureIntlTelValidate(jQuery("#phonenumber" + srno), iti);

};

var addCustomerJob = function (srno) {
    var newJob =
        `<div id="customer${srno}job" style="display: none" class="form-group">
            <h6 class=" translate-popup">${__('Guest')} ${srno}:</h6>
            <label class="label mb-4 translate-popup para-normal">${__('Type of Appointment:')}</label>
            <div  class="row mb-4">
                <div class="col-xs-12 col-sm-3 col-md-3">
                    <label class="label translate-popup custom-checkbox" for="piercing_select_${srno}">${__('Piercing')}
                      <input type="checkbox" class="piercing_select" data-no="${srno}" id="piercing_select_${srno}" onchange="displayPiercing(\'${'#piercing-wrapper' + srno}\')" name="customer[${srno}][piercing_select]">
                      <span class="checkmark"></span>
                    </label>
                </div>
                <div class="col-md-6 col-8">
                    <label class="label translate-popup custom-checkbox" for="checkup_select_${srno}">${__('Checkup/downsize')}
                      <input type="checkbox" class="checkup_select" data-no="${srno}" id="checkup_select_${srno}" value="1" name="customer[${srno}][checkup]">
                      <span class="checkmark"></span>
                    </label>
                </div>
                <div class="col-12">
                <input type="text" class="customer_select required-entry" value="" id="customer_select_${srno}"
                name="chekbox[${srno}]" style="display:none">
                </div>
            </div>
            <div id="piercing-wrapper${srno}" class="job-wrapper row" style="display:none">
                <div class="col-12">
                    <div class="row">
                        <div class="col-8">
                            <label class="label translate-popup para-bold color-6 attr-count">${__('Number of Piercings:')}</label>
                        </div>
                    <div class="col-4 text-right">
                        <select id="piercing_${srno}" class="input-box quantity-count"  name="customer[${srno}][piercing]" onchange="changePiercingQty(this)" disabled>
                            <option selected value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>`;


    jQuery('.job').append(newJob);
    jQuery('#customer' + srno + 'job').show('slow');
};
var removeCustomer = function (srno) {
    var customer = jQuery('#customer' + (srno + 1));
    customer.hide('slow', function () {
        customer.remove();
    });
};
var removeCustomerJob = function (srno) {
    var job = jQuery('#customer' + (srno + 1) + 'job');
    job.hide('slow', function () {
        job.remove();
    });
};

var displayPiercing = function (id) {
    jQuery(id).toggle();
    if (jQuery(id + ' select').prop("disabled"))
        jQuery(id + ' select').prop("disabled", false);
    else
        jQuery(id + ' select').prop("disabled", true);
};

var peopleCount = 1;
var time_in_min=0;

var calculateTime = function () {
    var old_time_in_min=time_in_min;
    var number_of_piercing = 0;
    var number_of_piercing_people = 0;
    var number_of_checkup = 0;
    for (var i = 1; i <= peopleCount; i++) {
        if (jQuery('#piercing_select_' + i).is(":checked")) {
            number_of_piercing = number_of_piercing + parseInt(jQuery('#piercing_' + i).val());
            number_of_piercing_people++;
        }
        if ((!jQuery('#piercing_select_' + i).is(":checked")) && (jQuery('#checkup_select_' + i).is(":checked"))) {
            number_of_checkup++;
        }
    }
    var slotsArray = JSON.parse(slots);
    var slotTime = parseInt(slotsArray[number_of_piercing_people][number_of_piercing]);
    var time = slotTime + (number_of_checkup * 10);
    time_in_min=time;
    var hours = Math.trunc(time / 60);
    var minutes = time % 60;
    var timeSpan=jQuery('#total_time');

    if(old_time_in_min!=time_in_min) {

        var hoursLabel=(hours>1)?"Hours":"Hour";
        var minLabel=(minutes>1)?"Minutes":"Minute";

    timeSpan.slideUp("slow",function () {
        timeSpan.html('<span class="translate-popup para-normal">'+__('Expected Appointment Length:')+'</span> '+hours +' <span class="translate-popup info-text-two">'+ __(hoursLabel)+'</span> '+ minutes+' <span class="translate-popup info-text-two">'+__(minLabel)+'</span>');
    });
    timeSpan.slideDown("slow");

        // jQuery('.form-disable-overlay').show();
        setLoader();
       //jQuery('#appointemnet_form input').prop("disabled",true);
        if(getAvailableSlots())
        {
         //   jQuery('#appointemnet_form input').prop("disabled",false);
        }
    }
    else {
        //jQuery('.form-disable-overlay').hide();

        //jQuery('#appointemnet_form input').prop("disabled", false);
    }
};




var getAvailableSlots = function () {
    jQuery('#appointment_date').datepicker('hide');

    if(time_in_min<1)
    {
        html=`<p class="col-12 translate-popup para-normal">
            ${__('Choose type of Appointment to get available time slots')}
            </p>`;
        jQuery("#slots_section").slideUp("fast",function () {
            jQuery('#slots_section').html(html);
            jQuery("#pick_ur_slot").addClass("loading");
        });
        jQuery("#slots_section").slideDown("slow",function () {
            jQuery("#pick_ur_slot").removeClass("loading");
        });
        //jQuery('.form-disable-overlay').hide();
        unsetLoader();
        return 1;
    }
    var request = {
        "slottime": time_in_min,
        "storeid": jQuery('#store_id').val(),
        "date": jQuery('#appointment_date').val(),
        "appointment_id":Allure.appointmentId,
    };

    jQuery.ajax({
        url: Allure.getAvlblSlotsUrl,
        dataType: 'json',
        type: 'POST',
        data: request,
       // async:false,
        beforeSend: function () {
             jQuery("#slots_section").slideUp("slow",function () {
                 jQuery("#pick_ur_slot").addClass("loading");
             });

           // jQuery('#slotloader').show();
        },
        complete: function () {
            // jQuery('#slotloader').hide();

        },
        timeout: 30000,
        error: function (jqXHR) {
            if (jqXHR.status == 0) {
                alert(__("Fail to connect, please check your internet connection"));
            }
        },
        success: setAvailableSlots
    });

    return 1;
};

var modifyDateSlotAvlble=0;

var setAvailableSlots = function (response) {
   // jQuery('#slotloader').hide();

    html=`<p class="col-12 p-1 m-1 text-center">
        <span class="translate-popup para-normal">${__('No Slot Available')}</span>
        </p>`;

    if(response.success==true)
    {
        //console.log(response.slots);
        var slots=JSON.stringify(response.slots);
         slots=JSON.parse(slots);
         if(slots.length>0) {
             var html="";
             for (var i = 0; i < slots.length; i++) {

                 html+=' <div class="slot-width float-left text-center info-text-two'+isModifyActive(slots[i])+'" data-start="'+slots[i]["start"]+'" data-end="'+slots[i]["end"]+'" data-p_id="'+slots[i]["id"]+'" title="'+slots[i]["start"]+'-'+slots[i]["end"]+'">\n'
                            +slots[i]["start"]+
                     '</div>';
             }
         }
    }

    if(modifyDateSlotAvlble==0)
    {
        jQuery('#starttime_hidden').attr('value','');
        jQuery('#endtime_hidden').attr('value','');
        jQuery('#piercer_id').attr('value','');
    }
    modifyDateSlotAvlble=0;


    jQuery("#slots_section").slideUp("slow",function () {
        jQuery('#slots_section').html(html);
    });
    jQuery("#slots_section").slideDown("slow",function () {
        jQuery("#pick_ur_slot").removeClass("loading");
        // jQuery('.form-disable-overlay').hide();
        unsetLoader();
    });

};


var isModifyActive=function (slot) {
    if(Allure.appointmentId)
    {
        if(slot['start']==jQuery('#starttime_hidden').attr('value') && slot['end']==jQuery('#endtime_hidden').attr('value') && slot['id']==jQuery('#piercer_id').attr('value'))
        {
            modifyDateSlotAvlble=1;
            return " active "
        }

    }

    return "";
};


var changeQty = function (obj) {
    var changedCount = jQuery(obj).val();
    if (changedCount > peopleCount) {
        var number_of_increment = changedCount - peopleCount;
        //console.log(number_of_increment);
        while (number_of_increment > 0) {
            peopleCount++;
            addCustomerJob(peopleCount);
            addCustomer(peopleCount);
            number_of_increment--;

            validateForm();

        }
    }
    else {
        var number_of_decrement = peopleCount - changedCount;
        while (number_of_decrement > 0) {
            peopleCount--;
            removeCustomer(peopleCount);
            removeCustomerJob(peopleCount);
            number_of_decrement--;
        }
    }

};

var formatDate = function(date) {
    var monthNames = [
        "Jan", "Feb", "Mar",
        "Apr", "May", "Jun", "Jul",
        "Aug", "Sep", "Oct",
        "Nov", "Dec"
    ];

    var day = date.getDate();
    var monthIndex = date.getMonth();
    var year = date.getFullYear();

    return day + '  ' + monthNames[monthIndex] + '  ' + year;
};

var validateForm = function () {

/*
    jQuery.validator.addMethod("noSpace", function(value, element) {
        return value.indexOf(" ") < 0 && value != "";
    }, "No space please and don't leave it empty");*/


    jQuery('.firstname,.lastname,.email').each(function() {
        var placeholder=jQuery(this).attr("placeholder");
        jQuery(this).rules('add', {
            required: true,
            nospace: true,
            messages: {
                required:  __("Please enter your")+" "+__(placeholder),
                nospace: __("Spaces Not Allowed")
            }
        });
    });
/*validation using hidden inputs*/
    jQuery('.customer_select').each(function() {
        jQuery(this).rules('add', {
            required:true,
            messages: {
                required:  __("Please select one of the above options"),
            }
        });
    });

    jQuery('#starttime_hidden').rules('add', {
        required: true,
        messages: {
            required: __("Please select slot")
        }
    });

    jQuery('.email').each(function() {
        //var placeholder=jQuery(this).attr("placeholder");
        jQuery(this).rules('add', {
            email: true,
            messages: {
                email:  __("Please enter a valid email address.")
            }
        });
    });

    jQuery(".noti_sms").change(function() {
        if(this.checked) {
            jQuery("#phonenumber" + jQuery(this).attr("data-section_id")).rules('add', {
                minlength: 10,
                messages: {
                    minlength: __("Please enter valid phone number.")
                }
            });
        }
        else {
            jQuery("#phonenumber" + jQuery(this).attr("data-section_id")).rules('add', {
                minlength: false
            });
        }
    });


};

jQuery(document).ready(function () {
    var $ = jQuery;


    if (jQuery(window).outerWidth() <= 1023) {
        jQuery('#desktop-slot').appendTo(jQuery('#mobile-slot'));
    }
    if (jQuery(window).outerWidth() <= 767) {


        jQuery(window).scroll(function () {
            var scrollTop = jQuery(window).scrollTop();
            var offsetHeight = jQuery('.pop-up-div').position().top - jQuery('.navbar-brand').height();
            // console.log(scrollTop+'::'+offsetHeight);
            if (scrollTop >= offsetHeight) {
                jQuery("#navbar").css("background-color", '#2c2c2c');
            } else {
                jQuery("#navbar").css("background-color", 'transparent');
            }
        });

        jQuery('.faq-question').click(function () {
            // console.log(jQuery(this));
            // console.log(jQuery(this).attr('area-expanded'));
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

    $('#close-nav').click(function () {
        // console.log('m in');
        $('#navbar-icon').trigger('click');
        // $('#navbar-icon').removeClass('active');
        // $('#navbar-menu').css('display','none');
    });

    jQuery('a#btnPriceList').click(function () {
        if ($(this).hasClass('active')) {
            $('#price-list').hide();
            $(this).removeClass('active');
        } else {
            $('#price-list').show();
            $(this).addClass('active');
        }
    });

    jQuery('#icon-price-close').click(function () {
        jQuery('a#btnPriceList').removeClass('active');
        $('#price-list').hide();
    });

    jQuery('.navbar-icon').click(function () {
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


});
