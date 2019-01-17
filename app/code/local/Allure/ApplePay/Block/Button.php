<?php
/**
 * Apple Pay
 *
 * @category    Allure
 * @package     Allure_ApplePay
 * @copyright   Copyright (c) 2017 Allure Inc
 * @license     http://opensource.org/licenses/Apache-2.0  Apache License, Version 2.0
 */

class Allure_ApplePay_Block_Button extends Mage_Core_Block_Template
{
    /**
     * Return URL to use for checkout
     */
    public function getCheckoutUrl()
    {
        return $this->helper('allure_applepay')->getCheckoutUrl();
    }

    /**
     * Return onepage checkout URL
     */
    public function getOnepageCheckoutUrl()
    {
        return $this->helper('allure_applepay')->getOnepageCheckoutUrl();
    }

    /**
     * Return CSS identifier to use for Apple Pay button
     */
    public function getApplePayButtonId() {
        return $this->getNameInLayout();
    }

    /**
     * Return Merchant ID
     */
    public function getMerchantId()
    {
        return $this->helper('allure_applepay')->getMerchantId();
    }

    /**
     * Return Merchant Name
     */
    public function getMerchantName()
    {
        return $this->helper('allure_applepay')->getMerchantName();
    }

    /**
     * Get additional login scope
     */
    public function getAdditionalScope()
    {
         return $this->helper('allure_applepay')->getAdditionalScope();
    }

    /**
     * Get button type
     */
    public function getButtonType()
    {
         return Mage::getSingleton('allure_applepay/config')->getButtonType();
    }

    /**
     * Get button size
     */
    public function getButtonSize()
    {
         return Mage::getSingleton('allure_applepay/config')->getButtonSize();
    }

    /**
     * Get button color
     */
    public function getButtonColor()
    {
         return Mage::getSingleton('allure_applepay/config')->getButtonColor();
    }

    /**
     * Is Disabled?
     *
     * @return bool
     */
    public function isDisabled()
    {
        return !Mage::getSingleton('checkout/session')->getQuote()->validateMinimumAmount();
    }

    /**
     * Is button enabled?
     *
     * @return bool
     */
    public function isApplePayButtonEnabled()
    {
        if (!Mage::getSingleton('allure_applepay/config')->isEnabled()) {
            return false;
        } else if (Mage::registry('current_product')) { // Viewing single product
			// Skip Gift Card
			if (Mage::registry('current_product')->getId() == 43357) return false;

            return $this->helper('allure_applepay')->isEnableProductPayments();

        } else {
            return ($this->helper('allure_applepay')->isEnableProductPayments() && (!Mage::getSingleton('allure_applepay/config')->isCheckoutOnepage() || Mage::getSingleton('allure_applepay/config')->showPayOnCart()));
        }
    }

    /**
     * Is button badge enabled?
     *
     * @return bool
     */
    public function isButtonBadgeEnabled()
    {
        return $this->helper('allure_applepay')->isButtonBadgeEnabled();
    }

    /**
     * Is Apple Pay enabled on product level?
     */
    public function isEnableProductPayments()
    {
        return $this->helper('allure_applepay')->isEnableProductPayments();
    }

    /**
     * Is popup window?
     *
     * @return bool
     */
    public function isPopup()
    {
        // Use redirect for sidecart/minicart pay button
        if ($this->getNameInLayout() == 'ApplePayButtonSideCart'
            && !Mage::app()->getStore()->isCurrentlySecure()
            ) {
            return 0;
        }

        return ($this->helper('allure_applepay')->isPopup());
    }

    /**
     * Is tokenized payments enabled?
     *
     * @return bool
     */
    public function isTokenEnabled()
    {
        return Mage::getSingleton('allure_applepay/config')->isTokenEnabled();
    }

}
