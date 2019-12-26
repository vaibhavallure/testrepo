jQuery(document).ready(function () {

    //on enter search
    jQuery('#search').keydown( (e) => {
//        console.log(e.keyCode)
        if(e.keyCode==13){
            let clone = jQuery('#search-input').clone();
            jQuery(clone).css('display','none');
            jQuery('#search_mini_form').append(clone);
            jQuery('#search_mini_form').submit()
        }
    })


    /*if(jQuery("body").hasClass("catalog-product-view") && !jQuery("body").hasClass("quickview-index-index")) {
        jQuery(document)
            .ajaxStart(function () {
                setLoader();
            })
            .ajaxStop(function () {
                unsetLoader();
            });
    }*/

    // jQuery('input[type=text]').on('keyup', function(event) {
    //     var $this = jQuery(this),
    //         val = $this.val();
    //     if(!$this.hasClass("validate-email"))
    //         val = val.charAt(0).toUpperCase() + val.slice(1);
    //
    //     $this.val(val);
    // });

    jQuery(window).bind('beforeunload', function() {
        // setLoader();
    });

    unsetLoader();

jQuery('.link-button').click(function(event) {
    unsetLoader();
});

    /*move cartilage section to filter bottom*/
    if(jQuery(".cartilage-section").length)
    jQuery(".cartilage-section").appendTo(jQuery(".mb-mana-catalog-leftnav"));


    if(!jQuery("p.category-image").length && !jQuery("p.category-image-bleed").length && !jQuery(".second-header-image").length ){
      jQuery(".mariatash-header").css("background","rgba(41,41,41,0.90)");
    }

    if(jQuery("p.category-image").length) {
        jQuery(".for-space-to-bottom").css("background-image", "url('"+jQuery("p.category-image img").attr("src")+"')");
        jQuery(".for-space-to-bottom").addClass("cat-img-space");
        jQuery("body").addClass("cat-img-present");
        jQuery("p.category-image").hide();
    }

    if(jQuery(".second-header-image").length) {
        jQuery(".for-space-to-bottom").css("background-image", "url('"+jQuery(".second-header-image img").attr("src")+"')");
        jQuery(".for-space-to-bottom").addClass("cat-img-space");
        jQuery("body").addClass("cat-img-present");
        jQuery(".second-header-image").hide();
    }

    if(jQuery(".catalog-product-view").length)
    {
        //.recently-view insta-main you_may_like
        if(!jQuery(".recently-view").length && !jQuery(".insta-main").length && !jQuery(".you_may_like").length)
        {
         jQuery(".footer").attr("style","padding-top:0px!important");
         jQuery(".for-bottom-space.p-5").removeClass("p-5");
         jQuery(".product-detail-parent").css("padding-bottom", "65px");
        }

    }

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


    var box_width = jQuery('.fs-entry-container').outerWidth();
    jQuery('.fs-entry-container').css('height', box_width);


    jQuery(".mobile-sub_menu .menu-head").click(function () {
    	var $ = jQuery;
    	if ($(this).find("a").hasClass('active')) {
    		$(this).find("a").removeClass('active');
    		$(this).next().removeClass('active');
    	} else {
    		$(this).find("a").addClass('active');
    		$(this).next().addClass('active');
    	}
    });


    jQuery(window).bind("resize scroll load",function () {
        var headerHeight = jQuery(".mariatash-header").outerHeight();
        jQuery('.open-navigation').css("padding-top", headerHeight);

        jQuery('.for-space-to-bottom').css("min-height",headerHeight+"px");


        if(jQuery(window).width() >= 992 && !jQuery("body.quickview-index-index").length)
        {
          var scroll = jQuery(window).scrollTop();
          var productImageHeight = jQuery('#product-detail-image > div').outerHeight();
          var productDetailsHeight = jQuery('#product-details-flow').outerHeight();
          // if (jQuery('.owl-carousel').length) {
              if ((productImageHeight) > (productDetailsHeight - scroll)) {
                  jQuery('#product-details-flow').removeClass('offset-66');
                  jQuery('#product-detail-image').removeClass('fix-image');
                  jQuery('#product-detail-image > div').addClass('position-bottom');
              } else {
                  jQuery('#product-details-flow').addClass('offset-66');
                  jQuery('#product-detail-image').addClass('fix-image');
                  jQuery('#product-detail-image > div').removeClass('position-bottom');
              }


            let hidden = true;

            /*zopim -- to change margin from bottom */
            //show footer if mouse is at bottom and not at the end of the scroll
            if(jQuery(window).width() > 768) {
                jQuery(window).mousemove(function (e) {
                    if (!jQuery('body').hasClass('cms-index-index') && !(jQuery(window).scrollTop() + jQuery(window).height() == jQuery(document).height())) {
                        if (e.screenY >= jQuery(window).height() - 50) {
                            if (hidden) {
                                jQuery(".zopim").addClass("bottom-change");
                                jQuery("#footer8").css("height", "59px");
                                hidden = false;
                            }
                        }else if ( !(parseInt(jQuery('.t_Tooltip.t_Tooltip_allure_footer').css('left')) > 0)
                            || (jQuery('.t_Tooltip.t_Tooltip_allure_footer').css('display') == 'none') ) {
                            jQuery(".zopim").removeClass("bottom-change");
                            jQuery("#footer8").css("height", "0px");
                            hidden = true;
                        }
                    }
                });
                //if scroll is at bottom show footer
                if (jQuery(window).scrollTop() + jQuery(window).height() == jQuery(document).height()) {
                    jQuery(".zopim").addClass("bottom-change");
                    jQuery("#footer8").css("height", "60px");
                    hidden = false;
                } else {
                    jQuery(".zopim").removeClass("bottom-change");
                    jQuery("#footer8").css("height", "0px");
                    hidden = true;
                }
            }


            if(jQuery(window).scrollTop() + jQuery(window).height()+100 > jQuery(document).height()) {
                    jQuery(".footer").removeClass("floating");
                }else {
                    jQuery(".footer").addClass("floating");
                }
            /*  zopim -code end-----------------*/

        }else {
            jQuery('#product-details-flow').removeClass('offset-66');
            jQuery('#product-detail-image').removeClass('fix-image');
        }



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


            // }
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

            }else {
                /*scaling close quick view*/
                if(jQuery(".fancybox-desktop.fancybox-type-iframe.fancybox-opened").length)
                {
                      jQuery(".fancybox-item.fancybox-close").trigger("click");
                }

            }
            if (jQuery(window).width() <= 1024 && jQuery('body').hasClass("desktop-device")) {

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
            jQuery(this).addClass('active_menu');
            jQuery('section.sub-menu').hide();
            setTimeout(function() {
                jQuery(".menu_overlay").removeClass("d-none");
                jQuery(section_id).slideDown("slow");
            unScrollBody();
            }, 300);
        }
    });
    jQuery('#navbarNavDropdown').mouseleave(function () {
        setTimeout(function() {
            if (!jQuery('#navbarNavDropdown').is(':hover')) {
                jQuery(".main_menu").removeClass('active_menu');
                jQuery('section.sub-menu').slideUp("slow");
                jQuery(".menu_overlay").addClass("d-none");
                scrollBody();
            }
        }, 600);
    });

    if(!jQuery('body').hasClass('mobile-device')) {
        jQuery('.noChild').mouseover(function () {
            jQuery(".main_menu").removeClass('active_menu');
            jQuery('section.sub-menu').slideUp();
            jQuery(".menu_overlay").addClass("d-none");
            scrollBody();
        });
    }



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

    jQuery(document).on('click','.menu_overlay', function(e){
    	event.stopPropagation();
        if (jQuery(window).width() <= 1024 || jQuery('body').hasClass('mobile-device')) {
            jQuery('#menu-btn').click()
        }
    });

    jQuery('#menu-btn ,.mobile-main_menu .main_menu,.close-section,.mobile-sub_menu .menu-head,.select-currency-mobile,.wishlist,.my-account-mobile').click(function(event){
        event.stopPropagation();
    });
    jQuery("#menu-btn").on("click", function () {
        if(jQuery('#cross-icon').is(':visible')) {
            jQuery("#cross-icon").click();
        }
        if (jQuery('.mobile-main_menu').hasClass('active')) {
            jQuery('section.sub-menu').hide();
        }
        jQuery('body').toggleClass("fancybox-lock");
        if(jQuery(".menu_overlay").hasClass("d-none")){
            jQuery(".menu_overlay").removeClass("d-none")
        }else{
            jQuery(".menu_overlay").addClass("d-none")
        }

        jQuery('.mobile-main_menu').toggleClass('active');
        jQuery(this).toggleClass("change");

        // jQuery("body").prepend(overlay);

    });
    // jQuery('.mobile-main_menu .main_menu').click(function () {
    //     var section_id = '.' + jQuery(this).attr('data-id');
    //     jQuery('section.sub-menu').hide();
    //     jQuery(section_id).show();
    // });
    // jQuery('.close-section').click(function () {
    //     jQuery('section.sub-menu').hide();
    // });

    jQuery(".mobile-sub_menu  #jewelry .menu-head.jwl-head span a").attr("href", "#");

    jQuery(".mobile-main_menu li.parent a").attr("href", "#");


});


/*filter popup start---------------------------------*/

jQuery(window).bind("load resize", function (e) {
   /* var width = jQuery(window).width();
    if ((width < 1023)) {

        if (jQuery('.filter-common-div').length) {

            if(!jQuery('.filterPopup').length) {
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
                jQuery('.filter-common-div').hide();

                jQuery("body").append(filter_popup);
                jQuery(".mb-breadcrumbs .breadcrumbs").prepend(filterButton);

            }
            else {
                jQuery('.filter-common-div').html("");
                jQuery('.filter-common-div').hide();
                jQuery('.filterButton').show();

            }
            jQuery(".filterButton").on('click', function () {
                jQuery(".filterPopup").css({"opacity": "1", "pointer-events": "auto"});
                unScrollBody();
            });

            jQuery(".filterPopup .close").on('click', function () {
                jQuery(".filterPopup").css({"opacity": "0", "pointer-events": "none"});
                scrollBody();
            });
        }
    }else {
        jQuery('.filter-common-div').html(jQuery('.filterPopup .modal-body').html());
        jQuery('.filter-common-div').show();
        jQuery('.filterButton').hide();
    }*/
});
/*filter popup end---------------------------------*/

jQuery(window).bind("load resize scroll", function (e) {

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
