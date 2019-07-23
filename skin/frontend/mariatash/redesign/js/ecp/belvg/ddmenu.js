;
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
/**********************************************
 *        MAGENTO EDITION USAGE NOTICE        *
 **********************************************/
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/**********************************************
 *        DISCLAIMER                          *
 **********************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 **********************************************
 * @category   Belvg
 * @package    Belvg_DropDownMenu
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */
(function($){
    $.fn.ddmenu = function(options){

        /**
         * Default settings
         */
        var settings = $.extend({
            fly:           true,
            opacity:       '0.5',
            transitionIn:  'fade',
            transitionOut: 'fade',
            speedIn:        300,
            speedOut:       200,
            effectLinear:  'linear',
            effectIn:      'easeOutBack',
            effectOut:     'easeInBack',
            hoverOverDelay: 300,
            hoverOutDelay:  100
        }, options);
        settings.overlaySpeedIn = Math.round(settings.speedIn / 2);

        var methods = {
            /**
             * Event to open the menu
             */
            hoverOver: function(li){
                var sub = $(li).find(".sub");
                if(sub.length){
                    methods.overlayShow();
                    sub.stop().parent('li').css('z-index','102');
                    sub.attr('style', 'opacity:0').show();
                    methods.resize(li);
                    sub.hide();

                    switch(settings.transitionIn){
                        case 'fade':
                            sub.fadeTo(settings.speedIn, 1, settings.effectIn, function(){
                                $(this).show();
                            });
                            break;
                        case 'slide':
                            sub.css({'opacity':1});
                            sub.slideDown(settings.speedIn, settings.effectIn);
                            break;
                        default:
                            sub.css({'opacity':1});
                            sub.show();
                    }
                }
                $(li).addClass('active');
            },

            /**
             * Event to close the menu
             */
            hoverOut: function(li){
                var sub = $(li).find(".sub");
                if(sub.length){
                    sub.stop().parent('li').css('z-index','101');

                    switch(settings.transitionOut){
                        case 'fade':
                            sub.fadeTo(settings.speedOut, 0, settings.effectOut, function(){
                                $(this).hide();
                            });
                            break;
                        case 'slide':
                            sub.slideUp(settings.speedOut, settings.effectOut);
                            break;
                        default:
                            sub.hide();
                    }
                    methods.overlayHide();
                }
                $(li).removeClass('active');
            },

            /**
             * Event to show overlay
             */
            overlayShow: function(){
                if($('#overlay-nav').length){
                    $('#overlay-nav').css('height',$(window).height()).stop().fadeTo(settings.overlaySpeedIn, settings.opacity, settings.effectLinear, function(){
                        $(this).show();
                    });
                }
            },

            /**
             * Event to hide overlay
             */
            overlayHide: function(){
                if($('#overlay-nav').length){
                    $('#overlay-nav').stop().fadeTo(settings.speedOut, 0, settings.effectLinear, function(){
                        $(this).hide();
                    });
                }
            },

            /**
             * Change submenu properties
             */
            resize: function(li){
                methods.setNavWidth(li);
                methods.setLeftPos(li);
                //methods.setNavHeight(li);
            },

            /**
             * Change submenu width
             */
            setNavWidth: function(li){
                var rowWidth    = 0;
                rowWidth = $(li).find("table").width();
                $(li).find(".sub").css({'width':rowWidth});
            },

            /**
             * Change submenu height
             */
            setNavHeight: function(li){
                var rowHeight   = parseInt($("#top-nav .sub").height());
                $(li).find(".sub > ul").each(function(){
                    $(this).css({'height':rowHeight});
                });
            },

            /**
             * Change position submenu (depending on the width)
             */
            setLeftPos: function(li){
                var liWidth     = parseInt($(li).find(".sub").width());
                var liPos       = liWidth + parseInt($(li).find(".sub").offset().left);
                var navWidth    = parseInt($("#top-nav").width()) + parseInt($("#top-nav").css('padding-left')) + parseInt($("#top-nav").css('padding-right'));
                var navPos      = navWidth + parseInt($("#top-nav").offset().left) - 2;

                if(liPos > navPos){
                    if(liWidth > navWidth){
                        // center
                        $(li).find(".sub").css('left', navPos - liPos + 1 + parseInt((liWidth-navWidth)/2));
                    }else{
                        // right
                        $(li).find(".sub").css('left', navPos - liPos);
                    }
                }
            }
        };

        $('#overlay-nav').css({'opacity':0}).hide();

        return this.each(function() {
            var $this       = $(this);

            /**
             * Initialization Menu
             */
            var li_cache, over = false;
            $this.find("> li").hover(
                function (e){
                    var $li     = $(this), speed;

                    if(li_cache === this && over){
                        $.doTimeout("hoverOut");
                        return;
                    }
                    if(over){
                        $.doTimeout("hoverOut", true);
                        speed = 0;
                    }else{
                        $.doTimeout("hoverOut");
                        speed = settings.hoverOverDelay;
                    }

                    $.doTimeout("hoverIn", speed, function(){
                        over    = true;
                        methods.hoverOver($li);
                    });
                }, function(e){
                    var $li = $(this);

                    $.doTimeout("hoverIn" );
                    $.doTimeout("hoverOut", settings.hoverOutDelay, function(){
                        over = false;
                        methods.hoverOut($li);
                    });
                }
            );

            $this.find("li.sub-menu").find(".sub > table td:last").addClass('last');

            /**
             * Initialization Floating Menu
             */
            if(settings.fly){
                var nav_top_pos = $this.offset().top;
                $(window).scroll(function() {
                    if($(window).scrollTop() > nav_top_pos) {
                        if(!$this.hasClass('ddmenu_fly')) {
                            $('#top-nav-ddmenu_fly_bg').show();
                            $this.css('left', $this.offset().left+'px').addClass('ddmenu_fly');
                        };
                    }else{
                        if($this.hasClass('ddmenu_fly')) {
                            $('#top-nav-ddmenu_fly_bg').hide();
                            $this.removeClass('ddmenu_fly');
                        }
                    }
                });
            }
        });

    };

})(jBelvgDd);

;