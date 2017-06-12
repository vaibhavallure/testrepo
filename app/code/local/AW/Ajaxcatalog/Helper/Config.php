<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Ajaxcatalog
 * @version    2.0.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Ajaxcatalog_Helper_Config extends Mage_Core_Helper_Abstract
{
    const GENERAL_IS_ENABLED = 'awajaxcatalog/general/enabled';
    const GENERAL_ACTION_TYPE = 'awajaxcatalog/general/action_type';
    const GENERAL_IS_BACKTOTOP_ENABLED
        = 'awajaxcatalog/general/backtotop_enabled';
    const GENERAL_BACKTOTOP_LABEL = 'awajaxcatalog/general/backtotop_label';


    /**
     * @param Mage_Core_Model_Store|null $store
     *
     * @return bool
     */
    public static function isEnabled($store = null)
    {
        return (bool)Mage::getStoreConfig(self::GENERAL_IS_ENABLED, $store);
    }

    /**
     * @param Mage_Core_Model_Store|null $store
     *
     * @return string
     */
    public static function getActionType($store = null)
    {
        return Mage::getStoreConfig(self::GENERAL_ACTION_TYPE, $store);
    }

    /**
     * @param Mage_Core_Model_Store|null $store
     *
     * @return bool
     */
    public static function isBackToTopEnabled($store = null)
    {
        return (bool)Mage::getStoreConfig(
            self::GENERAL_IS_BACKTOTOP_ENABLED, $store
        );
    }

    /**
     * @param Mage_Core_Model_Store|null $store
     *
     * @return string
     */
    public static function getBackToTopLabel($store = null)
    {
        $value = Mage::getStoreConfig(self::GENERAL_BACKTOTOP_LABEL, $store);
        if (strlen(trim($value)) === 0) {
            $helper = Mage::helper('aw_ajaxcatalog');
            $value = $helper->__('Back to Top');
        }
        return $value;
    }
}