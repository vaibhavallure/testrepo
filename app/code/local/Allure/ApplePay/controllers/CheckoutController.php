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
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
            // The following two curl SSL options are set to "false" for ease of development/debug purposes only.
            // Any code used in production should either remove these lines or set them to the appropriate
            // values to properly use secure connections for PCI-DSS compliance.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);	//for production, set value to true or 1
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);	//for production, set value to 2
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
            curl_setopt($ch, CURLOPT_SSLCERT, dirname(__FILE__).'/../certs/Identity_VenusByMariaTash.pem');
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

        $cart   = $this->_getCart();

        $this->truncateCart();

        //$this->cleanQuotes();
        
        //$this->getOnepage()->initCheckout();
        
        //$cart->init()->save();

        $params = $this->getRequest()->getParams();

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
            
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            
            $quote = $cart->getQuote();
            
            $result = array(
                //'request' => $this->getRequest(),
                'params'        => $this->getRequest()->getParams(),
                'quote_id'      => $quote->getId(),
                'global_currency'  => $quote->getGlobalCurrencyCode(),
                'currency'      => Mage::app()->getStore()->getCurrentCurrencyCode(),
                'coupon_code'   => $quote->getCouponCode(),
                'subtotal'      => $quote->getBaseSubtotal(),
                'grand_total'   => $quote->getBaseGrandTotal(),
                'items'         => $quote->getItemsCount(),
                'total'         => Mage::helper('core')->currency($product->getFinalPrice(), false, false),
                'quote'         => $quote->getData()
            );
            
            /*
            $cart->init()->save();
            
            $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
            
            
            $result['subtotal']      = $this->getOnepage()->getQuote()->getBaseSubtotal();
            $result['grand_total']   = $this->getOnepage()->getQuote()->getBaseGrandTotal();
            
            $result['totals'] = array();
            
            foreach ($this->getOnepage()->getQuote()->getTotals() as $code => $total) {
                $result['totals'][$code] = array(
                        'title' => $total->getTitle(),
                        'value'=> $total->getValue()
                );
            }
            */

            $this->getResponse()->setBody(json_encode($result));
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
        
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('billing', array());
            
//             $quoteId = $this->getRequest()->getPost('quote_id', null);
//             $this->_getSession()->setQuoteId($quoteId);
//             $quote = Mage::getModel('sales/quote')->load($quoteId);
//             $this->_getCart()->setQuote($quote);
            
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
            }
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
                    $address = $this->getOnepage()->getQuote()->getShippingAddress();
                    
                    $shippingMethods = array();
                    
                    $hasDefaultMethod = false;
                    
                    if ($address->getShippingMethod() && $address->getShippingMethod() != '') {
                        $shippingMethods[$address->getShippingMethod()] = array();
                        $this->_getSession()->setDefaultShippingMethod($address->getShippingMethod());
                        $hasDefaultMethod = true;
                    }
                    
                    
                    foreach($address->getGroupedAllShippingRates() as $rates){
                        foreach ($rates as $rate) {
                            if ($rate->getErrorMessage() || $rate->getErrorMessage() != '' || $rate->getCarrier() == 'counterpoint_storepickupshipping') {
                                continue;
                            }
                            
                            if (!$hasDefaultMethod) {
                                $this->_getSession()->setDefaultShippingMethod($rate->getCode());
                                $hasDefaultMethod = true;
                            }
                            
                            $shippingMethods[$rate->getCode()] = $rate->getData();
                        }
                    }
                    
                    $result['goto_section'] = 'shipping_method';
                    $result['shipping_methods'] = $shippingMethods;
                    $result['update_section'] = array(
                            'name' => 'shipping-method',
                            //'html' => $this->_getShippingMethodsHtml()
                    );

                    $result['allow_sections'] = array('shipping');
                    $result['duplicateBillingInfo'] = 'true';
                } else {
                    $result['goto_section'] = 'shipping';
                }
                
                $this->getOnepage()->getQuote()->collectTotals()->save();
                
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
            
            //$result['billing_address'] = $this->_getQuote()->getBillingAddress()->getFirstname();
            
            //$result['shipping_address'] = $this->_getQuote()->getShippingAddress()->getFirstname();
            
            $this->getOnepage()->saveDeliveryOptions(array('delivery' => array( 'method' => 'one_ship')));

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Shipping address save action
     */
    public function saveShippingAction() {

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
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Shipping method save action
     */
    public function saveShippingMethodAction() {
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
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Create order action
     */
    public function saveOrderAction ($paymentData = false)
    {   
        $result = array();
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        
        try {
            if ($paymentData) {
                $data = $paymentData;
            } else {
                $data = $this->getRequest()->getPost('payment', array());
            }
            
            if ($data) {
                $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT |
                    Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY |
                    Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY |
                    Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX |
                    Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
                
                if (strtolower($this->getOnepage()->getQuote()->getDeliveryMethod()) == strtolower($_checkoutHelper::ONE_SHIP)) {
                    $this->getOnepage()
                    ->getQuote()
                    ->getPayment()
                    ->importData($data);
                } else {
                    if (! $this->getOnepage()->getQuoteOrdered()->getIsCheckoutCart()) {
                        $this->getOnepage()
                        ->getQuoteOrdered()
                        ->getPayment()
                        ->importData($data);
                    }
                    
                    if (! $this->getOnepage()->getQuoteBackordered()->getIsCheckoutCart()) {
                        $this->getOnepage()
                        ->getQuoteBackordered()
                        ->getPayment()
                        ->importData($data);
                    }
                }
            }

            // die;
            // Mage::log($data,Zend_log::DEBUG,'abc',true);die;
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
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    public function saveTransactionAction() {
        
        $data = $_POST;
        
        Mage::log("BEGIN: saveTransactionAction",Zend_Log::DEBUG, 'applepay.log', true);
        
        Mage::log(json_encode($_POST),Zend_Log::DEBUG, 'applepay.log', true);

        if (!$this->getOnepage()->getQuote()->getShippingAddress()->getShippingMethod()) {
            $this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod($this->_getSession()->getDefaultShippingMethod());
        }
        
        $paymentData = array('method' => 'applepay');
        
        $this->getRequest()->setPost('payment', $paymentData);
        
        Mage::log("END: saveTransactionAction",Zend_Log::DEBUG, 'applepay.log', true);
        return $this->saveOrderAction($paymentData);
    }

    public function saveOrderTransactionAction() {

        $data = $_POST;
        
        Mage::log("BEGIN: saveTransactionAction",Zend_Log::DEBUG, 'applepay.log', true);
        
        Mage::log(json_encode($_POST),Zend_Log::DEBUG, 'applepay.log', true);
        
        
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
      </transactionRequest>
</createTransactionRequest>
XML;
        
        $transRequestXml = new SimpleXMLElement($transRequestXmlStr);
        
        $loginId = 'venus12';
        $transactionKey = '5s8UVJ42HUhj6u9k';
        
        $transRequestXml->merchantAuthentication->addChild('name',$loginId);
        $transRequestXml->merchantAuthentication->addChild('transactionKey',$transactionKey);
        
        $transRequestXml->transactionRequest->amount = $_POST['amount'];
        $transRequestXml->transactionRequest->payment->opaqueData->dataDescriptor=$_POST['dataDesc'];
        $transRequestXml->transactionRequest->payment->opaqueData->dataValue=$_POST['dataBinary'];
        
        if ($_POST['dataDesc'] === 'COMMON.VCO.ONLINE.PAYMENT') {
            $transRequestXml->transactionRequest->addChild('callId',$_POST['callId']);
        }
        
        
        if (isset($_POST['paIndicator'])){
            $transRequestXml->transactionRequest->addChild('cardholderAuthentication');
            $transRequestXml->transactionRequest->addChild('authenticationIndicator',$_POST['paIndicator']);
            $transRequestXml->transactionRequest->addChild('cardholderAuthenticationValue',$_POST['paValue']);
        }
        
        $url="https://api.authorize.net/xml/v1/request.api";
        
        print_r($transRequestXml->asXML());
        
        try{	//setting the curl parameters.
            $ch = curl_init();
            if (FALSE === $ch)
                throw new Exception('failed to initialize');
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $transRequestXml->asXML());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
            // The following two curl SSL options are set to "false" for ease of development/debug purposes only.
            // Any code used in production should either remove these lines or set them to the appropriate
            // values to properly use secure connections for PCI-DSS compliance.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	//for production, set value to true or 1
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);	//for production, set value to 2
            curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
            $content = curl_exec($ch);
            if (FALSE === $content)
                throw new Exception(curl_error($ch), curl_errno($ch));
            curl_close($ch);
            
            $xmlResult=simplexml_load_string($content);
            
            $jsonResult=json_encode($xmlResult);
            
            echo $jsonResult;
                    
        }catch(Exception $e) {
            trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
        }
        
        
        $paymentData = array('method' => 'applepay'); 
        
        $this->getRequest()->setPost('payment', $paymentData);
        
        Mage::log("END: saveTransactionAction",Zend_Log::DEBUG, 'applepay.log', true);
        return $this->saveOrderAction($paymentData);
    }
    
    public function testTransactionAction() {
        
        $data = $_POST;
        
        //$session = $this->_getSession();
        //$checkoutSession = $this->_getCheckoutSession();
        
        //$cart = $this->_getCart();
        
        //$masterQuote = $checkoutSession->getQuote();
        //$quote = $session->getQuote();
        
        
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
      </transactionRequest>
</createTransactionRequest>
XML;
        
        $transRequestXml = new SimpleXMLElement($transRequestXmlStr);
        
        $loginId = 'venus12';
        $transactionKey = '5s8UVJ42HUhj6u9k';
        
        $transRequestXml->merchantAuthentication->addChild('name',$loginId);
        $transRequestXml->merchantAuthentication->addChild('transactionKey',$transactionKey);
        
        $transRequestXml->transactionRequest->amount = 1;
        $transRequestXml->transactionRequest->payment->opaqueData->dataDescriptor='COMMON.APPLE.INAPP.PAYMENT';
        $transRequestXml->transactionRequest->payment->opaqueData->dataValue='eyJ2ZXJzaW9uIjoiRUNfdjEiLCJkYXRhIjoiY2NEU24vb0o5M0ZBRUQ1WU14Wm1ldXQvZlFDNjdBRTNvUE1LZWEyaFhTc0tUUnlXR3NIK2hEaDgvdjVVVzhJQ01VeWkxT2FsL2NPaDFxQ0FOaS9DZWdmM3FuWk0zL21sUTIrRjByWVh3OHZrOVIvcEZkaVF6UWZ4LzUxZk8zNTF3WU5qVkczODVJMWZDRndabVNYYmxFTzZSWnl3NDNGc250RTZDVm9DUFpkNEZObTlYRDVud1g0VENydS95UWdZSWx5WGJGNXlySFp6WTM5YnU4bXNrellCNzFqQ0lvblJ0blpZeXN5U2t3N3NYWUVQQlVweEQyNDdGemJaV3kwaWU0aGRDb1dicXJXWUxxOVVyRk5xNStGdTZoaUFjUEdQUmNOcDFFN0hHRExiZWNQL1MraHN3K013bGF3OWxLM0h2Ty9VMWhIeVByQkxOVENFVU56S0NFOFhIYVRsYzhRRldnNUxJVy94WGtzOEpYU0Nad1ViSE93N085aEc3UGk1cXo4RXNKMTlscExMbzJna1ZMWVNyMmxXM3lHMEZmUHZndy9NcUREeW1BPT0iLCJzaWduYXR1cmUiOiJNSUFHQ1NxR1NJYjNEUUVIQXFDQU1JQUNBUUV4RHpBTkJnbGdoa2dCWlFNRUFnRUZBRENBQmdrcWhraUc5dzBCQndFQUFLQ0FNSUlENGpDQ0E0aWdBd0lCQWdJSUpFUHlxQWFkOVhjd0NnWUlLb1pJemowRUF3SXdlakV1TUN3R0ExVUVBd3dsUVhCd2JHVWdRWEJ3YkdsallYUnBiMjRnU1c1MFpXZHlZWFJwYjI0Z1EwRWdMU0JITXpFbU1DUUdBMVVFQ3d3ZFFYQndiR1VnUTJWeWRHbG1hV05oZEdsdmJpQkJkWFJvYjNKcGRIa3hFekFSQmdOVkJBb01Da0Z3Y0d4bElFbHVZeTR4Q3pBSkJnTlZCQVlUQWxWVE1CNFhEVEUwTURreU5USXlNRFl4TVZvWERURTVNRGt5TkRJeU1EWXhNVm93WHpFbE1DTUdBMVVFQXd3Y1pXTmpMWE50Y0MxaWNtOXJaWEl0YzJsbmJsOVZRelF0VUZKUFJERVVNQklHQTFVRUN3d0xhVTlUSUZONWMzUmxiWE14RXpBUkJnTlZCQW9NQ2tGd2NHeGxJRWx1WXk0eEN6QUpCZ05WQkFZVEFsVlRNRmt3RXdZSEtvWkl6ajBDQVFZSUtvWkl6ajBEQVFjRFFnQUV3aFYzN2V2V3g3SWhqMmpkY0pDaElZM0hzTDF2TENnOWhHQ1YyVXIwcFVFYmcwSU8yQkh6UUg2RE14OGNWTVAzNnpJZzFyclYxTy8wa29tSlBud1BFNk9DQWhFd2dnSU5NRVVHQ0NzR0FRVUZCd0VCQkRrd056QTFCZ2dyQmdFRkJRY3dBWVlwYUhSMGNEb3ZMMjlqYzNBdVlYQndiR1V1WTI5dEwyOWpjM0F3TkMxaGNIQnNaV0ZwWTJFek1ERXdIUVlEVlIwT0JCWUVGSlJYMjIvVmRJR0dpWWwyTDM1WGhRZm5tMWdrTUF3R0ExVWRFd0VCL3dRQ01BQXdId1lEVlIwakJCZ3dGb0FVSS9KSnhFK1Q1TzhuNXNUMktHdy9vcnY5TGtzd2dnRWRCZ05WSFNBRWdnRVVNSUlCRURDQ0FRd0dDU3FHU0liM1kyUUZBVENCL2pDQnd3WUlLd1lCQlFVSEFnSXdnYllNZ2JOU1pXeHBZVzVqWlNCdmJpQjBhR2x6SUdObGNuUnBabWxqWVhSbElHSjVJR0Z1ZVNCd1lYSjBlU0JoYzNOMWJXVnpJR0ZqWTJWd2RHRnVZMlVnYjJZZ2RHaGxJSFJvWlc0Z1lYQndiR2xqWVdKc1pTQnpkR0Z1WkdGeVpDQjBaWEp0Y3lCaGJtUWdZMjl1WkdsMGFXOXVjeUJ2WmlCMWMyVXNJR05sY25ScFptbGpZWFJsSUhCdmJHbGplU0JoYm1RZ1kyVnlkR2xtYVdOaGRHbHZiaUJ3Y21GamRHbGpaU0J6ZEdGMFpXMWxiblJ6TGpBMkJnZ3JCZ0VGQlFjQ0FSWXFhSFIwY0RvdkwzZDNkeTVoY0hCc1pTNWpiMjB2WTJWeWRHbG1hV05oZEdWaGRYUm9iM0pwZEhrdk1EUUdBMVVkSHdRdE1Dc3dLYUFub0NXR0kyaDBkSEE2THk5amNtd3VZWEJ3YkdVdVkyOXRMMkZ3Y0d4bFlXbGpZVE11WTNKc01BNEdBMVVkRHdFQi93UUVBd0lIZ0RBUEJna3Foa2lHOTJOa0JoMEVBZ1VBTUFvR0NDcUdTTTQ5QkFNQ0EwZ0FNRVVDSUhLS253K1NveXE1bVhRcjFWNjJjMEJYS3BhSG9kWXU5VFdYRVBVV1BwYnBBaUVBa1RlY2ZXNitXNWwwcjBBRGZ6VENQcTJZdGJTMzl3MDFYSWF5cUJOeThiRXdnZ0x1TUlJQ2RhQURBZ0VDQWdoSmJTKy9PcGphbHpBS0JnZ3Foa2pPUFFRREFqQm5NUnN3R1FZRFZRUUREQkpCY0hCc1pTQlNiMjkwSUVOQklDMGdSek14SmpBa0JnTlZCQXNNSFVGd2NHeGxJRU5sY25ScFptbGpZWFJwYjI0Z1FYVjBhRzl5YVhSNU1STXdFUVlEVlFRS0RBcEJjSEJzWlNCSmJtTXVNUXN3Q1FZRFZRUUdFd0pWVXpBZUZ3MHhOREExTURZeU16UTJNekJhRncweU9UQTFNRFl5TXpRMk16QmFNSG94TGpBc0JnTlZCQU1NSlVGd2NHeGxJRUZ3Y0d4cFkyRjBhVzl1SUVsdWRHVm5jbUYwYVc5dUlFTkJJQzBnUnpNeEpqQWtCZ05WQkFzTUhVRndjR3hsSUVObGNuUnBabWxqWVhScGIyNGdRWFYwYUc5eWFYUjVNUk13RVFZRFZRUUtEQXBCY0hCc1pTQkpibU11TVFzd0NRWURWUVFHRXdKVlV6QlpNQk1HQnlxR1NNNDlBZ0VHQ0NxR1NNNDlBd0VIQTBJQUJQQVhFWVFaMTJTRjFScGVKWUVIZHVpQW91L2VlNjVONEkzOFM1UGhNMWJWWmxzMXJpTFFsM1lOSWs1N3VnajlkaGZPaU10MnUyWnd2c2pvS1lUL1ZFV2pnZmN3Z2ZRd1JnWUlLd1lCQlFVSEFRRUVPakE0TURZR0NDc0dBUVVGQnpBQmhpcG9kSFJ3T2k4dmIyTnpjQzVoY0hCc1pTNWpiMjB2YjJOemNEQTBMV0Z3Y0d4bGNtOXZkR05oWnpNd0hRWURWUjBPQkJZRUZDUHlTY1JQaytUdkorYkU5aWhzUDZLNy9TNUxNQThHQTFVZEV3RUIvd1FGTUFNQkFmOHdId1lEVlIwakJCZ3dGb0FVdTdEZW9WZ3ppSnFraXBuZXZyM3JyOXJMSktzd053WURWUjBmQkRBd0xqQXNvQ3FnS0lZbWFIUjBjRG92TDJOeWJDNWhjSEJzWlM1amIyMHZZWEJ3YkdWeWIyOTBZMkZuTXk1amNtd3dEZ1lEVlIwUEFRSC9CQVFEQWdFR01CQUdDaXFHU0liM1kyUUdBZzRFQWdVQU1Bb0dDQ3FHU000OUJBTUNBMmNBTUdRQ01EclBjb05SRnBteGh2czF3MWJLWXIvMEYrM1pEM1ZOb282KzhaeUJYa0szaWZpWTk1dFpuNWpWUVEyUG5lbkMvZ0l3TWkzVlJDR3dvd1YzYkYzek9EdVFaLzBYZkN3aGJaWlB4bkpwZ2hKdlZQaDZmUnVaeTVzSmlTRmhCcGtQQ1pJZEFBQXhnZ0dMTUlJQmh3SUJBVENCaGpCNk1TNHdMQVlEVlFRRERDVkJjSEJzWlNCQmNIQnNhV05oZEdsdmJpQkpiblJsWjNKaGRHbHZiaUJEUVNBdElFY3pNU1l3SkFZRFZRUUxEQjFCY0hCc1pTQkRaWEowYVdacFkyRjBhVzl1SUVGMWRHaHZjbWwwZVRFVE1CRUdBMVVFQ2d3S1FYQndiR1VnU1c1akxqRUxNQWtHQTFVRUJoTUNWVk1DQ0NSRDhxZ0duZlYzTUEwR0NXQ0dTQUZsQXdRQ0FRVUFvSUdWTUJnR0NTcUdTSWIzRFFFSkF6RUxCZ2txaGtpRzl3MEJCd0V3SEFZSktvWklodmNOQVFrRk1ROFhEVEU0TURFek1ERXpNVFF4TlZvd0tnWUpLb1pJaHZjTkFRazBNUjB3R3pBTkJnbGdoa2dCWlFNRUFnRUZBS0VLQmdncWhrak9QUVFEQWpBdkJna3Foa2lHOXcwQkNRUXhJZ1FnZ0Q5dEtjaHhLNVpqbjA0SkJaVE5sZFlEb3FvOEk5L28xclliVlZkRCtDMHdDZ1lJS29aSXpqMEVBd0lFUmpCRUFpQm5UN1lGVW9QQXVQZ0NzQUJaOVpxR01TRzN2MXJsYkpvZW1TVGtMV0d6REFJZ0MyUHZYYXpja2dDM1BTUjFoanlraldmck8yUzBSZzZsUWt4RHlScEE4a0lBQUFBQUFBQT0iLCJoZWFkZXIiOnsiZXBoZW1lcmFsUHVibGljS2V5IjoiTUZrd0V3WUhLb1pJemowQ0FRWUlLb1pJemowREFRY0RRZ0FFK0tQTXRJYUlQOU5weW04b2dQMVJuc3R0c1RuZm1iWkIxcVJCVVNsVjU0Wm82L0IwUUhMNUZsckJDaHh0VWlNMmY1b3NBQU5BZzRnTkl1UXB4d01Kd2c9PSIsInB1YmxpY0tleUhhc2giOiJpZTZHMTJEQmt2cVVxUTlCdHhPb045RlZCeEM4L2dvbG9seDZqNitTMkY0PSIsInRyYW5zYWN0aW9uSWQiOiIyNjg5M2FiNjZjYWFlMmQ3MmNiNGFjOTQwZDJjODg4NWM5Y2Q1ZmM2NDI1MjJhZmU0ZjI3ZGQ1OTE4ZmM3OGRmIn19';
        
        $url="https://api.authorize.net/xml/v1/request.api";
        
        try{	//setting the curl parameters.
            $ch = curl_init();
            if (FALSE === $ch)
                throw new Exception('failed to initialize');
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $transRequestXml->asXML());
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
                // The following two curl SSL options are set to "false" for ease of development/debug purposes only.
                // Any code used in production should either remove these lines or set them to the appropriate
                // values to properly use secure connections for PCI-DSS compliance.
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	//for production, set value to true or 1
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);	//for production, set value to 2
                curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
                $content = curl_exec($ch);
                if (FALSE === $content)
                    throw new Exception(curl_error($ch), curl_errno($ch));
                    curl_close($ch);
                    
                    $xmlResult=simplexml_load_string($content);
                    
                    $jsonResult=json_encode($xmlResult);
                    
                    echo $jsonResult;
                    
        } catch(Exception $e) {
            trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
        }
        
        die('DONE');
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
    
    public function refreshtotalsAction()
    {
        $params = $this->getRequest()->getParams();
        if(!empty($params)){
            $data = $params['shipping_method'];
            Mage::getSingleton('checkout/type_onepage')->saveShippingMethod($data);
        }
        $this->_getQuote()->collectTotals()->save();
        $this->loadLayout();
        $this->renderLayout();
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
        return Mage::getSingleton('checkout/type_onepage');
        //return Mage::getSingleton('allure_applepay/checkout_type_onepage');
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
