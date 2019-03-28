<?php
/**
 * Apple Pay
 *
 * @category    Allure
 * @package     Allure_ApplePay
 * @copyright   Copyright (c) 2017 Allure Inc
 * @license     http://opensource.org/licenses/Apache-2.0  Apache License, Version 2.0
 */

class Allure_ApplePay_Block_Script extends Mage_Core_Block_Template
{
    /**
     * Disable loading applepay.js for <default> (every page) if cart is empty
     */
    protected function _afterToHtml($html)
    {
        if ($this->getIsDefault() && Mage::helper('checkout/cart')->getItemsCount() == 0) {
            return;
        }
        else {
            return $html;
        }
    }

    /**
     * Is enabled?
     */
    public function isEnabled()
    {
        return Mage::helper('allure_applepay')->isEnabled();
    }

    /**
     * Is loaded?
     */
    public function isLoaded()
    {
        if ($this->getIsLoaded()) {
            return true;
        }
        $this->setIsLoaded(true);
        return false;
    }
    
    public function getMethod()
    {
        return Mage::getSingleton('allure_applepay/method');
    }
    
    public function getConfigData($field, $storeId = null)
    {
        return $this->getMethod()->getConfigData($field, $storeId);
    }
}
