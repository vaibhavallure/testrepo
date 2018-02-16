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
        'model/config/config',
        'ui/components/settings/synchronization/sync-map',
        'model/synchronization',
        'eventManager',
        'lib/cookie',
        'action/notification/add-notification',
        'mage/translate',
    ],
    function ($, ko, Component, config, syncmap, synchronization, eventManager, Cookies, addNotification, Translate) {
        "use strict";

        return Component.extend({

            isInstall: ko.observable(true),
            message: ko.observable(''),
            number: syncmap.length,
            model: syncmap,
            running: ko.observable(false),
            modelList: ko.observableArray([]),
            percent: ko.observable('0'),

            initialize: function () {
                this._super();
                var self = this;
                self.model.sort(function (a, b) {
                    var x = a.sort_order;
                    var y = b.sort_order;
                    return ((x < y) ? -1 : ((x > y) ? 1 : 0));
                });
                if (Cookies.get('check_login')) {
                    self.isInstall(true);
                } else {
                    self.isInstall(false);
                    self.updateData();
                }
            },

            updateData: function () {
                var self = this;
                var model = this.model.shift();
                if (typeof model == 'undefined') {
                    eventManager.dispatch('finish_install');
                    return this;
                }
                var update = new synchronization(model);

                var endDeferred = $.Deferred();
                update.initialize(endDeferred);
                update.update();
                self.modelList.push(update);
                endDeferred.done(function () {
                    self.percent(parseInt(self.modelList().length * 100 / self.number));
                    self.updateData();
                });
                endDeferred.fail(function () {
                    self.updateData();
                });
            },
            updateAll: function () {
                var self = this;
                var checkNumber = self.modelList().length;
                var updateArray = [];
                $.each(self.modelList(), function (index, model) {
                    self.running(true);
                    model.setMode('finish');
                    updateArray[model.id] = $.Deferred();
                    model.actionText('Updating...');
                    model.doneDeferred = updateArray[model.id];
                    model.processUpdate(updateArray[model.id]);
                    updateArray[model.id].done(function () {
                        checkNumber = checkNumber - 1;
                        if (checkNumber <= 0 && checkNetWork) {
                            location.reload();
                        } else if (checkNumber <= 0) {
                            self.running(false);
                        }
                    });
                    updateArray[model.id].fail(function (error) {
                        checkNumber = checkNumber - 1;
                        if (checkNumber <= 0 && (!checkNetWork || error.statusText == 'error' && error.status == 0)) {
                            checkNetWork = false;
                            console.log('Cannot connect to your server!');
                            addNotification(Translate('Cannot connect to your server!'), true, 'danger', 'Error');
                            self.running(false);
                            return self;
                        } else if (checkNumber <= 0 && checkNetWork) {
                            location.reload();
                        } else if (checkNumber <= 0) {
                            self.running(false);
                        }
                    });
                });
            },
            reloadAll: function() {
                var self = this;
                var checkNumber = self.modelList().length;
                var reloadArray = [];
                $.each(self.modelList(), function (index, model) {
                    self.running(true);
                    model.setMode('install');
                    reloadArray[model.id] = $.Deferred();
                    model.clearData(reloadArray[model.id]);
                    model.doneDeferred = reloadArray[model.id];
                    reloadArray[model.id].done(function () {
                        checkNumber = checkNumber - 1;
                        if (checkNumber <= 0 && checkNetWork) {
                            location.reload();
                        } else if (checkNumber <= 0) {
                            self.running(false);
                        }
                    });
                    reloadArray[model.id].fail(function (error) {
                        checkNumber = checkNumber - 1;
                        if (checkNumber <= 0 && (!checkNetWork || error.statusText == 'error' && error.status == 0)) {
                            checkNetWork = false;
                            console.log('Cannot connect to your server!');
                            addNotification(Translate('Cannot connect to your server!'), true, 'danger', 'Error');
                            self.running(false);
                            return self;
                        } else if (checkNumber <= 0 && checkNetWork) {
                            location.reload();
                        } else if (checkNumber <= 0) {
                            self.running(false);
                        }
                    });
                });
            }
        });
    }
);