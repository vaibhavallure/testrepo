$(document).ready(function() {

    if ($('.owl-carousel').length) {
        $('#owl-one, #owl-two').owlCarousel({
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
        $('#insta-carousel').owlCarousel({
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

    $(".left-arrow.one").click(function(e) {
        $('#owl-one').trigger('next.owl.carousel');
    });

    $(".right-arrow.one").click(function() {
        $('#owl-one').trigger('prev.owl.carousel');
    });

    $(".left-arrow.two").click(function(e) {
        $('#owl-two').trigger('next.owl.carousel');
    });

    $(".right-arrow.two").click(function() {
        $('#owl-two').trigger('prev.owl.carousel');
    });

    $(".left-arrow.insta").click(function() {
        $('#insta-carousel').trigger('next.owl.carousel');
    });

    $(".right-arrow.insta").click(function() {
        $('#insta-carousel').trigger('prev.owl.carousel');
    });

    var defaultcolrSwatch = 'silver';
    var defaultorientation = 'left';
    var defaultNumber = '1';
    var headerHeight = $(".mariatash-header").outerHeight();
    $('.open-navigation').css("padding-top", headerHeight);

    $(window).scroll(function() {
        var scroll = $(window).scrollTop();
        if (scroll > $(".mariatash-header").outerHeight()) {
            $("#main-logo").addClass('header-item-hide');
            $(".usd-account").addClass('header-item-hide');
            $("#scroll-logo").addClass('header-item-show');
            $('#navbarNavDropdown').addClass('mt-xl-2');
            $('.mariatash-header').addClass('header-height');
        } else {
            $("#main-logo").removeClass('header-item-hide');
            $(".usd-account").removeClass('header-item-hide');
            $("#scroll-logo").removeClass('header-item-show');
            $('#navbarNavDropdown').removeClass('mt-xl-2');
            $('.mariatash-header').removeClass('header-height');
        }

        var productImageHeight = $('#product-detail-image > div').outerHeight();
        var productDetailsHeight = $('#product-details-flow').outerHeight();
        if ($('.owl-carousel').length) {
            if ((productImageHeight) > (productDetailsHeight - scroll)) {
                $('#product-details-flow').removeClass('offset-66');
                $('#product-detail-image').removeClass('fix-image');
                $('#product-detail-image > div').addClass('position-bottom');
            } else {
                $('#product-details-flow').addClass('offset-66');
                $('#product-detail-image').addClass('fix-image');
                $('#product-detail-image > div').removeClass('position-bottom');
            }
        }
        headerHeight = $(".mariatash-header").outerHeight();
        $('.open-navigation').css("padding-top", headerHeight);
    });

    $(".navbar-nav .nav-link, .open-navigation").hover(function() {
            $('.open-navigation').css("display", "block");
            $('.mariatash-header').addClass('dark-header');
        },
        function() {
            $('.open-navigation').css("display", "none");
            $('.mariatash-header').removeClass('dark-header');
        });

    $('.catalog .swatch-item').click(function() {
        $("#catalog-item-image").attr("src", "images/catalog-item-" + $(this).attr("data") + '.png');

        if ($(this).hasClass('active-swatch')) {} else {
            ($('.catalog .swatch-item').removeClass('active-swatch'));
            ($(this).addClass('active-swatch'));
        }
    });

    $('.product-details-flow .swatch-item').click(function() {
        defaultcolrSwatch = $(this).attr("data");
        changeImage();
        if ($(this).hasClass('active-swatch')) {} else {
            ($('.product-details-flow .swatch-item').removeClass('active-swatch'));
            ($(this).addClass('active-swatch'));
        }
    });

    $('.thumbnail-items').click(function() {
        $("#product-image").attr("src", "images/product" + $(this).attr("data") + '-' + defaultcolrSwatch + '-' + defaultorientation + '.png');
        if (($(this).attr("data") == "3") || ($(this).attr("data") == "4")) {
            $("#product-image").attr("src", "images/thumb3" + '-product-image' + '.png')
        }
        if ($(this).hasClass('active-thumbnail')) {} else {
            ($('.thumbnail-items').removeClass('active-thumbnail'));
            ($(this).addClass('active-thumbnail'));
        }
    });

    $('.orientation-option input').click(function() {
        if ($('input[name=orientation]:checked').val() == "left") {
            defaultorientation = 'left';
        } else {
            defaultorientation = 'right';
        }
        changeImage();
    });

    $('.add-to-cart').click(function() {
        $('.add-to-cart').html("<i class='fa fa-circle-o-notch fa-spin'></i> adding");
        setTimeout(function() {
            $('.add-to-cart').html("<i class='fa fa-check'></i> added");
        }, 2000);

        setTimeout(function() {
            $('.add-to-cart').html("add to cart");
        }, 3500);
    });

    $('.steps-header').click(function() {
        $('#' + $(this).attr('data')).toggle();
        $('#' + $(this).attr('data')).parent().toggleClass('active-step');
    });

    $('.shipping-return-header').click(function() {
        var clikId = $(this).prop('data');
        if ($('#' + clikId).length) {
            console.log('if');
            $('#' + $(this).attr('data')).toggle();
        } else {
            console.log('else');
            $('.shipping-return-content').hide();
            $('#' + $(this).attr('data')).toggle();
        }
        // $('.shipping-return-content').hide();
        // $('#' + $(this).attr('data')).toggle();
        // $('#' + $(this).attr('data')).parent().toggleClass('active-accordian');
    });

    $('#view-password').click(function() {
        if ($('#inputPassword').prop('type') === 'password') {
            $('#inputPassword').prop('type', 'text');
        } else {
            $('#inputPassword').prop('type', 'password');
        }

    });

    var options = [];
    $('.dropdown-menu a').on('click', function(event) {
        var $target = $(event.currentTarget),
            val = $target.attr('data-value'),
            $inp = $target.find('input'),
            idx;
        if ((idx = options.indexOf(val)) > -1) {
            options.splice(idx, 1);
            setTimeout(function() { $inp.prop('checked', false) }, 0);
        } else {
            options.push(val);
            setTimeout(function() { $inp.prop('checked', true) }, 0);
        }
        $(event.target).blur();
        return false;
    });

    function changeImage() {
        ($('.thumbnail-items').removeClass('active-thumbnail'));
        ($('#thumb1').parent().addClass('active-thumbnail'));

        $("#thumb1").attr("src", "images/product1-" + defaultcolrSwatch + '-' + defaultorientation + '.png');
        $("#thumb2").attr("src", "images/product2-" + defaultcolrSwatch + '-' + defaultorientation + '.png');
        $("#product-image").attr("src", "images/product" + defaultNumber + '-' + defaultcolrSwatch + '-' + defaultorientation + '.png');
    }

});

function myFunction(x) {
    x.classList.toggle("change");
    $('.mariatash-header').toggleClass('black-bg');
}
