<?php

/**
 * Onepage controller for checkout
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once ('app/code/local/MT/Checkout/controllers/OnepageController.php');

class Allure_MultiCheckout_OnepageController extends MT_Checkout_OnepageController
{
    
    /**
     * Checkout page
     */
    public function indexAction() {
        if ($this->getLogStatus()){
            Mage::log('*****Multicheckout onepage controller****',Zend_log::DEBUG,'onepage.log',true);
        }

        if (!Mage::getModel('core/cookie')->get('frontend_timeout')) {
            Mage::getModel('core/cookie')->set('from_checkout_page', 1);
        }
        //print_r(Mage::getSingleton('allure_multicheckout/ordered_session')->getQuote()->getAllVisibleItems());die;
        //echo "<pre>";print_r(Mage::getSingleton('allure_multicheckout/ordered_session')->getQuote()->getOrderedItems());die;
        
        if (!Mage::helper('checkout')->canOnepageCheckout()) {
            Mage::getSingleton('checkout/session')->addError($this->__('The onepage checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }
        $quote = $this->getOnepage()->getQuote();
        if ($this->getLogStatus()){
            Mage::log('Index- QuoteId:'.$quote->getId().', CustomerEmail:'.$quote->getCustomerEmail(),Zend_log::DEBUG,'onepage.log',true);
        }
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
            Mage::getStoreConfig('sales/minimum_order/error_message') :
            Mage::helper('checkout')->__('Subtotal must exceed minimum order amount');
            
            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure' => true)));
        $this->getOnepage()->initCheckout();
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Checkout'));
        $this->renderLayout();
    }

    /**
     * save checkout billing address
     */
    public function saveBillingAction ()
    {
        if ($this->getLogStatus()){
            $quoteLog = $this->getOnepage()->getQuote();
            Mage::log('Billing- QuoteId:'.$quoteLog->getId().', CustomerEmail:'.$quoteLog->getCustomerEmail(),Zend_log::DEBUG,'onepage.log',true);
        }

        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            // $postData = $this->getRequest()->getPost('billing', array());
            // $data = $this->_filterPostData($postData);
            $data = $this->getRequest()->getPost('billing', array());
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
            
            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
            }
            $result = $this->getOnepage()->saveBilling($data, $customerAddressId);
            
            if (! isset($result['error'])) {
                /* check quote for virtual */
                if ($this->getOnepage()
                    ->getQuote()
                    ->isVirtual()) {
                    $result['goto_section'] = 'payment';
                    $result['update_section'] = array(
                            'name' => 'payment-method',
                            'html' => $this->_getPaymentMethodsHtml()
                    );
                } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
                    
                    if (Mage::helper('allure_multicheckout')->isQuoteContainOutOfStockProducts()) {
                        /* mt allure new code added here */
                        $result['goto_section'] = 'delivery_option';
                        $result['update_section'] = array(
                                'name' => 'delivery-option',
                                'html' => $this->_getDeliveryinstuctionsHtml()
                        );
                    } else {
                        $result['goto_section'] = 'shipping_method';
                        $result['update_section'] = array(
                                'name' => 'shipping-method',
                                'html' => $this->_getShippingMethodsHtml()
                        );
                    }
                    
                    $result['allow_sections'] = array(
                            'shipping'
                    );
                    $result['duplicateBillingInfo'] = 'true';
                } else {
                    $result['goto_section'] = 'shipping';
                }
            }
            
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Shipping address save action
     */
    public function saveShippingAction ()
    {
        if ($this->getLogStatus()){
            $quoteLog = $this->getOnepage()->getQuote();
            Mage::log('Shipping- QuoteId:'.$quoteLog->getId().', CustomerEmail:'.$quoteLog->getCustomerEmail(),Zend_log::DEBUG,'onepage.log',true);
        }

        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping', array());
            $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
            $result = $this->getOnepage()->saveShipping($data, $customerAddressId);
            
            if (! isset($result['error'])) {
                
                if (Mage::helper('allure_multicheckout')->isQuoteContainOutOfStockProducts()) {
                    $result['goto_section'] = 'delivery_option';
                    $result['update_section'] = array(
                            'name' => 'delivery-option',
                            'html' => $this->_getDeliveryinstuctionsHtml()
                    );
                } else {
                    $result['goto_section'] = 'shipping_method';
                    $result['update_section'] = array(
                            'name' => 'shipping-method',
                            'html' => $this->_getShippingMethodsHtml()
                    );
                }
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Get shipping method step html
     *
     * @return string
     */
    protected function _getShippingMethodsHtml ()
    {
        if ($this->getLogStatus()){
            $quoteLog = $this->getOnepage()->getQuote();
            Mage::log('ShippingMethodsHtml- QuoteId:'.$quoteLog->getId().', CustomerEmail:'.$quoteLog->getCustomerEmail(),Zend_log::DEBUG,'onepage.log',true);
        }

        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        if (strtolower($this->getOnepage()
            ->getQuote()
            ->getDeliveryMethod()) == strtolower($_checkoutHelper::TWO_SHIP))
            $update->load('checkout_onepage_allureshippingmethod');
        else
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
    protected function _getPaymentMethodsHtml ()
    {
        if ($this->getLogStatus()){
            $quoteLog = $this->getOnepage()->getQuote();
            Mage::log('PaymentMethodsHtml- QuoteId:'.$quoteLog->getId().', CustomerEmail:'.$quoteLog->getCustomerEmail(),Zend_log::DEBUG,'onepage.log',true);
        }

        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $role = Mage::getSingleton('customer/group')->load($roleId)->getData('customer_group_code');
        
        if ('wholesale' == strtolower($role))
            $update->load('checkout_onepage_allurepaymentmethod');
        else
            $update->load('checkout_onepage_paymentmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    protected function _getDeliveryinstuctionsHtml ()
    {
        if ($this->getLogStatus()){
            $quoteLog = $this->getOnepage()->getQuote();
            Mage::log('DeliveryinstuctionsHtml- QuoteId:'.$quoteLog->getId().', CustomerEmail:'.$quoteLog->getCustomerEmail(),Zend_log::DEBUG,'onepage.log',true);
        }

        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_deliveryoption');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    /**
     * Shipping method save action
     */
    public function saveShippingMethodActionOld ()
    {
        if ($this->getLogStatus()){
            $quoteLog = $this->getOnepage()->getQuote();
            Mage::log('ShippingMethodActionOld- QuoteId:'.$quoteLog->getId().', CustomerEmail:'.$quoteLog->getCustomerEmail(),Zend_log::DEBUG,'onepage.log',true);
        }

        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping_method', '');
            Mage::getSingleton('checkout/session')->setInStockOrderShippingMethod($data);
            // Mage::log($data,Zend_log::DEBUG,'abc',true);
            $result = $this->getOnepage()->saveShippingMethod($data);
            // $result will contain error data if shipping method is empty
            if (! $result) {
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method',
                        array(
                                'request' => $this->getRequest(),
                                'quote' => $this->getOnepage()->getQuote()
                        ));
                $this->getOnepage()
                    ->getQuote()
                    ->collectTotals();
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                
                $result['goto_section'] = 'delivery_option';
                
                $result['update_section'] = array(
                        'name' => 'delivery-option',
                        'html' => $this->_getDeliveryinstuctionsHtml()
                );
            }
            $this->getOnepage()
                ->getQuote()
                ->collectTotals()
                ->save();
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Shipping method save action
     */
    public function saveShippingMethodAction ()
    {
        if ($this->getLogStatus()){
            $quoteLog = $this->getOnepage()->getQuote();
            Mage::log('saveShippingMethod- QuoteId:'.$quoteLog->getId().', CustomerEmail:'.$quoteLog->getCustomerEmail(),Zend_log::DEBUG,'onepage.log',true);
        }

        if ($this->getRequest()->isPost()) {
            // Mage::log($this->getRequest()->getPost(),Zend_log::DEBUG,'abc',true);die;
            $data = $this->getRequest()->getPost('shipping_method', '');
            Mage::getSingleton('checkout/session')->setInStockOrderShippingMethod($data);
            $no_signature_delivery = $this->getRequest()->getPost('no_signature_delivery', '');
            $result = $this->getOnepage()->saveShippingMethod($this->getRequest()
                ->getPost());
            $this->getOnepage()
                ->getQuote()
                ->setData('no_signature_delivery', $no_signature_delivery)
                ->save();
            /*
             * $result will have erro data if shipping method is empty
             */
            if (! $result) {
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method',
                        array(
                                'request' => $this->getRequest(),
                                'quote' => $this->getOnepage()->getQuote()
                        ));
                $this->getOnepage()
                    ->getQuote()
                    ->collectTotals();
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                
                $_checkoutHelper = Mage::helper('allure_multicheckout');
                if (strtolower(
                        $this->getOnepage()
                            ->getQuote()
                            ->getDeliveryMethod()) == strtolower($_checkoutHelper::TWO_SHIP)) {
                    
                    $giftMessageId = $this->getOnepage()
                        ->getQuote()
                        ->getGiftMessageId();
                    $quoteItems = $this->getOnepage()
                        ->getQuote()
                        ->getAllVisibleItems();
                    if ($this->getOnepage()->getQuoteOrdered()) {
                        if (isset($giftMessageId) && ! empty($giftMessageId)) {
                            $this->getOnepage()
                                ->getQuoteOrdered()
                                ->setGiftMessageId($giftMessageId)
                                ->save();
                        }
                        $this->getOnepage()
                            ->getQuoteOrdered()
                            ->setData('no_signature_delivery', $no_signature_delivery)
                            ->save();
                    }
                    if ($this->getOnepage()->getQuoteBackordered()) {
                        if (isset($giftMessageId) && ! empty($giftMessageId)) {
                            $this->getOnepage()
                                ->getQuoteBackordered()
                                ->setGiftMessageId($giftMessageId)
                                ->save();
                        }
                        $this->getOnepage()
                            ->getQuoteBackordered()
                            ->setData('no_signature_delivery', $no_signature_delivery)
                            ->save();
                    }
                    
                    foreach ($quoteItems as $item) {
                        $sku = $item->getSku();
                        if ($this->getOnepage()->getQuoteOrdered()) {
                            foreach ($this->getOnepage()
                                ->getQuoteOrdered()
                                ->getAllVisibleItems() as $item1) {
                                $sku1 = $item1->getSku();
                                if ($sku == $sku1)
                                    $item1->setGiftMessageId($item->getGiftMessageId())
                                        ->save();
                            }
                        }
                        if ($this->getOnepage()->getQuoteBackordered()) {
                            foreach ($this->getOnepage()
                                ->getQuoteBackordered()
                                ->getAllVisibleItems() as $item2) {
                                $sku2 = $item2->getSku();
                                if ($sku == $sku2)
                                    $item2->setGiftMessageId($item->getGiftMessageId())
                                        ->save();
                            }
                        }
                    }
                }
                
                $result["goto_section"] = "payment";
                $result["update_section"] = array(
                        "name" => "payment-method",
                        "html" => $this->_getPaymentMethodsHtml()
                );
            }
            $this->getOnepage()
                ->getQuote()
                ->collectTotals()
                ->save();
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function saveDeliveryOptionAction ()
    {
        if ($this->getLogStatus()){
            $quoteLog = $this->getOnepage()->getQuote();
            Mage::log('DeliveryOption- QuoteId:'.$quoteLog->getId().', CustomerEmail:'.$quoteLog->getCustomerEmail(),Zend_log::DEBUG,'onepage.log',true);
        }

        if ($this->getRequest()->isPost()) {
            // $data = $this->getRequest()->getPost("delivery", "");
            // $shipinmethod = $this->getRequest()->getPost('shipping_method',
            // '');
            // if(isset($shipinmethod) && !empty($shipinmethod)){
            // $result1 =
            // $this->getOnepage()->saveShippingMethod($shipinmethod);
            // set shipping method for in stock order
            // $this->getCheckout()->setInStockOrderShippingMethod($shipinmethod);
            // }s
            $data = $this->getRequest()->getPost();
            // Mage::log($data,Zend_log::DEBUG,'abc',true);die;
            $result = $this->getOnepage()->saveDeliveryOptions($data);
            /*
             * $result will have error data if shipping method is empty
             */
            
            if (! $result) {
                Mage::dispatchEvent("checkout_controller_onepage_save_deliveryoption",
                        array(
                                "request" => $this->getRequest(),
                                "quote" => $this->getOnepage()->getQuote()
                        ));
                $this->getResponse()->setBody(Zend_Json::encode($result));
                
                /*
                 * $result["goto_section"] = "payment";
                 * $result["update_section"] = array(
                 * "name" => "payment-method",
                 * "html" => $this->_getPaymentMethodsHtml()
                 * );
                 */
                
                $result['goto_section'] = 'shipping_method';
                $result['update_section'] = array(
                        'name' => 'shipping-method',
                        'html' => $this->_getShippingMethodsHtml()
                );
            }
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }

    /**
     * Save payment ajax action
     *
     * Sets either redirect or a JSON response
     */
    public function savePaymentAction ()
    {
        if ($this->getLogStatus()){
            $quoteLog = $this->getOnepage()->getQuote();
            Mage::log('Payment- QuoteId:'.$quoteLog->getId().', CustomerEmail:'.$quoteLog->getCustomerEmail(),Zend_log::DEBUG,'onepage.log',true);
        }

        if ($this->_expireAjax()) {
            return;
        }
        try {
            if (! $this->getRequest()->isPost()) {
                $this->_ajaxRedirectResponse();
                return;
            }
            
            $data = $this->getRequest()->getPost('payment', array());
            // Mage::log($data,Zend_log::DEBUG,'abc',true);
            $result = $this->getOnepage()->savePayment($data);
            
            // get section and redirect data
            $redirectUrl = $this->getOnepage()
                ->getQuote()
                ->getPayment()
                ->getCheckoutRedirectUrl();
            if (empty($result['error']) && ! $redirectUrl) {
                // check delivery method set review
                $quoteObj = Mage::getSingleton('checkout/session')->getQuote();
                $_checkoutHelper = Mage::helper('allure_multicheckout');
                if (strtolower($quoteObj->getDeliveryMethod()) == strtolower($_checkoutHelper::TWO_SHIP)) {
                    $this->loadLayout('checkout_onepage_shipment_review');
                } else {
                    $this->loadLayout('checkout_onepage_review');
                }
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
        if ($this->getLogStatus()){
            $quoteLog = $this->getOnepage()->getQuote();
            Mage::log('saveOrder- QuoteId:'.$quoteLog->getId().', CustomerEmail:'.$quoteLog->getCustomerEmail(),Zend_log::DEBUG,'onepage.log',true);
        }

        if ($this->_expireAjax()) {
            return;
        }
        $result = array();
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        $checkoutalert = 0;
        $isuuemessage ='';
        try {
            $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
            if ($requiredAgreements) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                $diff = array_diff($requiredAgreements, $postedAgreements);
                if ($diff) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = $this->__(
                            'Please agree to all the terms and conditions before placing the order.');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
            }
            
            $data = $this->getRequest()->getPost('payment', array());
            // Mage::log($data,Zend_log::DEBUG,'abc',true);
            if ($data) {
                // Mage::log($data,Zend_log::DEBUG,'abc',true);die;
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
                    if (! $this->getOnepage()->getQuoteOrdered()->getIsCheckoutCart())
                        $this->getOnepage()
                            ->getQuoteOrdered()
                            ->getPayment()
                            ->importData($data);
                    // Mage::log($this->getOnepage()->getQuoteOrdered()->getPayment()->getCcNumber(),Zend_log::DEBUG,'abc',true);
                    if (! $this->getOnepage()->getQuoteBackordered()->getIsCheckoutCart())
                        $this->getOnepage()
                            ->getQuoteBackordered()
                            ->getPayment()
                            ->importData($data);
                    // Mage::log($this->getOnepage()->getQuoteBackordered()->getPayment()->getCcNumber(),Zend_log::DEBUG,'abc',true);
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
            $checkoutalert = 1;
            $isuuemessage = $e->getMessage();
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
            $checkoutalert = 1;
            $isuuemessage = $e->getMessage();
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
            $checkoutalert = 1;
            $isuuemessage = $e->getMessage();
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

        if ($checkoutalert) {
            Mage::log('call to save alert isuue',Zend_log::DEBUG,'allureAlerts.log',true);
            $alerthelper = Mage::helper('alertservices');
            $customer_email = $this->getOnepage()->getQuoteOrdered()->getCustomerEmail();
            $dataissue = array(
                    'customer_email' => $customer_email,
                    'created_at' => Mage::getModel('core/date')->gmtDate(),
                    'type' => 'checkout',
                    'error_message' => $isuuemessage
                );
            $alerthelper->saveAlertIssues($dataissue);
            
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

    /*
     * Added by mt-allure
     */
    public function successorderAction ()
    {
        if ($this->getLogStatus()){
            $quoteLog = $this->getOnepage()->getQuote();
            Mage::log('successorder- QuoteId:'.$quoteLog->getId().', CustomerEmail:'.$quoteLog->getCustomerEmail(),Zend_log::DEBUG,'onepage.log',true);
        }

        $session = $this->getOnepage()->getCheckout();
        
        $sessionOrdered = $this->getOnepage()->getCheckoutOrdered();
        $sessionBackordered = $this->getOnepage()->getCheckoutBackordered();
        if (! $sessionOrdered->getLastSuccessQuoteId() && ! $sessionBackordered->getSecondLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }
        
        $lastQuoteId = $sessionOrdered->getLastQuoteId();
        $lastOrderId = $sessionOrdered->getLastOrderId();
        $lastRecurringProfiles = $sessionOrdered->getLastRecurringProfileIds();
        
        $secondLastQuoteId = $sessionBackordered->getSecondLastQuoteId();
        $secondLastOrderId = $sessionBackordered->getSecondLastOrderId();
        $secondLastRecurringProfiles = $sessionBackordered->getSecondLastRecurringProfileIds();
        
        if ((! $lastQuoteId || (! $lastOrderId && empty($lastRecurringProfiles))) &&
                 (! $secondLastQuoteId || (! $secondLastOrderId && empty($secondLastRecurringProfiles())))) {
            $this->_redirect('checkout/cart');
            return;
        }
        $session->clear();
        $sessionOrdered->clear();
        $sessionBackordered->clear();
        
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        Mage::dispatchEvent('checkout_onepage_controller_success_action',
                array(
                        'order_ids' => array(
                                $lastOrderId
                        )
                ));
        $this->renderLayout();
    }

    private function getLogStatus(){
        return Mage::helper("allure_multicheckout")->getOnePagelogStatus();
    }
}
