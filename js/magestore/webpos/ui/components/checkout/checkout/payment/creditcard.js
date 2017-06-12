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
        'ko',
        'jquery',
        'underscore',
        'posComponent',
        'model/checkout/checkout/payment',
        'dataManager',
        'model/checkout/checkout/payment/creditcard',
        'model/checkout/checkout/payment/cardswiper'
    ],
    function (ko, $, _, Component, PaymentModel, DataManager, CreditCard, CardSwiper) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'ui/checkout/checkout/payment/creditcard'
            },
            ccMethods: PaymentModel.ccMethods,
            showCcForm: PaymentModel.showCcForm,
            cc_owner: ko.pureComputed(function(){
                return CreditCard.info.cc_owner();
            }),
            cc_type: ko.pureComputed(function(){
                return CreditCard.info.cc_type();
            }),
            cc_number: ko.pureComputed(function(){
                return CreditCard.info.cc_number();
            }),
            cc_exp_month: ko.pureComputed(function(){
                return CreditCard.info.cc_exp_month();
            }),
            cc_exp_year: ko.pureComputed(function(){
                return CreditCard.info.cc_exp_year();
            }),
            cc_cid: ko.pureComputed(function(){
                return CreditCard.info.cc_cid();
            }),
            removeCcMethod: function (data, event) {
                PaymentModel.removeCCmethod(data);
                CreditCard.resetData();
            },
            getCcYearsValues: function() {
                return _.map(DataManager.getData('cc_years'), function(value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            },
            getCcMonthsValues: function() {
                return _.map(DataManager.getData('cc_months'), function(value, key) {
                    return {
                        'value': key,
                        'month': value
                    }
                });
            },
            getCcTypesValues: function() {
                var self = this;
                var types = DataManager.getData('cc_types');
                if(self.ccMethods().length > 0){
                    var method = ko.utils.arrayFirst(self.ccMethods(), function(){
                        return true;
                    });
                    if(method && method.type != 0){
                        types = $.extend({'':''}, method.type);
                    }
                }
                return _.map(types, function(value, key) {
                    return {
                        'value': key,
                        'type': value
                    }
                });
            },
            afterRenderForm: function(){
                CardSwiper.initSwipe('webpos');
            },
            saveData: function(data, event){
                var element = event.target;
                if(element && element.name){
                    CreditCard.setData(element.name, element.value);
                }
            }
        });
    }
);