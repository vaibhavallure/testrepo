<?php
/**
 * @category   Allure
 * @package    Allure_CurrencyManager
 * @copyright  Copyright (c) 2016 Allure Inc (http://www.allureinc.co)
 * @contacts   support@allureinc.co
 */

class Allure_CurrencyManager_Block_Js  extends Mage_Core_Block_Template
{
    public function getJsonConfig()
    {
        if (method_exists(Mage::helper('core'), 'jsonEncode')) {
            return Mage::helper('core')->jsonEncode(
                Mage::helper('currencymanager')->getOptions(
                    array(),
                    false,
                    Mage::app()->getStore()->getCurrentCurrencyCode()
                )
            );
        } else {
            return Zend_Json::encode(
                Mage::helper('currencymanager')->getOptions(
                    array(),
                    false,
                    Mage::app()->getStore()->getCurrentCurrencyCode()
                )
            );
        }
    }
}
