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
        'model/abstract',
        'model/resource-model/magento-rest/integration/rewardpoints/rate',
        'model/resource-model/indexed-db/integration/rewardpoints/rate',
        'model/collection/integration/rewardpoints/rate'
    ],
    function ($, modelAbstract, restResource, indexedDbResource, collection) {
        "use strict";
        return modelAbstract.extend({
            sync_id:'rewardpoint_rates',
            initialize: function () {
                this._super();
                this.setResource(restResource(), indexedDbResource());
                this.setResourceCollection(collection());
            },
        });
    }
);