if (Prototype.BrowserFeatures.ElementExtensions) {
    var disablePrototypeJS = function (method, pluginsToDisable) {
            var handler = function (event) {
                event.target[method] = undefined;
                setTimeout(function () {
                    delete event.target[method];
                }, 0);
            };
            pluginsToDisable.each(function (plugin) {
                jQuery(window).on(method + '.bs.' + plugin, handler);
            });
        },
        pluginsToDisable = ['collapse', 'dropdown', 'modal', 'tooltip', 'popover'];
    disablePrototypeJS('show', pluginsToDisable);
    disablePrototypeJS('hide', pluginsToDisable);
}

var addProductDirectToCart = function (e) {
	e.preventDefault();
	$ = jQuery;
	$button = $(this);
	$id = $(this).data('id');
	$url = $(this).data('url');

	$canAddToCart = false;

	$configurable = $('#super-attribute-'+$id);

	$selectedConfiguration = false;

	if ($configurable.length) {
		$swatches = $configurable.children('ul');

		if ($swatches.length) {
			$selectedSwatch = $swatches.children('li.active');

			if ($selectedSwatch.length) {
				$canAddToCart = true;

				$selectedConfiguration = $selectedSwatch.data('id');
			}

		} else {
			$canAddToCart = true;
		}
	} else {
		$canAddToCart = true;
	}

	if ($canAddToCart) {
		$location = $url;
		if ($selectedConfiguration) {
			$location = $location.replace($id,$selectedConfiguration);
		}
		//location.href = $location;

		//allure code start
		var productId   = $(this).attr('data-id');
		var data 		= {};
    	var supArr 		= {};
    	var custOpArr 	= {};

    	var metal   				= 209;
        var superAttr 				= jQuery("#super-attribute-"+productId);
        var customOpSelect			= jQuery("#custom-option-select-"+productId);

        if(superAttr.length){
        	 var superAttrVal 			= jQuery("#color-icons-"+productId+" .active").attr("value");
        	 supArr[metal] 			    = superAttrVal;
        	 data['super_attribute'] 	= supArr;
        }

        if(customOpSelect.length){
        	var custName				= customOpSelect.attr("name");
            var custOptId				= customOpSelect.attr("data-option-id");
            var custVal 				= customOpSelect.val();
            custOpArr[custOptId] 	    = custVal;
            data['optionid'] 			= custVal;
            data['options'] 			= custOpArr;
        }

        data['qty'] = 1;

        var dataUrl = $(this).attr('data-url');
        var strJson = JSON.stringify(data);
        var jqxhr = jQuery.get(dataUrl, data, function (data) {
        	window.location.reload();
        });

        //allure code end
	}

	return false;
};

var loadSwatch = function (swatch_id) {

	var $ = jQuery;

	var swatch = $("#productsContainer .super_attribute ul li[id=color-icon-"+swatch_id+"]");

	if (!swatch.length) return;

	var swatchList = swatch.parent();

	var option = swatch.val();

	var product_id = swatchList.data('product_id');

	var imgSrc = $('#img-'+ swatch_id).text();
    var price  = $('#price-'+ swatch_id).html();
    var pv = $('#link-pv-'+product_id).text() + '?optionId=' +option;
    var qv = $('#link-qv-'+product_id).text() + '?optionId=' +option;

    if(price){
        $("#default-price-"+product_id).html(price);
    }

    console.log('#img-'+ product_id);

    if(imgSrc) {
        $('#img-'+ product_id).attr('src',imgSrc);
        $('a#' + product_id).attr('href',pv);
        $('#product-quickview-'+ product_id).attr('href',qv)
    }

    jQuery("#default-price-"+product_id+" .price-box").show();
};

var initSwatches = function () {

	var $ = jQuery;

    $('#productsContainer').on('click','.super_attribute ul li',function() {
        var product_id = $(this).parent().data('product_id');
        var swatch_id = $(this).data('id');
        var option = $(this).attr('value');
        var superAtt  = 'super-attribute-' + product_id;
        var quickview = 'product-quickview-' + product_id;
        var listIds   = $('#list-'+product_id).text();
        console.log('PRODUCT#'+product_id);
        console.log('SWATCH#'+swatch_id);
        // Check configurable
        //if(!listIds) return false;
        if(!listIds) listIds = product_id;

        $(this).siblings().removeClass('active');
        $(this).addClass('active');

        if ($('#list-img-'+product_id).text() == '1') {
        	loadSwatch(swatch_id);
        	return false;
        }


        if ($(this).children('span#img-'+swatch_id).text() == '') {
        	$.ajax({
                type: "POST",
                dataType : "json",
                url: BASE_URL + "ecpcolor/",
                data: {options: listIds, mode: '<?php echo $mode;?>', location: "Boston"}
            }).done(function(response) {
                if (response.colors != '') {
                   $("#color-icons-"+product_id).html(response.colors);
                   $('#list-img-'+product_id).text('1') ;

                   $('#color-icons-' + product_id + ' li').each(function(){
                        var color_id  = $(this).attr('id'),
                            color_title = $(this).attr('title');

                        $(this).attr('data-id', color_id).attr('id', 'color-icon-'+color_id);

                        Tipped.create($(this), color_title, {
                                skin: 'customTiny',
                                showOn: ['click', 'mouseover'],
                                background: { color: '#fff', opacity: .7 }
                        });
                    });

                   $("#color-icon-" + swatch_id).siblings().removeClass('active');
                   $("#color-icon-" + swatch_id).addClass('active');

                   loadSwatch(swatch_id);
                }

                if (response.price != '') {
                    $("#default-price-"+product_id).html(response.price);
                    $("#default-price-"+product_id+" .price-box").show();
                }

            });
        } else {
        	loadSwatch(swatch_id);
        }
    })
};

var priceRangeFrom = 0;

var priceRangeTo= 13500;

var priceAppliedFrom = 0;

var priceAppliedTO = 13500;

var initPriceSlider = function () {

	console.log('INIT PRICE SLIDER');

	console.log(priceRangeFrom+":"+priceRangeTo);
	console.log(priceAppliedFrom+":"+priceAppliedTO);

	ManaPro.filterSuperSlider('price', {
		rangeFrom: priceRangeFrom,
		rangeTo: priceRangeTo,
		appliedFrom: priceAppliedFrom,
		appliedTo: priceAppliedTO,
		numberFormat: "$0",
		appliedFormat: '<span class="fromprice">__0__ </span><span class="toprice">__1__</span>',
		url: '/jewelry/earlobe/where/price/__0__,__1__.html',
		clearUrl: '/jewelry/earlobe.html',
		manualEntry: 0,
		formatThreshold: 0,
	    numberFormat2: "0",
	    existingValues: {},
	    decimalDigits: 0,
	    decimalDigits2: 0
	});
};

var initFilter = function () {

	var $ = jQuery;

	$(".mb-mana-catalog-leftnav").hide();
	$(document).on('click','span.a_sortby.filter_by', function(){
		console.log('FILTER TOGGLE');
		$(".mb-mana-catalog-leftnav").show();

		initPriceSlider();
	});
	$(".mb-mana-catalog-leftnav").on('click','.block-layered-nav i,.block-layered-nav-bg i',  function(){
		console.log('FILTER CLOSE');
		jQuery(".mb-mana-catalog-leftnav").hide();
	});
};

jQuery(document).ready(function($){

	$('.navbar-nav').on('click','ul.dropdown-menu li.dropdown>a',function(e){
		e.stopPropagation();
		e.preventDefault();

		if ($(this).parent().hasClass('open')) {
			$(this).parent().removeClass('open');
			$(this).next().slideUp('slow');
		} else {
			$(this).parent().addClass('open');
			$(this).next().slideDown('slow');
		}

	});

	$('.scrollTop').click(function () {
		$("html, body").animate({
		    scrollTop: 0
		}, 600);
		return false;
	});

	$(".actions").on('click','.btn-directcart',addProductDirectToCart);

	if ($(".mb-mana-catalog-leftnav").length) {

		initFilter();
		initPriceSlider();
	}

	$("span.a_sortby.filter_by").click( function(){ initPriceSlider(); });

	if ($('ul.messages li').length) {
		$('ul.messages').fadeIn();
	}

	$('.footlinks>li>a').on('click',function(){
		$parent = $(this).parent();
		$active = $parent.hasClass('active');

		$('.footlinks>li').removeClass('active');

		if (!$active) {
			$parent.addClass('active');
			$(this).next().slideDown();
		} else {
			$parent.removeClass('active');
			$(this).next().slideUp();
		}
	});

	$('.owl-carousel').each(function(){

		var _this = $(this),
			options = _this.data('owl-carousel-options') ? _this.data('owl-carousel-options') : {},
			buttons = _this.data('nav'),
			config = $.extend(options,{
				dragEndSpeed : 500,
				smartSpeed : 500
			});

		var owl = _this.owlCarousel(config);

		$('.' + buttons + 'prev').on('click',function(){
			owl.trigger('prev.owl.carousel');
		});
		$('.' + buttons + 'next').on('click',function(){
			owl.trigger('next.owl.carousel');
		});

	});

	if ($('.sidebar-discard').length) {

        $("<div id='sns_right'></div>").prependTo('body');
        $("<div class='sns_overlay'></div>").prependTo('body');
        $('.sidebar').prependTo('#sns_right');

        $('.filter_by').live('click', function(event){
                event.preventDefault();
                if($('#sns_right').hasClass('active')){
                        $('.sns_overlay').fadeOut(250);
                        $('#sns_right').removeClass('active');
                        $('body').removeClass('show-sidebar');
                } else {
                        $('#sns_right').addClass('active');
                        $('.sns_overlay').fadeIn(250);
                        $('body').addClass('show-sidebar');
                }
        });

        $('#sns_right icon#close-icon').live('click', function(){
                if($('#sns_right').hasClass('active')){
                        $('.sns_overlay').fadeOut(250).hide();
                        $('#sns_right').removeClass('active');
                        $('body').removeClass('show-sidebar');
                } else {
                        $('#sns_right').addClass('active');
                        $('.sns_overlay').fadeIn(250);
                        $('body').addClass('show-sidebar');
                }
        });
	}

	$("#sns_tab_products ul.nav-tabs li a").click(function(){});
});