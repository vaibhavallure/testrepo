jQuery(document).ready(function() {


    if (jQuery(window).width() >= 992){
        var nav_height=jQuery('header').outerHeight();
        jQuery('.login_security,.my_addresses,.new_address,.category-shop-our-instagram .main-container').css("margin-top",nav_height/2);
    }


    var box_width=jQuery('.fs-entry-container').outerWidth();
    jQuery('.fs-entry-container').css('height',box_width);



    jQuery(".mobile-sub_menu .menu-head").click(function(){
        jQuery(this).find("a").toggleClass('active');
        jQuery(this).parent().find(".head-child").toggle();
    });



    var headerHeight = jQuery(".mariatash-header").outerHeight();
    jQuery('.open-navigation').css("padding-top", headerHeight);

    jQuery(window).scroll(function() {
        var scroll = jQuery(window).scrollTop();
        if (scroll > jQuery(".mariatash-header").outerHeight()) {
            jQuery(".mt-logo").addClass('header-item-hide');
            jQuery(".nav-links-left").addClass('header-item-hide');
            jQuery("#scroll-logo").addClass('header-item-show');
            jQuery('.mariatash-header').addClass('header-height');
            jQuery('.mariatash-header').addClass('maria-black');
        } else {
            jQuery(".mt-logo").removeClass('header-item-hide');
            jQuery(".nav-links-left").removeClass('header-item-hide');
            jQuery("#scroll-logo").removeClass('header-item-show');
            jQuery('.mariatash-header').removeClass('header-height');
            jQuery('.mariatash-header').removeClass('maria-black');

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
    });






    /*-----------------------------new js code-----------------------------*/

    jQuery('.main_menu').mouseover(function(){
        var section_id='#'+jQuery(this).attr('data-id');
        jQuery('section.sub-menu').hide();
        jQuery(section_id).show();
    });
    jQuery('#navbarNavDropdown').mouseleave(function(){
        jQuery('section.sub-menu').hide();
    })

    jQuery(".eye-image").click(function(){
        var pass_field=jQuery(this).parent().parent().find('.consistent-password');
        if(pass_field.hasClass("show"))
        {
            pass_field.attr("type","password");
            pass_field.removeClass("show");
        }
        else {
            pass_field.attr("type","text");
            pass_field.addClass("show");
        }
    });


    /*----------------mobile menu js-----------------------*/


    jQuery("#menu-btn").on("click",function(){
        if(jQuery('.mobile-main_menu').hasClass('active'))
        {
            jQuery('section.sub-menu').hide();
        }
        jQuery('body').toggleClass("overflow-hidden");

        jQuery('.mobile-main_menu').toggleClass('active');
        jQuery(this).toggleClass("change");


    });
    jQuery('.mobile-main_menu .main_menu').click(function(){
        var section_id='.'+jQuery(this).attr('data-id');
        jQuery('section.sub-menu').hide();
        jQuery(section_id).show();
    });
    jQuery('.close-section').click(function(){
        jQuery('section.sub-menu').hide();
    });

    jQuery(".mobile-sub_menu  #jewelry .menu-head.jwl-head span a").attr("href","#");

    jQuery(".mobile-main_menu li.parent a").attr("href","#");

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
            var filterButton='<a href="#" class="filterButton">' +
            '</a>';

            jQuery('.filter-common-div').html("");

            jQuery("body").append(filter_popup);
            jQuery(".category-products.aw-ajaxcatalog-container").prepend(filterButton);

            jQuery(".filterButton").on('click',function(){
                jQuery(".filterPopup").css({"opacity": "1", "pointer-events": "auto"});
            });

            jQuery(".filterPopup .close").on('click',function(){
                jQuery(".filterPopup").css({"opacity":"0","pointer-events":"none"});
            });
        }
    }
    /*filter popup end---------------------------------*/

});

function myFunction(x) {
    x.classList.toggle("change");
    //jQuery('.mariatash-header').toggleClass('black-bg');
}


jQuery(window).bind("load resize scroll",function(e){
    if (jQuery(window).width() >= 992){
        var nav_height=jQuery('header').outerHeight();
        jQuery('.login_security,.my_addresses,.new_address,.category-shop-our-instagram .main-container').css("margin-top",nav_height/2 +20);
    }
    else{
        jQuery('.login_security,.my_addresses,.new_address,.category-shop-our-instagram .main-container').css("margin-top",'0px');
    }


    var box_width=jQuery('.fs-entry-container').outerWidth();
    jQuery('.fs-entry-container').css('height',box_width);

});