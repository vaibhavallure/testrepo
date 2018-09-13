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
	 * Initialize/return the API gateway class.
	 */
	public function gateway()
	{
	    if( is_null( $this->_gateway ) ) {
	        $this->_gateway = Mage::getModel( $this->_code . '/gateway' );
	        $this->_gateway->init(array(
	                'login'			=> $this->getConfigData('login'),
	                'password'		=> $this->getConfigData('trans_key'),
	                'secret_key'	=> $this->getConfigData('secret_key'),
	                'test_mode'		=> $this->getConfigData('test'),
	                'verify_ssl'	=> $this->getConfigData('verify_ssl'),
	        ));
	    }

	    return $this->_gateway;
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
	    return (Mage::helper('allure_applepay')->isEnabledOnFrontEnd() && ((Mage::helper('allure_applepay')->isCheckoutApplePaySession() && $this->getConfigData('checkout_page') == 'onepage') || $this->getConfigData('use_in_checkout')));
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


	/**
	 * Checkout with payment action set to 'save only'. Save payment info, but do not authorize or capture.
	 */
	public function order(Varien_Object $payment, $amount)
	{
	    $this->_log( sprintf( 'order(%s %s, %s)', get_class( $payment ), $payment->getId(), $amount ) );

	    $this->_loadOrCreateCard( $payment );
	    $this->_resyncStoredCard( $payment );

	    /**
	     * There is no transaction ID, no transaction info, and no transaction. So...yeah.
	     */
	    $paymentData = array(
	            'profile_id' => $this->getCard()->getProfileId(),
	            'payment_id' => $this->getCard()->getPaymentId(),
	    );

	    $payment->setTransactionAdditionalInfo( Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $paymentData );

	    if( $payment->getOrder()->getStatus() != $this->getConfigData('order_status') ) {
	        $payment->getOrder()->setStatus( $this->getConfigData('order_status') );
	    }

	    $payment->setAdditionalInformation( Mage::helper('tokenbase')->replaceArray( $payment->getAdditionalInformation(), $paymentData ) )
	    ->setIsTransactionClosed(0);

	    $this->getCard()->updateLastUse()->save();

	    $this->_log( json_encode( $paymentData ) );

	    return $this;
	}

	/**
	 * Authorize a transaction
	 */
	public function authorize( Varien_Object $payment, $amount )
	{
	    $this->_log( sprintf( 'authorize(%s %s, %s)', get_class( $payment ), $payment->getId(), $amount ) );

	    $this->_loadOrCreateCard( $payment );

	    if( $amount <= 0 ) {
	        return $this;
	    }

	    /**
	     * Check for existing authorization, and void it if so.
	     */
	    $transactionId = explode( ':', $payment->getOrder()->getExtOrderId() );
	    if( !empty( $transactionId[1] ) ) {
	        $parentTransactionId = $payment->getParentTransactionId();
	        $payment->setParentTransactionId( $transactionId[0] );

	        $this->void( $payment );

	        $payment->setParentTransactionId( $parentTransactionId );
	    }

	    /**
	     * Process transaction and results
	     */
	    $this->_resyncStoredCard( $payment );

	    if( $this->getAdvancedConfigData('send_line_items') ) {
	        $this->gateway()->setLineItems( $payment->getOrder()->getAllVisibleItems() );
	    }

	    $this->_setCentinelParams( $payment );

	    $this->_beforeAuthorize( $payment, $amount );
	    $response = $this->gateway()->authorize( $payment, $amount );
	    $this->_afterAuthorize( $payment, $amount, $response );

	    $payment->setTransactionAdditionalInfo( Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $response->getData() );

	    if( $response->getIsFraud() === true ) {
	        $payment->setIsTransactionPending(true)
	        ->setIsFraudDetected(true)
	        ->setTransactionAdditionalInfo( 'is_transaction_fraud', true );
	    }
	    elseif( $payment->getOrder()->getStatus() != $this->getConfigData('order_status') ) {
	        $payment->getOrder()->setStatus( $this->getConfigData('order_status') );
	    }

	    $payment->getOrder()->setExtOrderId( sprintf( '%s:%s', $response->getTransactionId(), $response->getAuthCode() ) );

	    $payment->setTransactionId( $this->_getValidTransactionId( $payment, $response->getTransactionId() ) )
	    ->setAdditionalInformation( Mage::helper('tokenbase')->replaceArray( $payment->getAdditionalInformation(), $response->getData() ) )
	    ->setIsTransactionClosed(0);

	    $this->getCard()->updateLastUse()->save();

	    $this->_log( json_encode( $response->getData() ) );

	    return $this;
	}

	/**
	 * Capture a transaction [authorize if necessary]
	 */
	public function capture( Varien_Object $payment, $amount )
	{
	    $this->_log( sprintf( 'capture(%s %s, %s)', get_class( $payment ), $payment->getId(), $amount ) );

	    $this->_loadOrCreateCard( $payment );

	    if( $amount <= 0 ) {
	        return $this;
	    }

		var_dump($payment->getData());die;

	    /**
	     * Check for existing auth code.
	     */
	    $transactionId = explode( ':', $payment->getOrder()->getExtOrderId() );
	    if( !empty( $transactionId[1] ) && strrpos( $payment->getParentTransactionId(), '-auth' ) === false ) {
	        $this->gateway()->setHaveAuthorized( true );
	        $this->gateway()->setAuthCode( $transactionId[1] );

	        if( $payment->getParentTransactionId() != '' ) {
	            $this->gateway()->setTransactionId( $payment->getParentTransactionId() );
	        }
	        else {
	            $this->gateway()->setTransactionId( $transactionId[0] );
	        }
	    }
	    else {
	        $this->gateway()->setHaveAuthorized( false );

	        $this->_setCentinelParams( $payment );
	    }

	    /**
	     * Grab transaction ID from the invoice in case partial invoicing.
	     */
	    if( $payment->hasInvoice() && $payment->getInvoice() instanceof Mage_Sales_Model_Order_Invoice ) {
	        $invoice	= $payment->getInvoice();
	    }
	    else {
	        $invoice	= Mage::registry('current_invoice');
	    }

	    if( !is_null( $invoice ) ) {
	        if( $invoice->getTransactionId() != '' ) {
	            $this->gateway()->setTransactionId( $invoice->getTransactionId() );
	        }

	        if( $this->getAdvancedConfigData('send_line_items') ) {
	            $this->gateway()->setLineItems( $invoice->getAllItems() );
	        }
	    }
	    elseif( $this->getAdvancedConfigData('send_line_items') ) {
	        $this->gateway()->setLineItems( $payment->getOrder()->getAllVisibleItems() );
	    }

	    /**
	     * Process transaction and results
	     */
	    $this->_resyncStoredCard( $payment );

	    $this->_beforeCapture( $payment, $amount );
	    $response = $this->gateway()->capture( $payment, $amount );
	    $this->_afterCapture( $payment, $amount, $response );

	    $payment->setTransactionAdditionalInfo( Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $response->getData() );

	    if( $response->getIsFraud() === true ) {
	        $payment->setIsTransactionPending(true)
	        ->setIsFraudDetected(true)
	        ->setTransactionAdditionalInfo( 'is_transaction_fraud', true );
	    }
	    elseif( $this->gateway()->getHaveAuthorized() === false ) {
	        if( $payment->getOrder()->getStatus() != $this->getConfigData('order_status') ) {
	            $payment->getOrder()->setStatus( $this->getConfigData('order_status') );
	        }

	        $payment->getOrder()->setExtOrderId( sprintf( '%s:%s', $response->getTransactionId(), $response->getAuthCode() ) );
	    }

	    $payment->setIsTransactionClosed(0);

	    // Set transaction id iff different from the last txn id -- use Magento's generated ID otherwise.
	    if( $payment->getParentTransactionId() != $response->getTransactionId() ) {
	        $payment->setTransactionId( $this->_getValidTransactionId( $payment, $response->getTransactionId() ) );
	    }

	    $payment->setShouldCloseParentTransaction(1);
	    $payment->setAdditionalInformation( Mage::helper('tokenbase')->replaceArray( $payment->getAdditionalInformation(), $response->getData() ) );

	    $this->getCard()->updateLastUse()->save();

	    $this->_log( json_encode( $response->getData() ) );

	    return $this;
	}

	/**
	 * Refund a transaction
	 */
	public function refund( Varien_Object $payment, $amount )
	{
	    $this->_log( sprintf( 'refund(%s %s, %s)', get_class( $payment ), $payment->getId(), $amount ) );

	    $this->_loadOrCreateCard( $payment );

	    if( $amount <= 0 ) {
	        return $this;
	    }

	    $creditmemo		= $payment->getCreditmemo();

	    /**
	     * Grab transaction ID from the order
	     */
	    if( $payment->getParentTransactionId() != '' ) {
	        $transactionId = substr( $payment->getParentTransactionId(), 0, strcspn( $payment->getParentTransactionId(), '-' ) );
	    }
	    else {
	        if( $creditmemo && $creditmemo->getInvoice()->getTransactionId() != '' ) {
	            $transactionId = $creditmemo->getInvoice()->getTransactionId();
	        }
	        else {
	            $transactionId = explode( ':', $payment->getOrder()->getExtOrderId() );
	            $transactionId = $transactionId[0];
	        }
	    }

	    $this->gateway()->setTransactionId( $transactionId );

	    /**
	     * Add line items.
	     */
	    if( $this->getAdvancedConfigData('send_line_items') ) {
	        if( $creditmemo ) {
	            $this->gateway()->setLineItems( $creditmemo->getAllItems() );
	        }
	        else {
	            $this->gateway()->setLineItems( $payment->getOrder()->getAllVisibleItems() );
	        }
	    }

	    /**
	     * Process transaction and results
	     */
	    $this->_beforeRefund( $payment, $amount );
	    $response = $this->gateway()->refund( $payment, $amount );
	    $this->_afterRefund( $payment, $amount, $response );

	    $payment->setIsTransactionClosed(1)
	    ->setAdditionalInformation( Mage::helper('tokenbase')->replaceArray( $payment->getAdditionalInformation(), $response->getData() ) )
	    ->setTransactionAdditionalInfo( Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $response->getData() );

	    if( $response->getTransactionId() != '' && $response->getTransactionId() != $transactionId ) {
	        $payment->setTransactionId( $this->_getValidTransactionId( $payment, $response->getTransactionId() ) );
	    }
	    else {
	        $payment->setTransactionId( $this->_getValidTransactionId( $payment, $transactionId . '-refund' ) );
	    }

	    if( $creditmemo && $creditmemo->getInvoice() && $creditmemo->getInvoice()->getBaseTotalRefunded() < $creditmemo->getInvoice()->getBaseGrandTotal() ) {
	        $payment->setShouldCloseParentTransaction(0);
	    }
	    else {
	        $payment->setShouldCloseParentTransaction(1);
	    }

	    $this->getCard()->updateLastUse()->save();

	    $this->_log( json_encode( $response->getData() ) );

	    return $this;
	}

	/**
	 * Void a payment
	 */
	public function void( Varien_Object $payment )
	{
	    $this->_log( sprintf( 'void(%s %s)', get_class( $payment ), $payment->getId() ) );

	    $this->_loadOrCreateCard( $payment );

	    /**
	     * Short-circuit if we don't have a real transaction ID. That means reauth not working or failed.
	     * Not doing this can result in voiding a valid (potentially already-captured) transaction. Bad.
	     */
	    if( strpos( $payment->getParentTransactionId(), '-auth' ) !== false ) {
	        $this->_log( sprintf( 'Skipping void; do not have a valid auth transaction ID. (%s)', $payment->getParentTransactionId() ) );

	        return $this;
	    }

	    /**
	     * Grab transaction ID from the order
	     */
	    $this->gateway()->setTransactionId( $payment->getParentTransactionId() );

	    /**
	     * Process transaction and results
	     */
	    $this->_beforeVoid( $payment );
	    $response = $this->gateway()->void( $payment );
	    $this->_afterVoid( $payment, $response );

	    $transactionId = $response->getTransactionId() != '' && $response->getTransactionId() != '0' ? $response->getTransactionId() : $payment->getTransactionId();

	    $payment->getOrder()->setExtOrderId( $transactionId );

	    $payment->setAdditionalInformation( Mage::helper('tokenbase')->replaceArray( $payment->getAdditionalInformation(), $response->getData() ) )
	    ->setShouldCloseParentTransaction(1)
	    ->setIsTransactionClosed(1);

	    $payment->setTransactionAdditionalInfo( Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $response->getData() );

	    $this->getCard()->updateLastUse()->save();

	    $this->_log( json_encode( $response->getData() ) );

	    return $this;
	}

	/**
	 * Cancel a payment
	 */
	public function cancel( Varien_Object $payment )
	{
	    $this->_log( sprintf( 'cancel(%s %s)', get_class( $payment ), $payment->getId() ) );

	    return $this->void( $payment );
	}

	/**
	 * Fetch transaction info -- fraud detection
	 */
	public function fetchTransactionInfoOld( Mage_Payment_Model_Info $payment, $transactionId )
	{
	    $this->_log( 'fetchTransactionInfo('.$transactionId.')' );

	    $this->_loadOrCreateCard( $payment );

	    /**
	     * Process transaction and results
	     */
	    $this->_beforeFraudUpdate( $payment, $transactionId );
	    $response = $this->gateway()->fraudUpdate( $payment, $transactionId );
	    $this->_afterFraudUpdate( $payment, $transactionId, $response );

	    if( $response->getIsApproved() ) {
	        $transaction = $payment->getTransaction($transactionId);
	        $transaction->setAdditionalInformation( 'is_transaction_fraud', false );

	        $payment->setIsTransactionApproved( true );
	    }
	    elseif( $response->getIsDenied() ) {
	        $payment->setIsTransactionDenied( true );
	    }

	    $this->_log( json_encode( $response->getData() ) );

	    $txn = $payment->getTransaction( $transactionId );
	    $existingData = $txn instanceof Mage_Sales_Model_Order_Payment_Transaction ? $txn->getAdditionalInformation( Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS ) : null;

	    return Mage::helper('tokenbase')->replaceArray( !is_null( $existingData ) ? $existingData : array(), $response->getData() );
	}
}
