jQuery(document).ready(function() {

    if (jQuery('.owl-carousel').length) {
        jQuery('#owl-one, #owl-two').owlCarousel({
            loop: true,
            margin: 10,
            nav: false,
            responsiveClass: true,
            responsive: {
                0: {
                    items: 1,
                },
                600: {
                    items: 2,
                },
                992: {
                    items: 4,
                },
                1200: {
                    items: 4,
                }
            }
        });
        jQuery('#insta-carousel').owlCarousel({
            loop: true,
            margin: 10,
            nav: false,
            responsiveClass: true,
            responsive: {
                0: {
                    items: 1,
                },
                600: {
                    items: 2,
                },
                992: {
                    items: 4,
                },
                1200: {
                    items: 5,
                }
            }
        });
    }

    jQuery(".left-arrow.one").click(function(e) {
        jQuery('#owl-one').trigger('next.owl.carousel');
    });

    jQuery(".right-arrow.one").click(function() {
        jQuery('#owl-one').trigger('prev.owl.carousel');
    });

    jQuery(".left-arrow.two").click(function(e) {
        jQuery('#owl-two').trigger('next.owl.carousel');
    });

    jQuery(".right-arrow.two").click(function() {
        jQuery('#owl-two').trigger('prev.owl.carousel');
    });

    jQuery(".left-arrow.insta").click(function() {
        jQuery('#insta-carousel').trigger('next.owl.carousel');
    });

    jQuery(".right-arrow.insta").click(function() {
        jQuery('#insta-carousel').trigger('prev.owl.carousel');
    });


    jQuery(".menu-head").click(function(){
        jQuery(this).find("a").toggleClass('active');
        jQuery(this).parent().find(".head-child").toggle();
    });


    var defaultcolrSwatch = 'silver';
    var defaultorientation = 'left';
    var defaultNumber = '1';
    var headerHeight = jQuery(".mariatash-header").outerHeight();
    jQuery('.open-navigation').css("padding-top", headerHeight);

    jQuery(window).scroll(function() {
        var scroll = jQuery(window).scrollTop();
        if (scroll > jQuery(".mariatash-header").outerHeight()) {
            jQuery(".mt-logo").addClass('header-item-hide');
            jQuery(".nav-links-left").addClass('header-item-hide');
            jQuery("#scroll-logo").addClass('header-item-show');
            jQuery('.mariatash-header').addClass('header-height');
        } else {
            jQuery(".mt-logo").removeClass('header-item-hide');
            jQuery(".nav-links-left").removeClass('header-item-hide');
            jQuery("#scroll-logo").removeClass('header-item-show');
            jQuery('.mariatash-header').removeClass('header-height');
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

    jQuery(".navbar-nav .nav-link, .open-navigation").hover(function() {
            jQuery('.open-navigation').css("display", "block");
            jQuery('.mariatash-header').addClass('dark-header');
        },
        function() {
            jQuery('.open-navigation').css("display", "none");
            jQuery('.mariatash-header').removeClass('dark-header');
        });

    jQuery('.catalog .swatch-item').click(function() {
        jQuery("#catalog-item-image").attr("src", "images/catalog-item-" + jQuery(this).attr("data") + '.png');

        if (jQuery(this).hasClass('active-swatch')) {} else {
            (jQuery('.catalog .swatch-item').removeClass('active-swatch'));
            (jQuery(this).addClass('active-swatch'));
        }
    });

    jQuery('.product-details-flow .swatch-item').click(function() {
        defaultcolrSwatch = jQuery(this).attr("data");
        changeImage();
        if (jQuery(this).hasClass('active-swatch')) {} else {
            (jQuery('.product-details-flow .swatch-item').removeClass('active-swatch'));
            (jQuery(this).addClass('active-swatch'));
        }
    });

    jQuery('.thumbnail-items').click(function() {
        jQuery("#product-image").attr("src", "images/product" + jQuery(this).attr("data") + '-' + defaultcolrSwatch + '-' + defaultorientation + '.png');
        if ((jQuery(this).attr("data") == "3") || (jQuery(this).attr("data") == "4")) {
            jQuery("#product-image").attr("src", "images/thumb3" + '-product-image' + '.png')
        }
        if (jQuery(this).hasClass('active-thumbnail')) {} else {
            (jQuery('.thumbnail-items').removeClass('active-thumbnail'));
            (jQuery(this).addClass('active-thumbnail'));
        }
    });

    jQuery('.orientation-option input').click(function() {
        if (jQuery('input[name=orientation]:checked').val() == "left") {
            defaultorientation = 'left';
        } else {
            defaultorientation = 'right';
        }
        changeImage();
    });

    jQuery('.add-to-cart').click(function() {
        jQuery('.add-to-cart').html("<i class='fa fa-circle-o-notch fa-spin'></i> adding");
        setTimeout(function() {
            jQuery('.add-to-cart').html("<i class='fa fa-check'></i> added");
        }, 2000);

        setTimeout(function() {
            jQuery('.add-to-cart').html("add to cart");
        }, 3500);
    });

    jQuery('.steps-header').click(function() {
        jQuery('#' + jQuery(this).attr('data')).toggle();
        jQuery('#' + jQuery(this).attr('data')).parent().toggleClass('active-step');
    });

    jQuery('.shipping-return-header').click(function() {
        var clikId = jQuery(this).prop('data');
        if (jQuery('#' + clikId).length) {
            console.log('if');
            jQuery('#' + jQuery(this).attr('data')).toggle();
        } else {
            console.log('else');
            jQuery('.shipping-return-content').hide();
            jQuery('#' + jQuery(this).attr('data')).toggle();
        }
        // jQuery('.shipping-return-content').hide();
        // jQuery('#' + jQuery(this).attr('data')).toggle();
        // jQuery('#' + jQuery(this).attr('data')).parent().toggleClass('active-accordian');
    });

    jQuery('#view-password').click(function() {
        if (jQuery('#inputPassword').prop('type') === 'password') {
            jQuery('#inputPassword').prop('type', 'text');
        } else {
            jQuery('#inputPassword').prop('type', 'password');
        }

    });

    var options = [];
    jQuery('.dropdown-menu a').on('click', function(event) {
        var jQuerytarget = jQuery(event.currentTarget),
            val = jQuerytarget.attr('data-value'),
            jQueryinp = jQuerytarget.find('input'),
            idx;
        if ((idx = options.indexOf(val)) > -1) {
            options.splice(idx, 1);
            setTimeout(function() { jQueryinp.prop('checked', false) }, 0);
        } else {
            options.push(val);
            setTimeout(function() { jQueryinp.prop('checked', true) }, 0);
        }
        jQuery(event.target).blur();
        return false;
    });

    function changeImage() {
        (jQuery('.thumbnail-items').removeClass('active-thumbnail'));
        (jQuery('#thumb1').parent().addClass('active-thumbnail'));

        jQuery("#thumb1").attr("src", "images/product1-" + defaultcolrSwatch + '-' + defaultorientation + '.png');
        jQuery("#thumb2").attr("src", "images/product2-" + defaultcolrSwatch + '-' + defaultorientation + '.png');
        jQuery("#product-image").attr("src", "images/product" + defaultNumber + '-' + defaultcolrSwatch + '-' + defaultorientation + '.png');
    }




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
    jQuery('.main_menu').click(function(){
        var section_id='.'+jQuery(this).attr('data-id');
        jQuery('section.sub-menu').hide();
        jQuery(section_id).show();
    });
    jQuery('.close-section').click(function(){
        jQuery('section.sub-menu').hide();
    });

    jQuery(".mobile-sub_menu  #jewelry .menu-head.jwl-head span a").attr("href","#");


});

function myFunction(x) {
    x.classList.toggle("change");
    //jQuery('.mariatash-header').toggleClass('black-bg');
}

