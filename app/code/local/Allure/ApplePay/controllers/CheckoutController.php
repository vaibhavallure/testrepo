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
            
            $cart->save();
            
            $this->_getSession()->setCartWasUpdated(true);
            
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
                    'subtotal'      => $quote->getBaseSubtotal(),
                    'grand_total'   => $quote->getBaseGrandTotal(),
                    'total'         => Mage::helper('core')->currency($product->getFinalPrice(), false, false)
                    //'session'       => $quote->getShippingAddress()->getData()
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
                    
                    foreach($address->getGroupedAllShippingRates() as $rates){
                        
                        foreach ($rates as $rate) {
                            if ($rate->getErrorMessage() || $rate->getErrorMessage() != '' || $rate->getCarrier() == 'counterpoint_storepickupshipping') {
                                continue;
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
                            'value'=> $total->getValue()
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
                
                $result['totals'] = array();
                
                foreach ($this->getOnepage()->getQuote()->getTotals() as $code => $total) {
                    $result['totals'][$code] = array(
                            'title' => $total->getTitle(),
                            'value'=> $total->getValue()
                    );
                }

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
                        'value'=> $total->getValue()
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
    public function saveOrderAction ()
    {
        
        if ($this->_expireAjax()) {
            return;
        }
        
        $result = array();
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        
        try {
            
            $data = $this->getRequest()->getPost('payment', array());
            
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

        $session = $this->_getSession();
        $checkoutSession = $this->_getCheckoutSession();

        $cart = $this->_getCart();

        $masterQuote = $checkoutSession->getQuote();
        $quote = $session->getQuote();
        
        $paymentData = array('method' => 'applepay'); 
        
        $this->getRequest()->setPost('payment', $paymentData);
        
        $checkoutSession->setQuoteId($quote->getId());
        $checkoutSession->replaceQuote($quote);
        
        $this->saveOrderAction();
        
        $checkoutSession->setQuoteId($masterQuote->getId());
        $checkoutSession->replaceQuote($masterQuote);

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
