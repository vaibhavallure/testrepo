<?php
/**
 * Apple Pay
 *
 * @category    Allure
 * @package     Allure_ApplePay
 * @copyright   Copyright (c) 2017 Allure Inc
 * @license     http://opensource.org/licenses/Apache-2.0  Apache License, Version 2.0
 */

class Allure_ApplePay_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get config
     */
    public function getConfig()
    {
        return Mage::getSingleton('allure_applepay/config');
    }

    /**
     * Retrieve Merchant ID
     *
     * @return string
     */
    public function getMerchantId()
    {
        return $this->getConfig()->getMerchantId();
    }

    /**
     * Retrieve Merchant Name
     *
     * @return string
     */
    public function getMerchantName()
    {
        return $this->getConfig()->getMerchantName();
    }

    /**
     * Return URL to use for checkout
     *
     * @param $hasToken   Amazon token is passed in query paramaters to log user in
     */
    public function getCheckoutUrl($hasToken = true)
    {
        return Mage::getUrl('checkout/onepage', array('_secure'=>true));
    }

    /**
     * Return onepage checkout URL
     */
    public function getApplePayUrl()
    {
        return Mage::getUrl('applepay', array('_forced_secure'=>true));
    }

    /**
     * Retrieve stand alone URL
     *
     * @return string
     */
    public function getProcessUrl()
    {
        return Mage::getUrl('applepay/process', array('_secure'=>true));
    }

    /**
     * Retrieve customer verify url
     *
     * @return string
     */
    public function getVerifyUrl()
    {
        return $this->_getUrl('amazon_payments/customer/verify');
    }

    /**
     * Clear session data
     */
    public function clearSession()
    {
        Mage::getSingleton('checkout/session')->unsApplePayCheckout();
    }

    /**
     * Retrieve Apple Pay in session
     */
    public function getApplePaySession()
    {
        return Mage::getSingleton('customer/session')->getApplePay();
    }

    /**
     * Get config by website or store admin scope
     */
    public function getAdminConfig($path)
    {
        if ($storeCode = Mage::app()->getRequest()->getParam('store')) {
            return Mage::getStoreConfig($path, $storeCode);
        }
        else if ($websiteCode = Mage::app()->getRequest()->getParam('website')) {
            return Mage::app()->getWebsite($websiteCode)->getConfig($path);
        }
        else {
            return Mage::getStoreConfig($path);
        }
    }

    /**
     * Transform an Apple Pay address into a standard Magento address
     *
     * @param OffAmazonPaymentsService_Model_Address applePayAddress
     */
    public function transformApplePayAddressToMagentoAddress($amazonAddress) {
        $name = $amazonAddress->getName();
        $firstName = substr($name, 0, strrpos($name, ' '));
        $lastName  = substr($name, strlen($firstName) + 1);

        $data['firstname'] = $firstName;
        $data['lastname'] = $lastName;
        $data['country_id'] = $amazonAddress->getCountryCode();
        $data['city'] = $amazonAddress->getCity();
        $data['postcode'] = $amazonAddress->getPostalCode();
        $data['telephone'] = $amazonAddress->getPhone() ? $amazonAddress->getPhone() : $this->__('000-000-0000');

        $data['street'] = array();

        $countryCode = $amazonAddress->getCountryCode();
        $addressLine1 = $amazonAddress->getAddressLine1();
        $addressLine2 = $amazonAddress->getAddressLine2();
        $addressLine3 = $amazonAddress->getAddressLine3();
        if($countryCode && in_array($countryCode, array('AT', 'DE'))){
            if ($addressLine3) {
                $data['company'] = trim($addressLine1.' '.$addressLine2);
                $data['street'][] = $addressLine3;
            } else if ($addressLine2) {
                $data['company'] = $addressLine1;
                $data['street'][] = $addressLine2;
            } else {
                $data['street'][] = $addressLine1;
            }
        } else {
            if ($addressLine1) {
                $data['street'][] = $addressLine1;
            }
            if ($addressLine2) {
                $data['street'][] = $addressLine2;
            }
            if ($addressLine3) {
                $data['street'][] = $addressLine3;
            }
        }
        return $data;
    }

    /**
     * Get admin/default store id
     */
    public function getAdminStoreId()
    {
        if ($code = Mage::getSingleton('adminhtml/config_data')->getStore()) {
            return Mage::getModel('core/store')->load($code)->getId();
        }
        elseif ($code = Mage::getSingleton('adminhtml/config_data')->getWebsite()) {
            $website_id = Mage::getModel('core/website')->load($code)->getId();
            return Mage::app()->getWebsite($website_id)->getDefaultStore()->getId();
        }
        else
        {
            return Mage::app()->getDefaultStoreView()->getId();
        }
    }

    public function isEnabledOnFrontEnd()
    {
        return !Mage::app()->getStore()->isAdmin() && $this->isEnabled() && $this->checkValidIpAddress();
    }

    /**
     * Is Payment Method enabled?
     */
    public function isEnabled()
    {
        return $this->isModuleEnabled() && $this->getConfig()->isEnabled();
    }

    /**
     * Is button bade (acceptance mark) enabled?
     *
     * @return bool
     */
    public function isButtonBadgeEnabled($store = null)
    {
        return ($this->getConfig()->isButtonBadgeEnabled() && $this->getConfig()->isEnabled());
    }

    /**
     * Does user have Amazon order reference for checkout?
     *
     * @return string
     */
    public function isCheckoutApplePaySession()
    {
        return (Mage::getSingleton('checkout/session')->getApplePayCheckout());
    }

    /**
     * Is sandbox mode?
     *
     * @return bool
     */
    public function isApplePaySandbox()
    {
        return $this->getConfig()->isSandbox();
    }

    /**
     * @return mixed|string
     */
    public function checkValidIpAddress()
    {
        if ($this->getConfig()->isRestrictedByIps()) {

        	$whitelistedIPs = $this->getConfig()->getIpWhitelist();

            $whitelistedIPs = trim($whitelistedIPs);

            $whitelistedIPsArray = explode(',', $whitelistedIPs);

        	if (!empty($whitelistedIPsArray)) {

                $validIdIp = false;

                foreach ($whitelistedIPs as $ip) {
                    if ($this->_validateIpAddress($ip) && $ip == $this->_getIpAddress) {
                        $validIdIp = true;
                    }
                }

                return $validIdIp;
        	}
        }

        return true;
    }

    protected function _validateIpAddress($ipAddress)
    {
    	return filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * @return mixed|string
     */
    protected function _getIpAddress()
    {
        if ($this->getConfig()->isProxyMode()) {

        	$proxyIP = $this->getConfig()->getProxyIp();

            $proxyIP = trim($proxyIP);

            if (!empty($proxyIP) && $this->_validateIPAddress($proxyIP)) {
                return $proxyIP;
            }
        }

        return Mage::helper('core/http')->getRemoteAddr();
    }

    /**
     * Does product attribute allow purchase with Apple Pay?
     */
    public function isEnableProductPayments()
    {
        // Viewing single product
        if ($_product = Mage::registry('current_product')) {
            return !$_product->getDisableApplePay();
        }
        // Check cart products
        else {
            $cart = Mage::getModel('checkout/cart')->getQuote();
            foreach ($cart->getAllItems() as $item) {
                $_product = Mage::getModel('catalog/product')->load($item->getProductId());
                if ($_product->getDisableApplePay()) {
                    return false;
                }
            }
            return true;
        }
    }
}
