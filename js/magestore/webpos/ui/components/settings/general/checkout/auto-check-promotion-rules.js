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
        'ui/components/settings/general/element/select'
    ],function (Select) {

        "use strict";
        return Select.extend({
            defaults: {
                elementName: 'os_checkout.auto_check_promotion_rules',
                configPath: 'os_checkout/auto_check_promotion_rules',
            }
        });
    }
);