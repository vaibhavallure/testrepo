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
        'observer/catalog/category/load-product-by-category',
        'observer/synchronization/model-save-after',
        // 'observer/synchronization/model-massupdate-after',
        'observer/checkout/cart-item-remove-after',
        // 'observer/integration/rewardpoints/sync-prepare-maps'
    ],
    function ($,
              loadProductByCategory,
              modelSaveAfter,
              // modelMassUpdateAfter,
              cartItemRemoveAfter
              // rewardpointsSyncPrepareMaps
    ) {
        "use strict";

        return {
            processEvent: function() {
                loadProductByCategory.execute();
                modelSaveAfter.execute();
                // modelMassUpdateAfter.execute();
                cartItemRemoveAfter.execute();
                // rewardpointsSyncPrepareMaps.execute();
            }
        };
    }
);