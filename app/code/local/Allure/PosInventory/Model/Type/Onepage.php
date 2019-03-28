<?php
class Allure_ApplePay_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
    /**
     * Class constructor
     * Set customer already exists message
     */
    public function __construct()
    {
        $this->_helper = Mage::helper('checkout');
        $this->_customerEmailExistsMessage = Mage::helper('checkout')->__('There is already a customer registered using this email address. Please login using this email address or enter a different email address to register your account.');
        $this->_checkoutSession = Mage::getSingleton('allure_applepay/session');
        $this->_customerSession = Mage::getSingleton('customer/session');
    }
    
}