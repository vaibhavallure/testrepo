<?php

class Allure_MultiCheckout_Model_Paypal_Express_Checkout extends Mage_Paypal_Model_Express_Checkout
{
	protected $_checkoutSessionOrdered;
	protected $_checkoutSessionBackordered;
	
	public function getCheckoutOrdered()
	{
		if($this->_checkoutSessionOrdered==null)
			$this->_checkoutSessionOrdered = Mage::getSingleton("allure_multicheckout/ordered_session");
			return $this->_checkoutSessionOrdered;
	}
	
	public function getCheckoutBackordered()
	{
		if($this->_checkoutSessionBackordered==null)
			$this->_checkoutSessionBackordered = Mage::getSingleton("allure_multicheckout/backordered_session");
			return $this->_checkoutSessionBackordered;
	}
    
	public function getQuoteOrdered(){
		return Mage::getSingleton('allure_multicheckout/ordered_session')->getQuote();
	}
	
	public function getQuoteBackordred(){
		return Mage::getSingleton('allure_multicheckout/backordered_session')->getQuote();
	}
	
	public function returnFromPaypal($token)
	{
		$_checkoutstepHelper = Mage::helper('allure_multicheckout');
		if($_checkoutstepHelper->isTwoShipment()){
			$this->_getApi();
			$this->_api->setToken($token)
			->callGetExpressCheckoutDetails();
			
			$quote = $this->getQuoteOrdered();  //set two shipment Instock Quote
			
			$this->_ignoreAddressValidation();
			
			// import shipping address
			$exportedShippingAddress = $this->_api->getExportedShippingAddress();
			if (!$quote->getIsVirtual()) {
				$shippingAddress = $quote->getShippingAddress();
				if ($shippingAddress) {
					if ($exportedShippingAddress) {
						$this->_setExportedAddressData($shippingAddress, $exportedShippingAddress);
						
						if ($quote->getPayment()->getAdditionalInformation(self::PAYMENT_INFO_BUTTON) == 1) {
							// PayPal doesn't provide detailed shipping info: prefix, middlename, lastname, suffix
							$shippingAddress->setPrefix(null);
							$shippingAddress->setMiddlename(null);
							$shippingAddress->setLastname(null);
							$shippingAddress->setSuffix(null);
						}
						
						$shippingAddress->setCollectShippingRates(true);
						$shippingAddress->setSameAsBilling(0);
					}
					
					// import shipping method
					$code = '';
					if ($this->_api->getShippingRateCode()) {
						if ($code = $this->_matchShippingMethodCode($shippingAddress, $this->_api->getShippingRateCode())) {
							// possible bug of double collecting rates :-/
							$shippingAddress->setShippingMethod($code)->setCollectShippingRates(true);
						}
					}
					$quote->getPayment()->setAdditionalInformation(
							self::PAYMENT_INFO_TRANSPORT_SHIPPING_METHOD,
							$code
							);
				}
			}
			
			// import billing address
			$portBillingFromShipping = $quote->getPayment()->getAdditionalInformation(self::PAYMENT_INFO_BUTTON) == 1
			&& $this->_config->requireBillingAddress != Mage_Paypal_Model_Config::REQUIRE_BILLING_ADDRESS_ALL
			&& !$quote->isVirtual();
			if ($portBillingFromShipping) {
				$billingAddress = clone $shippingAddress;
				$billingAddress->unsAddressId()
				->unsAddressType();
				$data = $billingAddress->getData();
				$data['save_in_address_book'] = 0;
				$quote->getBillingAddress()->addData($data);
				$quote->getShippingAddress()->setSameAsBilling(1);
			} else {
				$billingAddress = $quote->getBillingAddress();
			}
			$exportedBillingAddress = $this->_api->getExportedBillingAddress();
			$this->_setExportedAddressData($billingAddress, $exportedBillingAddress);
			$billingAddress->setCustomerNotes($exportedBillingAddress->getData('note'));
			$quote->setBillingAddress($billingAddress);
			
			// import payment info
			$payment = $quote->getPayment();
			$payment->setMethod($this->_methodType);
			Mage::getSingleton('paypal/info')->importToPayment($this->_api, $payment);
			$payment->setAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_PAYER_ID, $this->_api->getPayerId())
			->setAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_TOKEN, $token)
			;
			$quote->collectTotals()->save();
		}else{
			parent::returnFromPaypal($token);
		}
	}
	
	/**
	 * Reserve order ID for specified quote and start checkout on PayPal
	 *
	 * @param string $returnUrl
	 * @param string $cancelUrl
	 * @param bool|null $button
	 * @return mixed
	 */
	public function start($returnUrl, $cancelUrl, $button = null)
	{
		$_checkoutstepHelper = Mage::helper('allure_multicheckout');
		if($_checkoutstepHelper->isTwoShipment()){
			$this->getQuoteOrdered()->collectTotals();
			
			if (!$this->getQuoteOrdered()->getGrandTotal() && !$this->getQuoteOrdered()->hasNominalItems()) {
				Mage::throwException(Mage::helper('paypal')->__('PayPal does not support processing orders with zero amount. To complete your purchase, proceed to the standard checkout process.'));
			}
			
			$grandTotal = $this->getQuoteOrdered()->getBaseGrandTotal() + 
			 			$this->getQuoteBackordred()->getBaseGrandTotal();




			$this->getQuoteOrdered()->reserveOrderId()->save();
			// prepare API
			$this->_getApi();
			$solutionType = $this->_config->getMerchantCountry() == 'DE'
					? Mage_Paypal_Model_Config::EC_SOLUTION_TYPE_MARK : $this->_config->solutionType;
					$this->_api->setAmount($grandTotal)
					->setCurrencyCode($this->getQuoteOrdered()->getBaseCurrencyCode())
					->setInvNum($this->getQuoteOrdered()->getReservedOrderId())
					->setReturnUrl($returnUrl)
					->setCancelUrl($cancelUrl)
					->setSolutionType($solutionType)
					->setPaymentAction($this->_config->paymentAction);
					
					if ($this->_giropayUrls) {
						list($successUrl, $cancelUrl, $pendingUrl) = $this->_giropayUrls;
						$this->_api->addData(array(
								'giropay_cancel_url' => $cancelUrl,
								'giropay_success_url' => $successUrl,
								'giropay_bank_txn_pending_url' => $pendingUrl,
						));
					}
					
					if ($this->_isBml) {
						$this->_api->setFundingSource('BML');
					}
					
					$this->_setBillingAgreementRequest();
					
					if ($this->_config->requireBillingAddress == Mage_Paypal_Model_Config::REQUIRE_BILLING_ADDRESS_ALL) {
						$this->_api->setRequireBillingAddress(1);
					}
					
					// supress or export shipping address
					if ($this->getQuoteOrdered()->getIsVirtual()) {
						if ($this->_config->requireBillingAddress == Mage_Paypal_Model_Config::REQUIRE_BILLING_ADDRESS_VIRTUAL) {
							$this->_api->setRequireBillingAddress(1);
						}
						$this->_api->setSuppressShipping(true);
					} else {
						$address = $this->getQuoteOrdered()->getShippingAddress();
						$isOverriden = 0;
						if (true === $address->validate()) {
							$isOverriden = 1;
							$this->_api->setAddress($address);
						}
						$this->getQuoteOrdered()->getPayment()->setAdditionalInformation(
								self::PAYMENT_INFO_TRANSPORT_SHIPPING_OVERRIDEN, $isOverriden
								);
						$this->getQuoteOrdered()->getPayment()->save();
					}
					
					// add line items
					$paypalCart = Mage::getModel('paypal/cart', array($this->getQuoteOrdered()));
					$this->_api->setPaypalCart($paypalCart)
					->setIsLineItemsEnabled($this->_config->lineItemsEnabled)
					;
					
					// add shipping options if needed and line items are available
					if ($this->_config->lineItemsEnabled && $this->_config->transferShippingOptions && $paypalCart->getItems()) {
						if (!$this->getQuoteOrdered()->getIsVirtual() && !$this->getQuoteOrdered()->hasNominalItems()) {
							if ($options = $this->_prepareShippingOptions($address, true)) {
								Mage::log("Options-".json_encode($options),Zend_log::DEBUG,'abc',true);
								$this->_api->setShippingOptionsCallbackUrl(
										Mage::getUrl('*/*/shippingOptionsCallback', array('quote_id' => $this->getQuoteOrdered()->getId()))
										)->setShippingOptions($options);
							}
						}
					}
					
					// add recurring payment profiles information
					if ($profiles = $this->getQuoteOrdered()->prepareRecurringPaymentProfiles()) {
						foreach ($profiles as $profile) {
							$profile->setMethodCode(Mage_Paypal_Model_Config::METHOD_WPP_EXPRESS);
							if (!$profile->isValid()) {
								Mage::throwException($profile->getValidationErrors(true, true));
							}
						}
						$this->_api->addRecurringPaymentProfiles($profiles);
					}
					
					$this->_config->exportExpressCheckoutStyleSettings($this->_api);
					
					// call API and redirect with token
					$this->_api->callSetExpressCheckout();
					$token = $this->_api->getToken();
					$this->_redirectUrl = $button ? $this->_config->getExpressCheckoutStartUrl($token)
					: $this->_config->getPayPalBasicStartUrl($token);
					
					$this->getQuoteOrdered()->getPayment()->unsAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);
					
					// Set flag that we came from Express Checkout button
					if (!empty($button)) {
						$this->getQuoteOrdered()->getPayment()->setAdditionalInformation(self::PAYMENT_INFO_BUTTON, 1);
					} elseif ($this->getQuoteOrdered()->getPayment()->hasAdditionalInformation(self::PAYMENT_INFO_BUTTON)) {
						$this->getQuoteOrdered()->getPayment()->unsAdditionalInformation(self::PAYMENT_INFO_BUTTON);
					}
					
					$this->getQuoteOrdered()->getPayment()->save();
				}else{
					return parent::start($returnUrl, $cancelUrl, $button = null);
				}
				return $token;
	}
	
	
	
	/**
	 * Involve new customer to system
	 *
	 * @return Mage_Paypal_Model_Express_Checkout
	 */
	protected function _customInvolveNewCustomer()
	{
		$customer = $this->getQuoteOrdered()->getCustomer();
		if ($customer->isConfirmationRequired()) {
			$customer->sendNewAccountEmail('confirmation');
			$url = Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail());
			$this->getCustomerSession()->addSuccess(
					Mage::helper('customer')->__('Account confirmation is required. Please, check your e-mail for confirmation link. To resend confirmation email please <a href="%s">click here</a>.', $url)
					);
		} else {
			$customer->sendNewAccountEmail();
			$this->getCustomerSession()->loginById($customer->getId());
		}
		return $this;
	}
	
	protected function _prepareNewCustomerQuoteForCustom()
	{
		$quote      = $this->getQuoteOrdered();
		$billing    = $quote->getBillingAddress();
		$shipping   = $quote->isVirtual() ? null : $quote->getShippingAddress();
		
		$customerId = $this->_lookupCustomerId();
		if ($customerId && !$this->_customerEmailExists($quote->getCustomerEmail())) {
			$this->getCustomerSession()->loginById($customerId);
			return $this->_prepareCustomerQuote();
		}
		
		$customer = $quote->getCustomer();
		/** @var $customer Mage_Customer_Model_Customer */
		$customerBilling = $billing->exportCustomerAddress();
		$customer->addAddress($customerBilling);
		$billing->setCustomerAddress($customerBilling);
		$customerBilling->setIsDefaultBilling(true);
		if ($shipping && !$shipping->getSameAsBilling()) {
			$customerShipping = $shipping->exportCustomerAddress();
			$customer->addAddress($customerShipping);
			$shipping->setCustomerAddress($customerShipping);
			$customerShipping->setIsDefaultShipping(true);
		} elseif ($shipping) {
			$customerBilling->setIsDefaultShipping(true);
		}
		/**
		 * @todo integration with dynamica attributes customer_dob, customer_taxvat, customer_gender
		 */
		if ($quote->getCustomerDob() && !$billing->getCustomerDob()) {
			$billing->setCustomerDob($quote->getCustomerDob());
		}
		
		if ($quote->getCustomerTaxvat() && !$billing->getCustomerTaxvat()) {
			$billing->setCustomerTaxvat($quote->getCustomerTaxvat());
		}
		
		if ($quote->getCustomerGender() && !$billing->getCustomerGender()) {
			$billing->setCustomerGender($quote->getCustomerGender());
		}
		
		Mage::helper('core')->copyFieldset('checkout_onepage_billing', 'to_customer', $billing, $customer);
		$customer->setEmail($quote->getCustomerEmail());
		$customer->setPrefix($quote->getCustomerPrefix());
		$customer->setFirstname($quote->getCustomerFirstname());
		$customer->setMiddlename($quote->getCustomerMiddlename());
		$customer->setLastname($quote->getCustomerLastname());
		$customer->setSuffix($quote->getCustomerSuffix());
		$customer->setPassword($customer->decryptPassword($quote->getPasswordHash()));
		$customer->setPasswordHash($customer->hashPassword($customer->getPassword()));
		$customer->save();
		$quote->setCustomer($customer);
		
		return $this;
	}
	
	
	protected function _prepareCustomerQuoteCustom()
	{
		$quote      = $this->getQuoteBackordred();
		//$quote      = $this->getQuoteOrdered();
		$billing    = $quote->getBillingAddress();
		$shipping   = $quote->isVirtual() ? null : $quote->getShippingAddress();
		
		$customer = $this->getCustomerSession()->getCustomer();
		if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
			$customerBilling = $billing->exportCustomerAddress();
			$customer->addAddress($customerBilling);
			$billing->setCustomerAddress($customerBilling);
		}
		if ($shipping && !$shipping->getSameAsBilling() &&
				(!$shipping->getCustomerId() || $shipping->getSaveInAddressBook())) {
					$customerShipping = $shipping->exportCustomerAddress();
					$customer->addAddress($customerShipping);
					$shipping->setCustomerAddress($customerShipping);
				}
				
				if (isset($customerBilling) && !$customer->getDefaultBilling()) {
					$customerBilling->setIsDefaultBilling(true);
				}
				if ($shipping && isset($customerShipping) && !$customer->getDefaultShipping()) {
					$customerShipping->setIsDefaultShipping(true);
				} else if (isset($customerBilling) && !$customer->getDefaultShipping()) {
					$customerBilling->setIsDefaultShipping(true);
				}
				$quote->setCustomer($customer);
	}
	
   
	public function prepareOrderReview($token = null)
	{
		$_checkoutstepHelper = Mage::helper('allure_multicheckout');
		if($_checkoutstepHelper->isTwoShipment()){
			$quote = $this->getQuoteOrdered();
			$payment = $quote->getPayment();
			if (!$payment || !$payment->getAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_PAYER_ID)) {
				Mage::throwException(Mage::helper('paypal')->__('Payer is not identified.'));
			}
			$quote->setMayEditShippingAddress(
					1 != $quote->getPayment()->getAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_SHIPPING_OVERRIDEN)
					);
			$quote->setMayEditShippingMethod(
					'' == $quote->getPayment()->getAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_SHIPPING_METHOD)
					);
			$this->_ignoreAddressValidation();
			$quote->collectTotals()->save();
		}else{
			parent::prepareOrderReview($token);
		}
	}
	
	
	
	public function placeCustom($token, $shippingMethodCode = null)
	{
		$_checkoutstepHelper = Mage::helper('allure_multicheckout');
		if($_checkoutstepHelper->isTwoShipment()){
			$isNewCustomer = false;
			switch ($this->getCheckoutMethod()) {
				case Mage_Checkout_Model_Type_Onepage::METHOD_GUEST:
					$this->_prepareGuestQuote();
					break;
				case Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER:
					$this->_prepareNewCustomerQuoteForCustom();
					$isNewCustomer = true;
					break;
				default:
					$this->_prepareCustomerQuote();
					break;
			}
			
			//$this->_ignoreAddressValidation();
			$quote1 = $this->getQuoteOrdered();
			$quote1->collectTotals();
			
			if($this->getCheckoutMethod()==Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER)
				$quote['customer_id'] = true;
				
				$service = Mage::getModel('sales/service_quote', $quote1);
				
				if($this->getQuoteBackordred()->getIsReadyToShip()){
					$service->submitAll();
				}else{
					Mage::getSingleton('checkout/session')->setIsSingleCharge(true);
					Mage::getSingleton('checkout/session')->setBaseTotal($this->getQuoteBackordred()->getBaseGrandTotal());
					$service->submitOrdersPayment(0);
					Mage::getSingleton('checkout/session')->setBaseTotal(0);
					Mage::getSingleton('checkout/session')->setIsSingleCharge(false);
				}
				
				$quote1->save();
				
				if ($isNewCustomer) {
					try {
						$this->_customInvolveNewCustomer();
					} catch (Exception $e) {
						Mage::logException($e);
					}
				}
				
				$this->_recurringPaymentProfiles = $service->getRecurringPaymentProfiles();
				// TODO: send recurring profile emails
				
				$this->getCheckoutOrdered()->setLastQuoteId($quote1->getId())
				->setLastSuccessQuoteId($quote1->getId())
				->clearHelperData();
				
				/** @var $order Mage_Sales_Model_Order */
				$order = $service->getOrder();
				$firstOrder = $order;
				if (!$order) {
					return;
				}
				$this->_billingAgreement = $order->getPayment()->getBillingAgreement();
				
				// commence redirecting to finish payment, if paypal requires it
				if ($order->getPayment()->getAdditionalInformation(
						Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_REDIRECT
						)) {
							$this->_redirectUrl = $this->_config->getExpressCheckoutCompleteUrl($token);
						}
						
						switch ($order->getState()) {
							// even after placement paypal can disallow to authorize/capture, but will wait until bank transfers money
							case Mage_Sales_Model_Order::STATE_PENDING_PAYMENT:
								// TODO
								break;
								// regular placement, when everything is ok
							case Mage_Sales_Model_Order::STATE_PROCESSING:
							case Mage_Sales_Model_Order::STATE_COMPLETE:
							case Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW:
								$order->queueNewOrderEmail();
								break;
						}
						
						$redirectUrl = $this->_redirectUrl;
						// add order information to the session
						$this->getCheckoutOrdered()->setLastOrderId($order->getId())
						->setRedirectUrl($this->_redirectUrl)
						->setLastRealOrderId($order->getIncrementId());
						
						// as well a billing agreement can be created
						if ($this->_billingAgreement) {
							$this->getCheckoutOrdered()->setLastBillingAgreementId($this->_billingAgreement->getId());
						}
						
						if ($this->_recurringPaymentProfiles) {
							$ids = array();
							foreach ($this->_recurringPaymentProfiles as $profile) {
								$ids[] = $profile->getId();
							}
							$this->getCheckoutOrdered()->setLastRecurringProfileIds($ids);
							// TODO: send recurring profile emails
						}
						
						
						
						//$this->_order = $order;
						
						//backorder creation
						$quote2 = $this->getQuoteBackordred();
						$quote2->collectTotals();
						$service = Mage::getModel('sales/service_quote', $quote2);
						
						$isSingleCharge = false;
						if($quote2->getIsReadyToShip()){
							$isSingleCharge = false;
						}else{
							$isSingleCharge=true;  //will not charge back order payment
						}
						$service->submitCustomQuote($firstOrder->getId(),$isSingleCharge);
						
						$quote2->save();
						
						if ($isNewCustomer) {
							try {
								$this->_prepareCustomerQuoteCustom();
							} catch (Exception $e) {
								Mage::logException($e);
							}
						}
						
						$this->_recurringPaymentProfiles = $service->getRecurringPaymentProfiles();
						// TODO: send recurring profile emails
						
						
						$this->getCheckoutBackordered()->setSecondLastQuoteId($quote2->getId())
						->setSecondLastSuccessQuoteId($quote2->getId())
						->clearHelperData();
						
						/** @var $order Mage_Sales_Model_Order */
						$order = $service->getOrder();
						if (!$order) {
							return;
						}
						$this->_billingAgreement = $order->getPayment()->getBillingAgreement();
						
						// commence redirecting to finish payment, if paypal requires it
						if ($order->getPayment()->getAdditionalInformation(
								Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_REDIRECT
								)) {
									$this->_redirectUrl = $this->_config->getExpressCheckoutCompleteUrl($token);
								}
								
								switch ($order->getState()) {
									// even after placement paypal can disallow to authorize/capture, but will wait until bank transfers money
									case Mage_Sales_Model_Order::STATE_PENDING_PAYMENT:
										// TODO
										break;
										// regular placement, when everything is ok
									case Mage_Sales_Model_Order::STATE_PROCESSING:
									case Mage_Sales_Model_Order::STATE_COMPLETE:
									case Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW:
										$order->queueNewOrderEmail();
										break;
								}
								
								
								
								$this->getCheckoutBackordered()->setSecondLastOrderId($order->getId())
								->setRedirectUrl($redirectUrl)
								->setSecondLastRealOrderId($order->getIncrementId());
								
								// as well a billing agreement can be created
								if ($this->_billingAgreement) {
									$this->getCheckoutBackordered()->setSecondLastBillingAgreementId($this->_billingAgreement->getId());
								}
								
								if ($this->_recurringPaymentProfiles) {
									$ids = array();
									foreach ($this->_recurringPaymentProfiles as $profile) {
										$ids[] = $profile->getId();
									}
									$this->getCheckoutBackordered()->setSecondLastRecurringProfileIds($ids);
									// TODO: send recurring profile emails
								}
								
								
		}else{
			Mage::getSingleton('checkout/session')->setIsSingleCharge(false);
			Mage::getSingleton('checkout/session')->setBaseTotal(0);
			parent::place($token);
		}
	}
	
	

    /**
     * Make sure addresses will be saved without validation errors
     */
    private function _ignoreAddressValidation()
    {
        $this->_quote->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$this->_quote->getIsVirtual()) {
            $this->_quote->getShippingAddress()->setShouldIgnoreValidation(true);
            if (!$this->_config->requireBillingAddress && !$this->_quote->getBillingAddress()->getEmail()) {
                $this->_quote->getBillingAddress()->setSameAsBilling(1);
            }
        }
    }

}
