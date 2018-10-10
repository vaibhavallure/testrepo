<?php
/**
 * Apple Pay
 *
 * @category    Allure
 * @package     Allure_ApplePay
 * @copyright   Copyright (c) 2017 Allure Inc
 * @license     http://opensource.org/licenses/Apache-2.0  Apache License, Version 2.0
 */

class Allure_ApplePay_Model_Config
{
    /**#@+
     * Paths to Payment Method config
     */

    const CONFIG_XML_PATH_ENABLED        = 'payment/applepay/active';
    const CONFIG_XML_PATH_MERCHANT_ID    = 'payment/applepay/merchant_id';
    const CONFIG_XML_PATH_MERCHANT_NAME  = 'payment/applepay/merchant_name';
    const CONFIG_XML_PATH_SANDBOX        = 'payment/applepay/sandbox';
    const CONFIG_XML_PATH_DEBUG          = 'payment/applepay/debug';
    const CONFIG_XML_PATH_PAYMENT_ACTION = 'payment/applepay/payment_action';
    const CONFIG_XML_PATH_ORDER_STATUS   = 'payment/applepay/order_status';
    const CONFIG_XML_PATH_CHECKOUT_PAGE  = 'payment/applepay/checkout_page';
    const CONFIG_XML_PATH_SHOW_PAY_CART  = 'payment/applepay/show_pay_cart';
    const CONFIG_XML_PATH_SECURE_CART    = 'payment/applepay/secure_cart';
    const CONFIG_XML_PATH_SHOW_COUPON    = 'payment/applepay/show_coupon';

    const CONFIG_XML_PATH_RESTRICTED_IPS = 'payment/applepay/restricted_ips';

    const CONFIG_XML_PATH_BUTTON_TYPE    = 'payment/applepay/button_type';
    const CONFIG_XML_PATH_BUTTON_COLOR   = 'payment/applepay/button_color';
    const CONFIG_XML_PATH_BUTTON_SIZE    = 'payment/applepay/button_size';
    const CONFIG_XML_PATH_BUTTON_BADGE   = 'payment/applepay/button_badge';

    /**
     * Retrieve config value for store by path
     *
     * @param string $path
     * @param mixed $store
     * @return mixed
     */
    protected function _getStoreConfig($path, $store)
    {
        return Mage::getStoreConfig($path, $store);
    }

    /**
     * Is sandbox?
     *
     * @param   store $store
     * @return  bool
     */
    public function isSandbox($store = null)
    {
        return (bool) $this->_getStoreConfig(self::CONFIG_XML_PATH_SANDBOX, $store);
    }

    /**
     * Is module enabled?
     *
     * @param   store $store
     * @return  bool
     */
    public function isEnabled($store = null)
    {
        $device = Mage::helper('allure_applepay/device');

        if (!$device->isMobile() || $device->isTablet() || !$device->isiOS() || !$device->isSafari()) {
            return false;
        }

        // Check for IP Restriction
        if ($this->_getStoreConfig(self::CONFIG_XML_PATH_RESTRICTED_IPS, $store)) {
            if ( !Mage::helper('core')->isDevAllowed() ) {
                return false;
            }
        }

        return ($this->_getStoreConfig(self::CONFIG_XML_PATH_ENABLED, $store));
    }

    /**
     * Is debug mode enabled?
     *
     * @param   store $store
     * @return  bool
     */
    public function isDebugMode($store = null)
    {
        return (bool) $this->_getStoreConfig(self::CONFIG_XML_PATH_DEBUG, $store);
    }

    /**
     * Get seller/merchant ID
     *
     * @param   store $store
     * @return  string
     */
    public function getMerchantId($store = null)
    {
        return trim($this->_getStoreConfig(self::CONFIG_XML_PATH_MERCHANT_ID, $store));
    }

    /**
     * Get seller/merchant Name
     *
     * @param   store $store
     * @return  string
     */
    public function getMerchantName($store = null)
    {
        return trim($this->_getStoreConfig(self::CONFIG_XML_PATH_MERCHANT_NAME, $store));
    }

    /**
     * Get Checkout Page type
     *
     * @param   store $store
     * @return  string
     */
    public function getCheckoutPage($store = null)
    {
        return 'onepage';
    }

    /**
     * Get payment action
     *
     * @param store $store
     * @return string
     */
    public function getPaymentAction($store = null)
    {
        return $this->_getStoreConfig(self::CONFIG_XML_PATH_PAYMENT_ACTION, $store);
    }

    /**
     * Get new order status
     *
     * @param store $store
     * @return string
     */
    public function getNewOrderStatus($store = null)
    {
        return $this->_getStoreConfig(self::CONFIG_XML_PATH_ORDER_STATUS, $store);
    }

    /**
     * Show Pay button on Cart?
     *
     * @param   store $store
     * @return  string
     */
    public function showPayOnCart($store = null)
    {
        return ($this->_getStoreConfig(self::CONFIG_XML_PATH_SHOW_PAY_CART, $store));
    }

    /**
     * Is secure cart?
     *
     * @param   store $store
     * @return  bool
     */
    public function isSecureCart($store = null)
    {
        return ($this->_getStoreConfig(self::CONFIG_XML_PATH_SECURE_CART, $store));
    }

    /**
     * Get button type
     *
     * @param   store $store
     * @return  string
     */
    public function getButtonType($store = null)
    {
        return $this->_getStoreConfig(self::CONFIG_XML_PATH_BUTTON_TYPE, $store);
    }

    /**
     * Get button color
     *
     * @param   store $store
     * @return  string
     */
    public function getButtonColor($store = null)
    {
        return $this->_getStoreConfig(self::CONFIG_XML_PATH_BUTTON_COLOR, $store);
    }

    /**
     * Get button size
     *
     * @param   store $store
     * @return  string
     */
    public function getButtonSize($store = null)
    {
        return $this->_getStoreConfig(self::CONFIG_XML_PATH_BUTTON_SIZE, $store);
    }

    /**
     * Is button bade (acceptance mark) enabled?
     *
     * @param   store $store
     * @return  bool
     */
    public function isButtonBadgeEnabled($store = null)
    {
        return ($this->_getStoreConfig(self::CONFIG_XML_PATH_BUTTON_BADGE, $store));
    }

    /**
     * Is Checkout using OnePage?
     *
     * @param   store $store
     * @return  bool
     */
    public function isCheckoutOnepage($store = null)
    {
        return ($this->_getStoreConfig(self::CONFIG_XML_PATH_CHECKOUT_PAGE, $store) == 'onepage');
    }

    /**
     * Show coupon/discount code?
     *
     * @param   store $store
     * @return  bool
     */
    public function isShowCoupon($store = null)
    {
        return ($this->_getStoreConfig(self::CONFIG_XML_PATH_SHOW_COUPON, $store));
    }

    public function getIpWhitelist($store = null) {
        $this->_getStoreConfig('ip_whitelist', $store);
    }

    public function isRestrictedByIps($store = null) {
        $this->_getStoreConfig('restrict_by_ips', $store);
    }

    public function getProxyIp($store = null) {
        $this->_getStoreConfig('proxy_ip', $store);
    }

    public function isProxyMode($store = null) {
        $this->_getStoreConfig('proxy_mode', $store);
    }

}
