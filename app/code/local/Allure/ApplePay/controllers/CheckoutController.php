<?php
/**
 * Apple Pay
 *
 * @category    Allure
 * @package     Allure_ApplePay
 * @copyright   Copyright (c) 2017 Allure Inc
 * @license     http://opensource.org/licenses/Apache-2.0  Apache License, Version 2.0
 */

class Allure_ApplePay_CheckoutController extends Mage_Core_Controller_Front_Action
{

    /**
      * default action
      *
      * @access public
      * @return void
      */
    public function indexAction()
    {
    }

    public function validateMerchantAction() {
        $validationUrl="https://apple-pay-gateway-cert.apple.com/paymentservices/startSession";

        $pemPwd = '';
        $displayName    = "Maria Tash";
        $domainName     = $_SERVER['HTTP_HOST'];
        $merchantId     = 'merchant.com.mariatash.authorizenet';

        $payload = array (
            "merchantIdentifier" => $merchantId,
            "domainName"    => $domainName,
            "displayName"   => $displayName
        );

        // JSON Payload
        //$validationPayload = '{"merchantIdentifier": "merchant.com.mariatash.authorizenet","domainName": "www.venusbymariatash.com","displayName":"Venus By Maria Tash"}';

        $validationPayload = json_encode($payload);

        try{
            //setting the curl parameters.
            $ch = curl_init();
            if (FALSE === $ch) {
                throw new Exception('failed to initialize');
            }
            curl_setopt($ch, CURLOPT_URL, $validationUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $validationPayload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3000);
            // The following two curl SSL options are set to "false" for ease of development/debug purposes only.
            // Any code used in production should either remove these lines or set them to the appropriate
            // values to properly use secure connections for PCI-DSS compliance.
            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);	//for production, set value to true or 1
            //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);	//for production, set value to 2
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
            curl_setopt($ch, CURLOPT_SSLCERT, dirname(__FILE__).'/../certs/Identity_MariaTash.pem');
            curl_setopt($ch, CURLOPT_SSLKEY, dirname(__FILE__).'/../certs/Identity_MariaTash.key');
            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $pemPwd);
            curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
            $content = curl_exec($ch);

            if (FALSE === $content) {
                print_r(curl_error($ch));
                throw new Exception(curl_error($ch), curl_errno($ch));
            }
            curl_close($ch);
            die($content);

        } catch (Exception $e) {
            trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
        }
    }


    public function addProductAction() {
        Mage::log("BEGIN: addProductAction",Zend_Log::DEBUG, 'applepay.log', true);

        Mage::log("DATA: ".json_encode($_REQUEST),Zend_Log::DEBUG, 'applepay.log', true);

        $cart   = $this->_getCart();

        $this->truncateCart();

        //$this->cleanQuotes();

        //$this->getOnepage()->initCheckout();

        //$cart->init()->save();

        $params = $this->getRequest()->getParams();

        $specialInstruction = (isset($params['gift-special-instruction']) && !empty($params['gift-special-instruction'])) ? trim($params['gift-special-instruction']) : false;

        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();

            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->getResponse()->setBody(false);
                exit;
            }

            $cart->addProduct($product, $params);

            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $this->_getSession()->setCartWasUpdated(true);

            $cart->save();

            if ($specialInstruction) {
                //$this->storeGiftMessage($specialInstruction);
            }

            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            //$couponCode = '4uWruyuc';
            //$this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')->collectTotals()->save();

            $quote = $cart->getQuote();

            $result = array(
                //'request' => $this->getRequest(),
                //'params'        => $this->getRequest()->getParams(),
                'quote_id'      => $quote->getId(),
                'global_currency'  => $quote->getGlobalCurrencyCode(),
                'currency'      => Mage::app()->getStore()->getCurrentCurrencyCode(),
                //'coupon_code'   => $quote->getCouponCode(),
                'subtotal'      => $quote->getBaseSubtotal(),
                'grand_total'   => $quote->getBaseGrandTotal(),
                'items'         => $quote->getItemsCount(),
                'total'         => $product->getFinalPrice(),
                //'quote'         => $quote->getData()
            );

            $this->getResponse()->setBody(json_encode($result));
            Mage::log("END: addProductAction",Zend_Log::DEBUG, 'applepay.log', true);
            return;
        } catch (Mage_Core_Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
            return;
        } catch (Exception $e) {
            $this->getResponse()->setBody('Cannot add the item to shopping cart.'.$e->getMessage());
            Mage::logException($e);
            return;
        }
        var_dump($this->_getSession()->getData());die;

        Mage::log(json_encode($data), Zend_Log::DEBUG, 'applepay.log', true);
    }

    /**
     * save checkout billing address
     */
    public function saveBillingAction() {

        Mage::log("BEGIN: saveBillingAction",Zend_Log::DEBUG, 'applepay.log', true);
        Mage::log("DATA: ".json_encode($_REQUEST),Zend_Log::DEBUG, 'applepay.log', true);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('billing', array());

            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

            $calculateRegion = true;

            $calculateTotals = true;

            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
            }

            if (isset($data['country_id'])) {
                $data['country_id'] = strtoupper(trim($data['country_id']));
            }

            if ($calculateRegion) {

                if (!isset($data['region_id']) || empty($data['region_id'])) {
                    $regionName = $data['region'];

                    $region  = Mage::getModel('directory/region')->loadByCode($regionName, $data['country_id']);

                    if (!$region->getId()) {
                        $region = Mage::getModel('directory/region')->loadByName($regionName, $data['country_id']);
                    }

                    if ($region->getId()) {
                        $data['region_id'] = $region->getId();
                    }
                }
            }

            Mage::log("NEW DATA: ".json_encode($data),Zend_Log::DEBUG, 'applepay.log', true);

            if (!$calculateTotals) {
                $this->_getQuote()->getShippingAddress()->setCollectShippingRates(false);
            }

			Mage::log("START: saveBillingAddress",Zend_Log::DEBUG, 'applepay.log', true);

            $result = $this->getOnepage()->saveBilling($data, $customerAddressId);

			Mage::log("END: saveBillingAddress",Zend_Log::DEBUG, 'applepay.log', true);

			//$couponCode = '4uWruyuc';
            //$this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')->collectTotals()->save();

            if (!isset($result['error'])) {
                /* check quote for virtual */
                if (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
                    $address = $this->getOnepage()->getQuote()->getShippingAddress();

                    $shippingMethods = array();

                    $hasDefaultMethod = false;

                    $calculateShippingMethods = true;

                    if ($calculateShippingMethods) {

						Mage::log("START: getGroupedAllShippingRates",Zend_Log::DEBUG, 'applepay.log', true);

						foreach($address->getGroupedAllShippingRates() as $rates){
                            foreach ($rates as $rate) {

					            Mage::log("RATE: ".$rate->getCarrier().'//'.$rate->getCode(),Zend_Log::DEBUG, 'applepay.log', true);

                                if ($rate->getErrorMessage() || $rate->getErrorMessage() != '' || in_array($rate->getCarrier(), array('counterpoint_storepickupshipping', 'tm_storepickupshipping', 'allure_pickinstore'))) {
                                    continue;
                                }

								$shippingMethods[$rate->getCode()] = array(
									//"rate_id"			=> $rate->getId(),
						            "address_id"		=> $rate->getAddressId(),
						            "code"				=> $rate->getCode(),
						            "carrier"			=> $rate->getCarrier(),
						            "carrier_title"		=> $rate->getCarrierTitle(),
						            "method"			=> $rate->getMethod(),
						            "method_title"		=> $rate->getMethodTitle(),
						            "method_description"=> $rate->getMethodDescription(),
						            "price"				=> round($rate->getPrice(), 2)
								);

                                if (!$hasDefaultMethod) {
                                    $this->_getSession()->setDefaultShippingMethod($rate->getCode());

									if (!$address->getShippingMethod() || $address->getShippingMethod() != '') {
	                                    $address->setShippingMethod($rate->getCode());
									}

                                    //$this->getOnepage()->getQuote()->collectTotals()->save();

                                    $hasDefaultMethod = true;
                                }
                            }
                        }

						Mage::log("END: getGroupedAllShippingRates",Zend_Log::DEBUG, 'applepay.log', true);
                    }


					Mage::log("DEFAULT CARRIER: ".$address->getShippingMethod(),Zend_Log::DEBUG, 'applepay.log', true);

                    $result['shipping_methods'] = $shippingMethods;
                }

				Mage::log("START: collectTotals",Zend_Log::DEBUG, 'applepay.log', true);
                $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);
                $this->getOnepage()->getQuote()->collectTotals()->save();
				Mage::log("END: collectTotals",Zend_Log::DEBUG, 'applepay.log', true);

                $result['global_currency']  = $this->getOnepage()->getQuote()->getGlobalCurrencyCode();
                $result['currency']      = Mage::app()->getStore()->getCurrentCurrencyCode();

                if ($calculateTotals) {

                    $result['totals'] = array();

					Mage::log("START: getTotals",Zend_Log::DEBUG, 'applepay.log', true);
                    foreach ($this->getOnepage()->getQuote()->getTotals() as $code => $total) {
                        $result['totals'][$code] = array(
                                'title' => $total->getTitle(),
                                'value'=> round($total->getValue() / $this->getOnepage()->getQuote()->getBaseToQuoteRate(), 2)//$total->getValue()
                        );
                    }
					Mage::log("END: getTotals",Zend_Log::DEBUG, 'applepay.log', true);
                }
            }

			Mage::log("START: _setDeliveryOption",Zend_Log::DEBUG, 'applepay.log', true);
            $this->_setDeliveryOption();
			Mage::log("END: _setDeliveryOption",Zend_Log::DEBUG, 'applepay.log', true);

            //$this->getOnepage()->saveDeliveryOptions(array('delivery' => array( 'method' => 'one_ship')));

            Mage::log("RESULT: ".json_encode($result),Zend_Log::DEBUG, 'applepay.log', true);
            Mage::log("END: saveBillingAction",Zend_Log::DEBUG, 'applepay.log', true);

            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    private function _setDeliveryOption()
    {
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        $this->_getQuote()->setDeliveryMethod('one_ship');
        $this->_getQuote()->setOrderType($_checkoutHelper::SINGLE_ORDER);
    }


    /**
     * save checkout billing address
     */
    public function saveBillingAddressAction() {

        Mage::log("BEGIN: saveBillingAddressAction",Zend_Log::DEBUG, 'applepay.log', true);
        Mage::log("DATA: ".json_encode($_REQUEST),Zend_Log::DEBUG, 'applepay.log', true);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('billing', array());

            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
            }

            if (!isset($data['region_id']) || empty($data['region_id'])) {
                $regionName = $data['region'];

                $region  = Mage::getModel('directory/region')->loadByCode($regionName, $data['country_id']);

                if (!$region->getId()) {
                    $region = Mage::getModel('directory/region')->loadByName($regionName, $data['country_id']);
                }

                if ($region->getId()) {
                    $data['region_id'] = $region->getId();
                }
            }

            Mage::log("NEW DATA: ".json_encode($data),Zend_Log::DEBUG, 'applepay.log', true);

            $result = $this->getOnepage()->saveBilling($data, $customerAddressId);

            if (!isset($result['error'])) {
                /* check quote for virtual */
                if ($this->getOnepage()->getQuote()->isVirtual()) {
                    $result['goto_section'] = 'payment';
                    $result['update_section'] = array(
                            'name' => 'payment-method',
                            //'html' => $this->_getPaymentMethodsHtml()
                    );
                } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {

                    $result['goto_section'] = 'shipping_method';
                    $result['update_section'] = array(
                            'name' => 'shipping-method',
                            'html' => $this->_getShippingMethodsHtml()
                    );

                    $result['allow_sections'] = array('shipping');
                    $result['duplicateBillingInfo'] = 'true';
                } else {
                    $result['goto_section'] = 'shipping';
                }

                $result['global_currency']  = $this->getOnepage()->getQuote()->getGlobalCurrencyCode();
                $result['currency']      = Mage::app()->getStore()->getCurrentCurrencyCode();

                $result['totals'] = array();

                foreach ($this->getOnepage()->getQuote()->getTotals() as $code => $total) {
                    $result['totals'][$code] = array(
                            'title' => $total->getTitle(),
                            'value'=> round($total->getValue() / $this->getOnepage()->getQuote()->getBaseToQuoteRate(), 2)//$total->getValue()
                    );
                }
            }


            Mage::log("RESULT: ".json_encode($result),Zend_Log::DEBUG, 'applepay.log', true);
            Mage::log("END: saveBillingAddressAction",Zend_Log::DEBUG, 'applepay.log', true);

			$this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }


    /**
     * save checkout billing address
     */
    public function loadShippingMethodsAction() {

        Mage::log("BEGIN: loadShippingMethodsAction",Zend_Log::DEBUG, 'applepay.log', true);

        $this->getOnepage()->saveDeliveryOptions(array('delivery' => array( 'method' => 'one_ship')));

        $result = array();

        $address = $this->getOnepage()->getQuote()->getShippingAddress();

        Mage::log("DATA: ".json_encode($address->getData()),Zend_Log::DEBUG, 'applepay.log', true);

        $shippingMethods = array();

        $hasDefaultMethod = false;

        if ($address->getShippingMethod() && $address->getShippingMethod() != '') {
            $shippingMethods[$address->getShippingMethod()] = array();
            $this->_getSession()->setDefaultShippingMethod($address->getShippingMethod());
            $hasDefaultMethod = true;
        }


        foreach($address->getGroupedAllShippingRates() as $rates) {
            foreach ($rates as $rate) {
                if ($rate->getErrorMessage() || $rate->getErrorMessage() != '' || $rate->getCarrier() == 'counterpoint_storepickupshipping') {
                    continue;
                }

                if (!$hasDefaultMethod) {
                    $this->_getSession()->setDefaultShippingMethod($rate->getCode());
                    $hasDefaultMethod = true;
                }

				if (!in_array($rate->getCode(), array('tm_storepickupshipping_tm_storepickupshipping', 'tm_storepickupshipping','allure_pickinstore_allure_pickinstore','allure_pickinstore'))) {
					$shippingMethods[$rate->getCode()] = $rate->getData();
				}
            }
        }

        if (count($shippingMethods)) {
            $defaultShippingMethodRow = each($shippingMethods);
            $defaultShippingMethod = $defaultShippingMethodRow['key'];

            Mage::log("defaultShippingMethod: ".($defaultShippingMethod),Zend_Log::DEBUG, 'applepay.log', true);
            $this->getOnepage()->saveShippingMethod($defaultShippingMethod);
            $this->_getSession()->setDefaultShippingMethod($defaultShippingMethod);
        }

        $result['goto_section'] = 'shipping_method';
        $result['shipping_methods'] = $shippingMethods;
        $result['update_section'] = array(
                'name' => 'shipping-method',
                //'html' => $this->_getShippingMethodsHtml()
        );

        $result['allow_sections'] = array('shipping');
        $result['duplicateBillingInfo'] = 'true';

        $result['global_currency']  = $this->getOnepage()->getQuote()->getGlobalCurrencyCode();
        $result['currency']      = Mage::app()->getStore()->getCurrentCurrencyCode();


        Mage::log("RESULT: ".json_encode($result),Zend_Log::DEBUG, 'applepay.log', true);
        Mage::log("END: saveBillingAction",Zend_Log::DEBUG, 'applepay.log', true);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Shipping address save action
     */
    public function saveShippingAction() {

        Mage::log("BEGIN: saveShippingAction",Zend_Log::DEBUG, 'applepay.log', true);

        Mage::log("DATA: ".json_encode($_REQUEST),Zend_Log::DEBUG, 'applepay.log', true);
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping', array());
            $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
            $result = $this->getOnepage()->saveShipping($data, $customerAddressId);

            $this->getOnepage()->getQuote()->collectTotals()->save();

            if (!isset($result['error'])) {
                $result['totals'] = $this->getOnepage()->getQuote()->getTotals();

                $result['goto_section'] = 'shipping_method';
                $result['update_section'] = array(
                        'name' => 'shipping-method',
                        'html' => $this->_getShippingMethodsHtml()
                );
            }
            Mage::log("END: saveShippingAction",Zend_Log::DEBUG, 'applepay.log', true);

        	$this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Shipping method save action
     */
    public function saveShippingMethodAction() {
        Mage::log("BEGIN: saveShippingMethodAction",Zend_Log::DEBUG, 'applepay.log', true);
        Mage::log("DATA: ".json_encode($_REQUEST),Zend_Log::DEBUG, 'applepay.log', true);
//         if ($this->_expireAjax()) {
//             return;
//         }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping_method', '');
            $result = $this->getOnepage()->saveShippingMethod($data);

            /*
             $result will have erro data if shipping method is empty
             */
            if (!$result) {
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array('request' => $this->getRequest(),
                        'quote' => $this->getOnepage()->getQuote()));

                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                $result['goto_section'] = 'payment';
                $result['update_section'] = array(
                        'name' => 'payment-method',
                        //'html' => $this->_getPaymentMethodsHtml()
                );
            }

            $this->getOnepage()->getQuote()->collectTotals()->save();

            $result['global_currency']  = $this->getOnepage()->getQuote()->getGlobalCurrencyCode();
            $result['currency']      = Mage::app()->getStore()->getCurrentCurrencyCode();

            $result['totals'] = array();

            foreach ($this->getOnepage()->getQuote()->getTotals() as $code => $total) {
                $result['totals'][$code] = array(
                        'title' => $total->getTitle(),
                        'value'=> round($total->getValue() / $this->getOnepage()->getQuote()->getBaseToQuoteRate(), 2)
                );
            }

            Mage::log("END: saveShippingMethodAction",Zend_Log::DEBUG, 'applepay.log', true);

			$this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Save payment ajax action
     *
     * Sets either redirect or a JSON response
     */
    public function savePaymentAction() {

        try {
            if (!$this->getRequest()->isPost()) {
                $this->_ajaxRedirectResponse();
                return;
            }

            // set payment to quote
            $result = array();
            $data = $this->getRequest()->getPost('payment', array());
            $result = $this->getOnepage()->savePayment($data);

            // get section and redirect data
            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if (empty($result['error']) && !$redirectUrl) {
                $this->loadLayout('checkout_onepage_review');
                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                        'name' => 'review',
                        'html' => $this->_getReviewHtml()
                );
            }
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__('Unable to set Payment Method.');
        }

		$this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

	public function saveOrder($paymentData = false) {

        $result = array();
        $_checkoutHelper = Mage::helper('allure_multicheckout');

        try {
            if ($paymentData) {
                $data = $paymentData;
            } else {
                $data = $this->getRequest()->getPost('payment', array());
            }

            Mage::log("PAYMENT_DATA::".json_encode($data),Zend_Log::DEBUG, 'applepay.log', true);

            if ($data) {
                $data['checks'] =   Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL |
                                    Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY ;

                $this->getOnepage()
                    ->getQuote()
                    ->getPayment()
                    ->importData($data);
            }

            $this->getOnepage()->saveCustomOrder($data);

            $redirectUrl = $this->getOnepage()
            ->getCheckout()
            ->getRedirectUrl();
            $result['success'] = true;
            $result['error'] = false;
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            if (! empty($message)) {
                $result['error_messages'] = $message;
            }
            $result['goto_section'] = 'payment';
            $result['update_section'] = array(
                    'name' => 'payment-method',
                    'html' => $this->_getPaymentMethodsHtml()
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('allure_exception')->notifyExceptionForPayment(
                    $this->getOnepage()
                    ->getQuote(), $e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()
                    ->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();

            $gotoSection = $this->getOnepage()
            ->getCheckout()
            ->getGotoSection();
            if ($gotoSection) {
                $result['goto_section'] = $gotoSection;
                $this->getOnepage()
                ->getCheckout()
                ->setGotoSection(null);
            }
            $updateSection = $this->getOnepage()
            ->getCheckout()
            ->getUpdateSection();
            if ($updateSection) {
                if (isset($this->_sectionUpdateFunctions[$updateSection])) {
                    $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
                    $result['update_section'] = array(
                            'name' => $updateSection,
                            'html' => $this->$updateSectionFunction()
                    );
                }
                $this->getOnepage()
                ->getCheckout()
                ->setUpdateSection(null);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('allure_exception')->notifyExceptionForPayment(
                    $this->getOnepage()
                    ->getQuote(), $e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()
                    ->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $this->__(
                    'There was an error processing your order. Please contact us or try again later.');
        }

        if (strtolower($this->getOnepage()
                ->getQuote()
                ->getDeliveryMethod()) == strtolower($_checkoutHelper::ONE_SHIP)) {
                    $this->getOnepage()
                    ->getQuote()
                    ->save();
                    if ($result['success']) {
                        if ($this->getOnepage()->getQuoteOrdered()) {
                            $this->getOnepage()
                            ->getQuoteOrdered()
                            ->setIsActive(false)
                            ->save();
                        }
                        if ($this->getOnepage()->getQuoteBackordered()) {
                            $this->getOnepage()
                            ->getQuoteBackordered()
                            ->setIsActive(false)
                            ->save();
                        }
                    }
        } else {
            $this->getOnepage()
            ->getQuoteOrdered()
            ->save();
            $this->getOnepage()
            ->getQuoteBackordered()
            ->save();
            if ($result['success']) {
                Mage::getSingleton('checkout/session')->getQuote()
                ->setIsActive(false)
                ->save();
                $this->getOnepage()
                ->getQuoteOrdered()
                ->setIsActive(false)
                ->save();
                $this->getOnepage()
                ->getQuoteBackordered()
                ->setIsActive(false)
                ->save();
            }
        }

        /**
         * when there is redirect to third party, we don't want to save order
         * yet.
         * we will save the order in return action.
         */
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }

		return $result;
	}

    /**
     * Create order action
     */
    public function saveOrderAction ($paymentData = false)
    {
		$result = $this->saveOrder();

		$this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function saveTransactionAction() {

        $data = $_POST;

        Mage::log("BEGIN: saveTransactionAction",Zend_Log::DEBUG, 'applepay.log', true);

        Mage::log("DATA: ".json_encode($_REQUEST),Zend_Log::DEBUG, 'applepay.log', true);

        if (!$this->getOnepage()->getQuote()->getShippingAddress()->getShippingMethod()) {
            $this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod($this->_getSession()->getDefaultShippingMethod());
        }

        $paymentData = array('method' => 'applepay');

        $this->getRequest()->setPost('payment', $paymentData);

        Mage::log("END: saveTransactionAction",Zend_Log::DEBUG, 'applepay.log', true);
        return $this->saveOrderAction($paymentData);
    }

    public function saveApplePayTransactionAction() {

        $data = $_POST;

		$result = array();
		$result['success'] = false;
		$result['error'] = true;

        Mage::log("BEGIN: saveOrderTransactionAction",Zend_Log::DEBUG, 'applepay.log', true);

        Mage::log(json_encode($_POST),Zend_Log::DEBUG, 'applepay.log', true);

        if (isset($data['billing'])) {
            $billingData = $data['billing'];
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

            if (isset($billingData['email'])) {
                $billingData['email'] = trim($billingData['email']);
            }

            if (!isset($billingData['region_id']) || empty($billingData['region_id'])) {
                $regionName = $billingData['region'];

                $region  = Mage::getModel('directory/region')->loadByCode($regionName, $billingData['country_id']);

                if (!$region->getId()) {
                    $region = Mage::getModel('directory/region')->loadByName($regionName, $billingData['country_id']);
                }

                if ($region->getId()) {
                    $billingData['region_id'] = $region->getId();
                }
            }

            Mage::log("NEW DATA: ".json_encode($billingData),Zend_Log::DEBUG, 'applepay.log', true);

            $this->getOnepage()->saveBilling($billingData, $customerAddressId);
            $this->getOnepage()->saveShipping($billingData, $customerAddressId);
        }

        if (!$this->getOnepage()->getQuote()->getShippingAddress()->getShippingMethod()) {
            $this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod($this->_getSession()->getDefaultShippingMethod());
        }

        $paymentData = array('method' => 'applepay');

        $this->getRequest()->setPost('payment', $paymentData);

        Mage::log("START: saveOrderAction",Zend_Log::DEBUG, 'applepay.log', true);

        try {

	        Mage::log("START: saveOrder",Zend_Log::DEBUG, 'applepay.log', true);
            $result = $this->saveOrder($paymentData);
			Mage::log("END: saveOrder",Zend_Log::DEBUG, 'applepay.log', true);

			Mage::log("START: _chargeCard",Zend_Log::DEBUG, 'applepay.log', true);

            if ($responseData = $this->_chargeCard()) {

				Mage::log("END: _chargeCard",Zend_Log::DEBUG, 'applepay.log', true);

                $responseDataObject = json_decode($responseData, true);

				Mage::log("responseDataObject: ".json_encode($responseDataObject),Zend_Log::DEBUG, 'applepay.log', true);

                if ($responseDataObject["messages"]["resultCode"] == "Ok") {

					$paymentData = $responseDataObject["transactionResponse"];

					Mage::log("paymentData: ".json_encode($paymentData),Zend_Log::DEBUG, 'applepay.log', true);

					$responseCode = (string) $paymentData["responseCode"];

					Mage::log("responseCode: $responseCode",Zend_Log::DEBUG, 'applepay.log', true);

					$responseCode = (int) $responseCode;

					if ($responseCode == 1) {

						$transactionId = $paymentData["transId"];

						$orderId = Mage::getSingleton('checkout/session')->getLastOrderId();

						$order = Mage::getModel('sales/order')->load($orderId);

						$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

						# The rest is only required when handling a partial invoice as in this example
						$amount = $invoice->getGrandTotal();
						$invoice->register()->pay();
						$invoice->getOrder()->setIsInProcess(true);

						$formatedPrice = $order->getBaseCurrency()->formatTxt($order->getBaseGrandTotal());

						$comment = 'Amount of ' . $formatedPrice . ' captured through Apple Pay.';

						$history = $invoice->getOrder()->addStatusHistoryComment(
						    $comment, false
						);

						$history->setIsCustomerNotified(true);

						Mage::log("START: createInvoice",Zend_Log::DEBUG, 'applepay.log', true);

						Mage::getModel('core/resource_transaction')
						    ->addObject($invoice)
						    ->addObject($invoice->getOrder())
						    ->save();

						Mage::log("END: createInvoice",Zend_Log::DEBUG, 'applepay.log', true);

						//$order->save();

						// Prepare payment object
						Mage::log("START: savePayment",Zend_Log::DEBUG, 'applepay.log', true);
						$payment = $order->getPayment();
						$payment->setMethod('applepay')
						->setTransactionId($transactionId)
						->setParentTransactionId(null)
						->save();
						Mage::log("END: savePayment",Zend_Log::DEBUG, 'applepay.log', true);

						Mage::log("START: addTransaction",Zend_Log::DEBUG, 'applepay.log', true);
						$transaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, null, false, $comment);
					    $transaction->setParentTxnId($transactionID);
					    $transaction->setIsClosed(true);
					    $transaction->setAdditionalInformation("PaymentResponse", serialize($paymentData));
					    $transaction->save();
						Mage::log("END: addTransaction",Zend_Log::DEBUG, 'applepay.log', true);

						// $order->save();
					} else {

						$orderId = Mage::getSingleton('checkout/session')->getLastOrderId();

						$order = Mage::getModel('sales/order')->load($orderId);

						$quote = Mage::getModel('sales/quote')->load($order->getQuoteId());

						$order->cancel();
			            $order->setStatus('canceled');
			            $order->save();

			            $quote->setIsActive(1)->save();

						$result['success'] = false;
						$result['error'] = true;
					}
                } else {

					$orderId = Mage::getSingleton('checkout/session')->getLastOrderId();

					$order = Mage::getModel('sales/order')->load($orderId);

					$quote = Mage::getModel('sales/quote')->load($order->getQuoteId());

					$order->cancel();
					$order->setStatus('canceled');
					$order->save();

					$quote->setIsActive(1)->save();

					$result['success'] = false;
					$result['error'] = true;
				}
            }

			//return $result;
        } catch (Exception $e) {
            Mage::log("EXCEPTION: ".$e->getMessage(),Zend_Log::DEBUG, 'applepay.log', true);
            throw new Exception($e->getMessage(), $e->getCode());
        }
        Mage::log("END: saveOrderAction",Zend_Log::DEBUG, 'applepay.log', true);
        Mage::log("END: saveOrderTransactionAction",Zend_Log::DEBUG, 'applepay.log', true);

		Mage::log(Mage::helper('core')->jsonEncode($result), Zend_Log::DEBUG, 'applepay.log', true);

		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    private function _chargeCard()
    {
        $transRequestXmlStr=<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<createTransactionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
	<merchantAuthentication></merchantAuthentication>
	<transactionRequest>
		<transactionType>authCaptureTransaction</transactionType>
		<amount>assignAMOUNT</amount>
		<currencyCode>USD</currencyCode>
		<payment>
			<opaqueData>
				<dataDescriptor>assignDD</dataDescriptor>
				<dataValue>assignDV</dataValue>
			</opaqueData>
		</payment>
		<order>
			<invoiceNumber>INV-12345</invoiceNumber>
			<description>Apple Pay Order</description>
		</order>

		<customer>
			<id>0</id>
		</customer>
		<billTo>
			<firstName>Maria</firstName>
			<lastName>Tash</lastName>
		</billTo>
		<shipTo>
			<firstName>Maria</firstName>
			<lastName>Tash</lastName>
			<company></company>
			<address>653 Broadway</address>
			<city>New York</city>
			<state>NY</state>
			<zip>10012</zip>
			<country>USA</country>
		</shipTo>
	</transactionRequest>
</createTransactionRequest>
XML;

		$lastOrderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();

		if (!$lastOrderId) return false;

		$orderId = Mage::getSingleton('checkout/session')->getLastOrderId();

		$order = Mage::getModel('sales/order')->load($orderId);

		$requestData = array(
			"createTransactionRequest" => array(
				"merchantAuthentication" =>  array(
					"name" =>  "API_LOGIN_ID",
					"transactionKey" =>  "API_TRANSACTION_KEY"
				),
				//"refId" =>  "123456",
				"transactionRequest" =>  array(
					"transactionType" =>  "authCaptureTransaction",
					"amount" =>  $_POST['amount'],
					"payment" =>  array(
						// "creditCard" =>  array(
						// 	"cardNumber" =>  "5424000000000015",
						// 	"expirationDate" =>  "2020-12",
						// 	"cardCode" =>  "999"
						// )
						"opaqueData" =>  array(
							"dataDescriptor" =>  $_POST['dataDesc'],
							"dataValue" =>  $_POST['dataBinary']
						)
					),
					// "lineItems" =>  array(
					// 	"lineItem" =>  array(
					// 		"itemId" =>  "1",
					// 		"name" =>  "vase",
					// 		"description" =>  "Cannes logo",
					// 		"quantity" =>  "18",
					// 		"unitPrice" =>  "45.00"
					// 	)
					// ),
					// "tax" =>  array(
					// 	"amount" =>  "4.26",
					// 	"name" =>  "level2 tax name",
					// 	"description" =>  "level2 tax"
					// ),
					// "duty" =>  array(
					// 	"amount" =>  "8.55",
					// 	"name" =>  "duty name",
					// 	"description" =>  "duty description"
					// ),
					// "shipping" =>  array(
					// 	"amount" =>  "4.26",
					// 	"name" =>  "level2 tax name",
					// 	"description" =>  "level2 tax"
					// ),
					// "poNumber" =>  "456654",
					"customer" =>  array(
						"id" =>  "99999456654"
					),
					"billTo" =>  array(
						"firstName" =>  "Ellen",
						"lastName" =>  "Johnson",
						"company" =>  "Souveniropolis",
						"address" =>  "14 Main Street",
						"city" =>  "Pecan Springs",
						"state" =>  "TX",
						"zip" =>  "44628",
						"country" =>  "USA"
					),
					"shipTo" =>  array(
						"firstName" =>  "China",
						"lastName" =>  "Bayles",
						"company" =>  "Thyme for Tea",
						"address" =>  "12 Main Street",
						"city" =>  "Pecan Springs",
						"state" =>  "TX",
						"zip" =>  "44628",
						"country" =>  "USA"
					)
				)
			)
		);

        $transRequestXml = new SimpleXMLElement($transRequestXmlStr);

        $loginId = 'venus12';
        $transactionKey = '5s8UVJ42HUhj6u9k';

        $transRequestXml->merchantAuthentication->addChild('name',$loginId);
        $transRequestXml->merchantAuthentication->addChild('transactionKey',$transactionKey);

        $transRequestXml->transactionRequest->amount = $_POST['amount'];
        $transRequestXml->transactionRequest->payment->opaqueData->dataDescriptor=$_POST['dataDesc'];
        $transRequestXml->transactionRequest->payment->opaqueData->dataValue=$_POST['dataBinary'];

		$transRequestXml->transactionRequest->order->invoiceNumber = $lastOrderId;

		$transRequestXml->transactionRequest->customer->id = (int) $order->getCustomerId();

		if ($billingAddress = $order->getBillingAddress()) {
			$transRequestXml->transactionRequest->billTo->firstName = $billingAddress->getFirstname();
			$transRequestXml->transactionRequest->billTo->lastName = $billingAddress->getLastname();
			//$transRequestXml->transactionRequest->billTo->company = $billingAddress->getCompany();
			//$transRequestXml->transactionRequest->billTo->address = $billingAddress->getStreetFull();
			//$transRequestXml->transactionRequest->billTo->city = $billingAddress->getCity();
			//$transRequestXml->transactionRequest->billTo->state = $billingAddress->getRegion();
			//$transRequestXml->transactionRequest->billTo->zip = $billingAddress->getPostcode();
			//$transRequestXml->transactionRequest->billTo->country = $billingAddress->getCountry();
		}

		if ($shippingAddress = $order->getShippingAddress()) {
			$transRequestXml->transactionRequest->shipTo->firstName = $shippingAddress->getFirstname();
			$transRequestXml->transactionRequest->shipTo->lastName = $shippingAddress->getLastname();
			$transRequestXml->transactionRequest->shipTo->company = $shippingAddress->getCompany();
			$transRequestXml->transactionRequest->shipTo->address = $shippingAddress->getStreetFull();
			$transRequestXml->transactionRequest->shipTo->city = $shippingAddress->getCity();
			$transRequestXml->transactionRequest->shipTo->state = $shippingAddress->getRegionCode();
			$transRequestXml->transactionRequest->shipTo->zip = $shippingAddress->getPostcode();
			$transRequestXml->transactionRequest->shipTo->country = $shippingAddress->getCountry();
		}

        if ($_POST['dataDesc'] === 'COMMON.VCO.ONLINE.PAYMENT') {
            $transRequestXml->transactionRequest->addChild('callId',$_POST['callId']);
        }

        if (isset($_POST['paIndicator'])){
            $transRequestXml->transactionRequest->addChild('cardholderAuthentication');
            $transRequestXml->transactionRequest->addChild('authenticationIndicator',$_POST['paIndicator']);
            $transRequestXml->transactionRequest->addChild('cardholderAuthenticationValue',$_POST['paValue']);
        }

        $url="https://api.authorize.net/xml/v1/request.api";

        Mage::log("REQUEST: ".$transRequestXml->asXML(),Zend_Log::DEBUG, 'applepay.log', true);

        //print_r($transRequestXml->asXML());

        try{	//setting the curl parameters.
            $ch = curl_init();
            if (FALSE === $ch) {
                throw new Exception('failed to initialize');
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $transRequestXml->asXML());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 500);
            // The following two curl SSL options are set to "false" for ease of development/debug purposes only.
            // Any code used in production should either remove these lines or set them to the appropriate
            // values to properly use secure connections for PCI-DSS compliance.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	//for production, set value to true or 1
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);	//for production, set value to 2
            curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
            $content = curl_exec($ch);

            if (FALSE === $content) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }

            curl_close($ch);

            $xmlResult=simplexml_load_string($content);

            $jsonResult=json_encode($xmlResult);

            Mage::log("RESPONSE: ".$jsonResult,Zend_Log::DEBUG, 'applepay.log', true);

            return $jsonResult;

        } catch (Exception $e) {
            Mage::log("ERROR: ".sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()),Zend_Log::DEBUG, 'applepay.log', true);
            trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
        }

        return false;
    }

    public function truncateCart()
    {
        try {
            $this->_getCart()->truncate()->save();
            $this->_getSession()->setCartWasUpdated(true);
        } catch (Mage_Core_Exception $exception) {
            $this->_getSession()->addError($exception->getMessage());
        } catch (Exception $exception) {
            $this->_getSession()->addException($exception, $this->__('Cannot update shopping cart.'));
        }
    }

    /**
     * Initialize coupon
     */
    public function applyCouponAction()
    {
        $response = array(
            'error' => true,
            'message' => ''
        );

        Mage::log("BEGIN: applyCouponAction",Zend_Log::DEBUG, 'applepay.log', true);
        Mage::log("DATA: ".json_encode($_REQUEST),Zend_Log::DEBUG, 'applepay.log', true);

        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getQuote()->getItemsCount()) {
            $response['message'] = 'Cart is empty';
            die(json_encode($response));
            return;
        }

        $couponCode = (string)$this->getRequest()->getParam('coupon_code');
        $couponCode = trim($couponCode);

        if ($this->getRequest()->getParam('action') == 'remove') {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            if (!$isAjax) $this->_goBack();
            else die(json_encode($response));
            return;
        }

        try {
            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
            ->collectTotals()
            ->save();

            if (strlen($couponCode)) {
                if ($couponCode == $this->_getQuote()->getCouponCode()) {
                    $response['error'] = false;
                    $response['message'] = $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode));
                    $response['disable'] = true;
                } else {
                    $response['error'] = true;
                    $response['message'] = $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
                }
            } else {
                $response['error'] = false;
                $response['message'] = $this->__('Coupon code was canceled.');
            }

        } catch (Mage_Core_Exception $e) {
            $response['error'] = true;
            $response['message'] = $e->getMessage();
        } catch (Exception $e) {
            $response['error'] = true;
            $response['message'] = $this->__('Cannot apply the coupon code.');
            Mage::logException($e);
        }

        Mage::log("END: applyCouponAction",Zend_Log::DEBUG, 'applepay.log', true);

        die(json_encode($response));
    }

    public function activateGiftCardAction()
    {
        $giftCardCode = trim((string)$this->getRequest()->getParam('giftcard_code'));
        $card = Mage::getModel('giftcards/giftcards')->load($giftCardCode, 'card_code');

        if ($card->getId() && ($card->getCardStatus() == 1)) {

            Mage::getSingleton('giftcards/session')->setActive('1');
            $this->_setSessionVars($card);
            $this->_getQuote()->collectTotals();
            $result['message'] =  $this->__('Gift Card used');
            $result['success'] = 1;
        }else {
            $result['success'] = 0;
            if($card->getId() && ($card->getCardStatus() == 2)) {
                $result['message'] = $this->__('Gift Card "%s" was used.', Mage::helper('core')->escapeHtml($giftCardCode));
            } else {
                $result['message'] = $this->__('Gift Card "%s" is not valid.', Mage::helper('core')->escapeHtml($giftCardCode));
            }
        }

        $this->loadLayout('myaccount_checkout_cart_layout');
        $html = $this->getLayout()->getBlock('checkout.cart_myaccount')->toHtml();
        $result['html']  = $html;

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function deactivateGiftCardAction()
    {
        $oSession = Mage::getSingleton('giftcards/session');
        $cardId = $this->getRequest()->getParam('id');
        $cardIds = $oSession->getGiftCardsIds();
        $sessionBalance = $oSession->getGiftCardBalance();
        $newSessionBalance = $sessionBalance - $cardIds[$cardId]['balance'];
        unset($cardIds[$cardId]);
        if(empty($cardIds))
        {
            Mage::getSingleton('giftcards/session')->clear();
        }
        $oSession->setGiftCardBalance($newSessionBalance);
        $oSession->setGiftCardsIds($cardIds);

        $this->_getQuote()->collectTotals()->save();

        $result['success'] = 1;
        $result['message'] = $this->__('Gift card Removed');

        $this->loadLayout('myaccount_checkout_cart_layout');
        $html = $this->getLayout()->getBlock('checkout.cart_myaccount')->toHtml();
        $result['html']  = $html;

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function refreshTotalsAction()
    {
        $params = $this->getRequest()->getParams();
        if(!empty($params)){
            $data = $params['shipping_method'];
            $this->getOnepage()->saveShippingMethod($data);
        }

        $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);

        $this->_getQuote()->collectTotals()->save();

        $result = array();

        $calculateTotals = true;

        if ($calculateTotals) {

            foreach ($this->getOnepage()->getQuote()->getTotals() as $code => $total) {
                $result[$code] = array(
                        'title' => $total->getTitle(),
                        'value'=> round($total->getValue() / $this->getOnepage()->getQuote()->getBaseToQuoteRate(), 2)//$total->getValue()
                );
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

	public function testAction()
	{

	    define("AUTHORIZENET_LOG_FILE", "authnet.log");

		$this->voidTransaction('40920712533');

		die('HI');
	}

    private function cleanQuotes()
    {
        $customerId = $this->_getCart()->getCustomerSession()->getCustomerId();
        $quoteId = $this->_getCart()->getQuote()->getId();

        $quote_collection = Mage::getModel('sales/quote')->getCollection()
        ->addFieldToFilter('customer_id', $customerId)
        ->addFieldToFilter('is_active', 1)
        ->addFieldToFilter('is_applepay', 1);

        foreach ($quote_collection as $quote) {
            if ($quote->getId() != $quoteId) {
                $quote->setIsActive(false)->save();
            }
        }

        $this->_getCart()->getQuote()->setIsActive(true);
    }

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
        //return Mage::getSingleton('allure_applepay/cart');
    }

    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage() {
        //return Mage::getSingleton('checkout/type_onepage');
        return Mage::getSingleton('allure_applepay/checkout_type_onepage');
    }

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
        //return Mage::getSingleton('allure_applepay/session');
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Initialize product instance from request data
     *
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _initProduct()
    {
        $productId = (int) $this->getRequest()->getParam('product');
        if ($productId) {
            $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     */
    protected function _expireAjax() {
        if (!$this->getOnepage()->getQuote()->hasItems()
                || $this->getOnepage()->getQuote()->getHasError()
                || $this->getOnepage()->getQuote()->getIsMultiShipping()) {
                $this->_ajaxRedirectResponse();
                return true;
            }
            $action = $this->getRequest()->getActionName();
            if ($this->_getSession()->getCartWasUpdated(true) && !in_array($action, array('index', 'progress'))) {
                $this->_ajaxRedirectResponse();
                return true;
            }

            return false;
    }

    protected function _ajaxRedirectResponse() {
        $this->getResponse()
        ->setHeader('HTTP/1.1', '403 Session Expired')
        ->setHeader('Login-Required', 'true')
        ->sendResponse();
        return $this;
    }

    /**
     * Get shipping method step html
     *
     * @return string
     */
    protected function _getShippingMethodsHtml() {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_shippingmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    /**
     * Get payment method step html
     *
     * @return string
     */
    protected function _getPaymentMethodsHtml() {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_paymentmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }
}
