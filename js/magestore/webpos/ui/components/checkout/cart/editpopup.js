/*
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Webpos
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

define(
    [
        'jquery',
        'ko',
        'posComponent',
        'model/checkout/cart/items/item/interface',
        'model/checkout/cart/editpopup',
        'helper/price',
        'helper/staff'
    ],
    function ($,ko, Component, ItemInterface, EditPopupModel, HelperPrice, Staff) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'ui/checkout/cart/editpopup'
            },
            focusQtyInput: true,
            initialize: function () {
                this._super();
                var self = this;
                this.hasCustomAmount = ko.pureComputed(function(){
                    return (EditPopupModel.getData("has_custom_price") == true)?true:false;
                });
                this.isCustomPrice = ko.pureComputed(function(){
                    return (EditPopupModel.getData("custom_type") == ItemInterface.CUSTOM_PRICE_CODE && self.hasCustomAmount() == true)?true:false;
                });
                this.isCustomDiscount = ko.pureComputed(function(){
                    return (EditPopupModel.getData("custom_type") == ItemInterface.CUSTOM_DISCOUNT_CODE && self.hasCustomAmount() == true)?true:false;
                });
                this.isTypeFixed = ko.pureComputed(function(){
                    return (EditPopupModel.getData("custom_price_type") == ItemInterface.FIXED_AMOUNT_CODE)?true:false;
                });
                this.isTypePercent = ko.pureComputed(function(){
                    return (EditPopupModel.getData("custom_price_type") == ItemInterface.PERCENTAGE_CODE)?true:false;
                });
                this.timeout = "";
            },
            incQty: function(){
                var qty_increment = HelperPrice.toNumber((EditPopupModel.getData('qty_increment'))?EditPopupModel.getData('qty_increment'):1);
                var qty = HelperPrice.toNumber(this.getQty());
                qty = qty + qty_increment;
                EditPopupModel.setData('qty',qty);
            },
            descQty: function(){
                var qty_increment = HelperPrice.toNumber((EditPopupModel.getData('qty_increment'))?EditPopupModel.getData('qty_increment'):1);
                var qty = HelperPrice.toNumber(this.getQty());
                qty = qty - qty_increment;
                EditPopupModel.setData('qty',qty);
            },
            modifyQty: function(data,event){
                var qty = event.target.value;
                var maximum_qty = EditPopupModel.getData('maximum_qty');
                if(maximum_qty && qty > maximum_qty){
                    event.target.value = maximum_qty;
                    qty = maximum_qty;
                }
                var minimum_qty = EditPopupModel.getData('minimum_qty');
                if(minimum_qty && qty < minimum_qty){
                    event.target.value = minimum_qty;
                    qty = minimum_qty;
                }else if(!minimum_qty && qty <= 0){
                    event.target.value = 1;
                    qty = 1;
                }
                EditPopupModel.setData('qty',HelperPrice.toNumber(qty));
            },
            getProductName: function(){
                return EditPopupModel.getData('product_name');
            },
            getProductImageUrl: function(){
                return EditPopupModel.getData('image_url');
            },
            getQty: function(){
                return EditPopupModel.getData('qty');
            },
            getCustomPriceAmount: function(){
                return EditPopupModel.getData('custom_price_amount');
            },
            getCurrencySymbol: function(){
                return window.webposConfig.currentCurrencySymbol;
            },
            customPrice: function(){
                if(EditPopupModel.getData('has_custom_price') == true && EditPopupModel.getData('custom_type') == ItemInterface.CUSTOM_PRICE_CODE){
                    EditPopupModel.setData('has_custom_price',false);
                }else{
                    EditPopupModel.setData('has_custom_price',true);
                    EditPopupModel.setData('custom_type',ItemInterface.CUSTOM_PRICE_CODE);
                    if(!EditPopupModel.getData('custom_price_type')){
                        EditPopupModel.setData('custom_price_type',ItemInterface.FIXED_AMOUNT_CODE);
                    }
                }
            },
            customDiscount: function(){
                if(EditPopupModel.getData('has_custom_price') == true && EditPopupModel.getData('custom_type') == ItemInterface.CUSTOM_DISCOUNT_CODE){
                    EditPopupModel.setData('has_custom_price',false);
                }else{
                    EditPopupModel.setData('has_custom_price',true);
                    EditPopupModel.setData('custom_type',ItemInterface.CUSTOM_DISCOUNT_CODE);
                    if(!EditPopupModel.getData('custom_price_type')){
                        EditPopupModel.setData('custom_price_type',ItemInterface.FIXED_AMOUNT_CODE);
                    }
                }
            },
            setTypeFixed: function(){
                EditPopupModel.setData('custom_price_type',ItemInterface.FIXED_AMOUNT_CODE);
            },
            setTypePercent: function(){
                EditPopupModel.setData('custom_price_type',ItemInterface.PERCENTAGE_CODE);
            },
            modifyPrice: function(data,event){
                clearTimeout(this.timeout);
                this.timeout = setTimeout(function () {
                    EditPopupModel.setData('custom_price_amount',HelperPrice.toNumber(event.target.value));
                }, 1000);
            },
            canAddDiscount: function(){
                return (Staff.isHavePermission("Magestore_Webpos::all_discount") || Staff.isHavePermission("Magestore_Webpos::apply_discount_per_item"));
            },
            canAddCustomPrice: function(){
                return (Staff.isHavePermission("Magestore_Webpos::all_discount") || Staff.isHavePermission("Magestore_Webpos::apply_custom_price"));
            }
        });
    }
);