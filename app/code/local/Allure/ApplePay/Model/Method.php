<?php
/**
 * Apple Pay
 *
 * @category    Allure
 * @package     Allure_ApplePay
 * @copyright   Copyright (c) 2017 Allure Inc
 * @license     http://opensource.org/licenses/Apache-2.0  Apache License, Version 2.0
 */

class Allure_ApplePay_Model_Method extends ParadoxLabs_TokenBase_Model_Method//Mage_Payment_Model_Method_Abstract
{
    protected $_code						= 'applepay';
    
	protected $_formBlockType			= 'applepay/form';
	protected $_infoBlockType			= 'applepay/info';
	
	protected $_canAuthorize            = true;  // Can authorize online?
	protected $_canCapture              = true;  // Can capture funds online?
	protected $_canCapturePartial       = false; // Can capture partial amounts online?
	protected $_canRefund               = true;  // Can refund online?
	protected $_canRefundInvoicePartial = true;
	protected $_canVoid                 = true;  // Can void transactions online?
	protected $_canUseInternal          = false; // Can use this payment method in administration panel?
	protected $_canUseCheckout          = true;  // Can show this payment method as an option on checkout payment page?
	protected $_canUseForMultishipping  = false; // Is this payment method suitable for multi-shipping checkout?
	protected $_isInitializeNeeded      = true;
	protected $_canFetchTransactionInfo = false;
	protected $_canReviewPayment        = false;
	
	/**
	 * Initialize scope
	 */
	public function __construct()
	{
	    return parent::__construct();
	}
	
	/**
	 * Update the CC info during the checkout process.
	 */
	public function assignData( $data )
	{
		if (!($data instanceof Varien_Object)) {
			$data = new Varien_Object($data);
		}
		
		parent::assignData( $data );
		
		return $this;
	}

	/**
	 * Validate the transaction inputs.
	 */
	public function validate()
	{
		return true;
	}
	
	/**
	 * Check whether payment method can be used
	 *
	 * TODO: payment method instance is not supposed to know about quote
	 *
	 * @param Mage_Sales_Model_Quote|null $quote
	 *
	 * @return bool
	 */
	public function isAvailable($quote = null)
	{
	    $checkResult = new StdClass;
	    $isActive = (bool)(int)$this->getConfigData('active', $quote ? $quote->getStoreId() : null);
	    
	    if ($isActive) {
	        $device = Mage::helper('allure_applepay/device');
	        
	        if (!$device->isMobile() || $device->isTablet() || !$device->isiOS() || !$device->isSafari()) {
	            $isActive = false;
	        }
	    }
	    
	    $checkResult->isAvailable = $isActive;
	    $checkResult->isDeniedInConfig = !$isActive; // for future use in observers
	    Mage::dispatchEvent('payment_method_is_active', array(
	            'result'          => $checkResult,
	            'method_instance' => $this,
	            'quote'           => $quote,
	    ));
	    
	    if ($checkResult->isAvailable && $quote) {
	        $checkResult->isAvailable = $this->isApplicableToQuote($quote, self::CHECK_RECURRING_PROFILES);
	    }
	    return $checkResult->isAvailable;
	}
	
	
	/**
	 * Allow payment method in checkout?
	 *
	 * @return bool
	 */
	public function canUseCheckout()
	{
	    return (Mage::getSingleton('allure_applepay/config')->isEnabled() && Mage::helper('allure_applepay')->isEnableProductPayments() && ((Mage::helper('allure_applepay')->isCheckoutApplePaySession() && $this->getConfigData('checkout_page') == 'onepage') || $this->getConfigData('use_in_checkout')));
	}
	
	/**
	 * Using internal pages for input payment data
	 * Can be used in admin
	 *
	 * @return bool
	 */
	public function canUseInternal()
	{
	    return false;
	}
	
	/**
	 * Retrieve payment method title
	 *
	 * @return string
	 */
	public function getTitle($original = false)
	{
	    if ($original) {
	        return $this->getConfigData('title');
	    } else {
	       return Mage::app()->getLayout()->createBlock('allure_applepay/button','ApplePayPaymentMethodsButton')->setTemplate('allure/applepay/button.phtml')->toHtml();
	    }
	}
}
