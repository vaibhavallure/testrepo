(function ($) {
    $.fn.inputToSelect = function (options) {
        options = $.extend({
            years: 100,
            months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            useLeapYear: true,
            yearsOverToday: 0,
            selectClass: '',
            hasEmpty: false,
            emptyValue: '',
            emptyText: '-',
            defaultToday: true,
            beforeReplace: function() {},
            afterReplace: function() {}
        }, options);

        var _private = {
            days: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]
        }

        return this.each(function () {
            if (jQuery(this).is('input')) {
                options.beforeReplace();

                jQuery(this).hide();

                jQuery(this).wrap('<div class="date_selector_div row" />');

                var _element = this;

                var parent_div = jQuery(this).parent();

                var radioOptionOne='<div class="email_delivery_option2 text-left">' +
                    '<label class="product-attribute-name " >Delivery:</label>'+
                    '<label class="para-normal custom-checkbox custom-checkbox-two" for="deliver_immediately">\n' +
                    '                                      <input type="checkbox" id="deliver_immediately" value="2" class="checkbox">\n' +
                    '                                      <span class="checkmark"></span>Deliver Immediately' +
                    '                                   </label>' +
                    '<input type="hidden" id="mail_delivery_option"  name="mail_delivery_option" value="1"></div>';

                parent_div.before(radioOptionOne);
                parent_div.prepend('<div class="text-left col-12"><label class="product-attribute-name " >Or:</label></div>');



                var i,d,cd = new Date();
                if (jQuery(_element).val()) {
                    var vals = jQuery(_element).val().split('-');
                    d = new Date(vals[0],vals[1]-1,vals[2]);
                } else {
                    d = new Date();
                }
                var year = d.getFullYear();
                var month = d.getMonth();
                var day = d.getDate();

                var currentYear = cd.getFullYear();

                jQuery(_element).data('day',day);
                jQuery(_element).data('month',month);
                jQuery(_element).data('year',year);

                parent_div.append('<div class="date_wrapper-column col-12"><div class="date_wrapper row"></div></div>');
                //month
                parent_div.find(".date_wrapper").append('<div class="div_selector_month"><select class="date_selector_month '+options.selectClass+'" /></div>');
                if (options.hasEmpty) {
                    parent_div.find('.date_selector_month').append('<option value="-1">'+options.emptyText+'</option>');
                }
                for (i=0;i<12;i++) {
                    parent_div.find('.date_selector_month').append('<option value="'+i+'">'+options.months[i]+'</option>');
                }
                parent_div.find('.date_selector_month').change(function(){
                    jQuery(_element).data('month',jQuery(this).val());

                    if (jQuery(this).val()!=-1) {
                        parent_div.find('.date_selector_day').empty();

                        if (options.hasEmpty) {
                            parent_div.find('.date_selector_day').append('<option value="-1">'+options.emptyText+'</option>');
                        }
                        for (i=1;i<=_private.days[jQuery(this).val()];i++) {
                            parent_div.find('.date_selector_day').append('<option value="'+i+'">'+i+'</option>');
                        }

                        parent_div.find('.date_selector_day').val(jQuery(_element).data('day'));

                        parent_div.find('input').val(jQuery(_element).data('year')+
                            '-'+(parseInt(jQuery(_element).data('month'))+1)+
                            '-'+jQuery(_element).data('day'));
                    } else {
                        parent_div.find('input').val(options.emptyValue);
                        parent_div.find('.date_selector_day').val('-1');
                        parent_div.find('.date_selector_month').val('-1');
                        parent_div.find('.date_selector_year').val('-1');
                    }
                });

                //day
                parent_div.find(".date_wrapper").append('<div class="div_selector_day" ><select class="date_selector_day '+options.selectClass+'" /></div>');
                if (options.hasEmpty) {
                    parent_div.find('.date_selector_day').append('<option value="-1">'+options.emptyText+'</option>');
                }
                for (i=1;i<=_private.days[month];i++) {
                    parent_div.find('.date_selector_day').append('<option value="'+i+'">'+i+'</option>');
                }
                parent_div.find('.date_selector_day').change(function(){
                    jQuery(_element).data('day',jQuery(this).val());

                    if (jQuery(this).val()!=-1) {
                        parent_div.find('input').val(jQuery(_element).data('year')+
                            '-'+(parseInt(jQuery(_element).data('month'))+1)+
                            '-'+jQuery(_element).data('day'));
                    } else {
                        parent_div.find('input').val(options.emptyValue);
                        parent_div.find('.date_selector_day').val('-1');
                        parent_div.find('.date_selector_month').val('-1');
                        parent_div.find('.date_selector_year').val('-1');
                    }
                });

                //year
                parent_div.find(".date_wrapper").append('<div class="div_selector_year"><select class="date_selector_year '+options.selectClass+'" /></div>');
                if (options.hasEmpty) {
                    parent_div.find('.date_selector_year').append('<option value="-1">'+options.emptyText+'</option>');
                }
                for (i=currentYear+options.yearsOverToday;i>currentYear-options.years;i--) {
                    parent_div.find('.date_selector_year').append('<option value="'+i+'">'+i+'</option>');
                }
                parent_div.find('.date_selector_year').change(function(){
                    jQuery(_element).data('year',jQuery(this).val());

                    if (jQuery(this).val()!=-1) {
                        if (jQuery(this).val()%4==0 && options.useLeapYear) {
                            _private.days[1] = 29;
                        } else {
                            _private.days[1] = 28;
                        }
                        if (jQuery(_element).data('month')==1) { //is february
                            parent_div.find('.date_selector_day').empty();

                            if (options.hasEmpty) {
                                parent_div.find('.date_selector_day').append('<option value="-1">'+options.emptyText+'</option>');
                            }
                            for (i=1;i<=_private.days[1];i++) {
                                parent_div.find('.date_selector_day').append('<option value="'+i+'">'+i+'</option>');
                            }
                        }

                        parent_div.find('.date_selector_day').val(jQuery(_element).data('day'));

                        parent_div.find('input').val(jQuery(_element).data('year')+
                            '-'+(parseInt(jQuery(_element).data('month'))+1)+
                            '-'+jQuery(_element).data('day'));
                    } else {
                        parent_div.find('input').val(options.emptyValue);
                        parent_div.find('.date_selector_day').val('-1');
                        parent_div.find('.date_selector_month').val('-1');
                        parent_div.find('.date_selector_year').val('-1');
                    }
                });

                parent_div.append('<div id="advice-required-entry-date" class="validation-advice" style="display:none"></div>');
                parent_div.append('<div class="date-input-info text-left col-12"><p class="para-normal mb-0">Based on Eastern Standard Time.</p></div>');

                if (!options.hasEmpty || options.defaultToday) {
                    //set the dates
                    parent_div.find('.date_selector_day').val(day);
                    parent_div.find('.date_selector_month').val(month);
                    parent_div.find('.date_selector_year').val(year);

                    //set the input value
                    parent_div.find('input').val(jQuery(_element).data('year')+
                        '-'+(parseInt(jQuery(_element).data('month'))+1)+
                        '-'+jQuery(_element).data('day'));
                } else {
                    parent_div.find('.date_selector_day').val('-1');
                    parent_div.find('.date_selector_month').val('-1');
                    parent_div.find('.date_selector_year').val('-1');

                    parent_div.find('input').val(options.emptyValue);
                }

                //run the function
                options.afterReplace();
            }
        });
    };
})(jQuery);
;
