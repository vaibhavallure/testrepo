jQuery(document).ready(function () {


    jQuery('a[href=#]').each(function () {
        jQuery(this).attr('href', 'JavaScript:Void(0)');
    });


    jQuery('.customer-account-index .breadcrumbs,.customer-account-index .messages').appendTo('.mt-new-myaccount-dashbord .page-title');

    jQuery('.myaccount-index-index .breadcrumbs,.myaccount-index-index .messages ').appendTo('.myaccount-index-index .page-title');

    jQuery('.customer-account-edit .breadcrumbs, .customer-account-edit .messages').appendTo('.customer-account-edit .col-xl-5.col-lg-6');

    jQuery('.customer-address-index .breadcrumbs, .customer-address-index .messages').appendTo('.customer-address-index .box-title');


    jQuery(".close-del").on('click', function () {
        jQuery("body").css({"overflow": "scroll", "position": "static"});
    });




    if (jQuery(window).width() >= 992) {

        // var cont_width=jQuery('.myaccount-index-index .mt-myaccount-page,.customer-address-index .addresses-list').outerWidth();
        // jQuery('.myaccount-index-index .breadcrumbs,.customer-address-index .breadcrumbs').css({
        //     'width': cont_width
        // });


        var nav_height = jQuery('header').outerHeight();
        jQuery('.login_security,.my_addresses,.new_address,.category-shop-our-instagram .main-container,.insta-details').css("margin-top", nav_height);

        //     jQuery('.myaccount-index-index .breadcrumbs,.customer-account-edit .breadcrumbs,.customer-address-index .breadcrumbs').css("margin-top",nav_height/2);
    }


    var box_width = jQuery('.fs-entry-container').outerWidth();
    jQuery('.fs-entry-container').css('height', box_width);


    jQuery(".mobile-sub_menu .menu-head").click(function () {
        jQuery(this).find("a").toggleClass('active');
        jQuery(this).parent().find(".head-child").toggle();
    });


    jQuery(window).bind("resize scroll",function () {
        var headerHeight = jQuery(".mariatash-header").outerHeight();
        jQuery('.open-navigation').css("padding-top", headerHeight);

        if (jQuery(window).width() >= 1363) {

            var scroll = jQuery(window).scrollTop();
            if (scroll > jQuery(".mariatash-header").outerHeight()) {
                jQuery(".mt-logo").addClass('d-none');
                jQuery(".nav-links-left").addClass('d-none');
                jQuery("#scroll-logo").removeClass('d-none');
                jQuery('.mariatash-header').addClass('header-height');
                jQuery('.mariatash-header').addClass('maria-black');
                jQuery('.mariatash-header').addClass('scrolled-menu');
                jQuery("section.sub-menu").addClass("scrolled");
            } else {
                jQuery(".mt-logo").removeClass('d-none');
                jQuery(".nav-links-left").removeClass('d-none');
                jQuery("#scroll-logo").addClass('d-none');
                jQuery('.mariatash-header').removeClass('header-height');
                jQuery('.mariatash-header').removeClass('maria-black');
                jQuery('.mariatash-header').removeClass('scrolled-menu');
                jQuery("section.sub-menu").removeClass("scrolled");

            }

            var productImageHeight = jQuery('#product-detail-image > div').outerHeight();
            var productDetailsHeight = jQuery('#product-details-flow').outerHeight();
            if (jQuery('.owl-carousel').length) {
                if ((productImageHeight) > (productDetailsHeight - scroll)) {
                    jQuery('#product-details-flow').removeClass('offset-66');
                    jQuery('#product-detail-image').removeClass('fix-image');
                    jQuery('#product-detail-image > div').addClass('position-bottom');
                } else {
                    jQuery('#product-details-flow').addClass('offset-66');
                    jQuery('#product-detail-image').addClass('fix-image');
                    jQuery('#product-detail-image > div').removeClass('position-bottom');
                }
            }
            headerHeight = jQuery(".mariatash-header").outerHeight();
            jQuery('.open-navigation').css("padding-top", headerHeight);

        } else {
            if (jQuery(window).width() >= 1024) {

                var scroll = jQuery(window).scrollTop();
                if (scroll > jQuery(".mariatash-header").outerHeight()) {
                    jQuery('.mariatash-header').addClass('maria-black');
                } else {
                    jQuery('.mariatash-header').removeClass('maria-black');
                }

            }
            jQuery(".mt-logo").removeClass('d-none');
            jQuery(".nav-links-left").removeClass('d-none');
            jQuery("#scroll-logo").addClass('d-none');
            jQuery('.mariatash-header').removeClass('header-height');
            //jQuery('.mariatash-header').removeClass('maria-black');
            jQuery('.mariatash-header').removeClass('scrolled-menu');
            jQuery("section.sub-menu").removeClass("scrolled");
        }
    });


    /*-----------------------------new js code-----------------------------*/

    jQuery('.main_menu').mouseover(function () {
        var section_id = '#' + jQuery(this).attr('data-id');

        if(!jQuery(this).hasClass('active_menu')) {
            jQuery(".main_menu").removeClass('active_menu');
            jQuery(".menu_overlay").removeClass("d-none");
            jQuery(this).addClass('active_menu');
            jQuery('section.sub-menu').hide();
            jQuery(section_id).slideDown();
            unScrollBody();
        }
    });
    jQuery('#navbarNavDropdown').mouseleave(function () {
        jQuery(".main_menu").removeClass('active_menu');
        jQuery('section.sub-menu').slideUp();
        jQuery(".menu_overlay").addClass("d-none");
        scrollBody();
    });

    jQuery('.noChild').mouseover(function () {
        jQuery(".main_menu").removeClass('active_menu');
        jQuery('section.sub-menu').slideUp();
        jQuery(".menu_overlay").addClass("d-none");
        scrollBody();
    });



    jQuery(".eye-image").click(function () {
        var pass_field = jQuery(this).parent().parent().find('.consistent-password');
        if (pass_field.hasClass("show")) {
            pass_field.attr("type", "password");
            pass_field.removeClass("show");
            jQuery(this).parent().find('.eye-image').css({
                'display': 'block'
            });
            jQuery(this).parent().find('.close-eye').css({
                'display': 'none'
            });
        }
        else {
            pass_field.attr("type", "text");
            pass_field.addClass("show");
            jQuery(this).parent().find('.eye-image').css({
                'display': 'none'
            });
            jQuery(this).parent().find('.close-eye').css({
                'display': 'block'
            });
        }
    });


    /*----------------mobile menu js-----------------------*/


    jQuery("#menu-btn").on("click", function () {
        if (jQuery('.mobile-main_menu').hasClass('active')) {
            jQuery('section.sub-menu').hide();
        }
        jQuery('body').toggleClass("fancybox-lock");
        jQuery(".menu_overlay").toggleClass("d-none");

        jQuery('.mobile-main_menu').toggleClass('active');
        jQuery(this).toggleClass("change");

        // jQuery("body").prepend(overlay);

    });
    jQuery('.mobile-main_menu .main_menu').click(function () {
        var section_id = '.' + jQuery(this).attr('data-id');
        jQuery('section.sub-menu').hide();
        jQuery(section_id).show();
    });
    jQuery('.close-section').click(function () {
        jQuery('section.sub-menu').hide();
    });

    jQuery(".mobile-sub_menu  #jewelry .menu-head.jwl-head span a").attr("href", "#");

    jQuery(".mobile-main_menu li.parent a").attr("href", "#");

    /*filter popup start---------------------------------*/
    var width = jQuery(window).width();
    if ((width < 1023)) {

        if (jQuery('.filter-common-div').length) {
            var filter_popup = '<div id="openModal" class="filterPopup">\n' +
                '\t<div class="pop-up-cover">\n' +
                '\t\t<div class="pop-title modal-header">\n' +
                '\t\t\t<a href="#close" title="Close" class="close exit-large">\n' +
                '\t\t\t\t<div></div>\n' +
                '\t\t\t</a>\n' +
                '\t\t</div>\n' +
                '\t\t<div class="body modal-body">\n' +
                '\t\t\t<p class="para-normal">' + jQuery('.filter-common-div').html() + '</p>\n' +
                '\t\t</div>\n' +
                '\t</div>\n' +
                '</div>';
            var filterButton = '<a href="#" class="filterButton">' +
                '</a>';

            jQuery('.filter-common-div').html("");

            jQuery("body").append(filter_popup);
            jQuery(".mb-breadcrumbs .breadcrumbs").prepend(filterButton);

            jQuery(".filterButton").on('click', function () {
                jQuery(".filterPopup").css({"opacity": "1", "pointer-events": "auto"});
                unScrollBody();
            });

            jQuery(".filterPopup .close").on('click', function () {
                jQuery(".filterPopup").css({"opacity": "0", "pointer-events": "none"});
                scrollBody();
            });
        }
    }
    /*filter popup end---------------------------------*/


});



jQuery(window).bind("load resize scroll", function (e) {


    if (jQuery(window).width() >= 992) {
        var nav_height = jQuery('header').outerHeight();
        jQuery('.login_security,.my_addresses,.new_address,.category-shop-our-instagram .main-container,.insta-details').css("margin-top", nav_height / 2 + 20);
    }
    else {
        jQuery('.login_security,.my_addresses,.new_address,.category-shop-our-instagram .main-container,.insta-details').css("margin-top", '0px');
    }

    var box_width = jQuery('.fs-entry-container').outerWidth();
    jQuery('.fs-entry-container').css('height', box_width);




// fixit(".cart-mt",'.fix-section',10,43);
// fixit("#checkoutSteps",'.col-right.sidebar',10,110);


});


var fixit=function (parent,el,top,right,left) {

if (jQuery(window).width() >= 1024) {

    if(jQuery(parent).length) {
        var scroll = jQuery(window).scrollTop();
        if (scroll > jQuery(parent).offset().top) {

            //if(jQuery('.fix-section'))
            jQuery(el).css({
                'position': 'fixed',
                'top': top,
                'right': right,
                'padding-top': jQuery('.navbar').outerHeight() + 5
            });
        } else {
            jQuery(el).css({
                'position': 'static',
                'top': '0px'
            });
        }
    }
}
else{
    jQuery(el).css({
        'position':'static',
        'top' : '0px'
    });
}
}

var setLoader = function(){
    jQuery.fancybox.showLoading();
    jQuery.fancybox.helpers.overlay.open({parent: $('body'),closeClick : false});
}

var unsetLoader= function(){
    jQuery.fancybox.hideLoading();
    jQuery('.fancybox-overlay.fancybox-overlay-fixed, .fancybox-overlay').hide();
}
