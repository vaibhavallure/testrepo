    <?php

/**
 * Onepage controller for checkout
 * Override
 */
require_once ('app/code/local/MT/Checkout/controllers/OnepageController.php');

class Allure_MultiCheckout_OnepageController extends MT_Checkout_OnepageController
{
    const WHOLESALE_GROUP_ID = 2;
    const GUEST_GROUP_ID = 0;
    const ONEPAGE_LOG_FILE = "onepage.log";
    
    protected $logStatus;
    protected $actionArray = array(
        "index"                 => "Checkout Init",
        "savebilling"           => "Checkout saveBillingAddress",
        "saveshipping"          => "Checkout saveShippingAddress",
        "saveshippingmethod"    => "Shipping Method",
        "savedeliveryoption"    => "Checkout saveDeliveryOption",
        "savepayment"           => "Checkout savePayment",
        "saveorder"             => "Save Order"
    );
    
    /**
     * Check log is enabled for file write.
     * @return boolean
     */
    private function getLogStatus()
    {
        if(!$this->logStatus)
            $this->logStatus  = Mage::helper("allure_multicheckout")->getOnePagelogStatus();
            return $this->logStatus;
    }
    
    /**
     * write onepage checkout step by step log.
     * @param string|array $info
     */
    private function writeOnepageLog($info = null)
    {
        if(!$info) return ;
        if($this->getLogStatus()){
            Mage::log($info, Zend_Log::DEBUG, self::ONEPAGE_LOG_FILE, true);
        }
    }
    
    /**
     * Get customer cart information.
     * @return string
     */
    private function getCustomerCartInfo()
    {
        $quote = $this->getOnepage()->getQuote();
        $firstName = $quote->getCustomerFirstname() ? $quote->getCustomerFirstname() : "";
        $lastName = $quote->getCustomerLastname() ? $quote->getCustomerLastname() : "";
        $customerInfo .= "Quote Id: ".$quote->getId();
        $customerInfo .= " Name: " . $firstName . " " . $lastName;
        $customerInfo .= " Email: ".$quote->getCustomerEmail();
        return $customerInfo;
    }
    
    
    /**
     * Check customer group id is not wholesale
     * then redirect to multishipping checkout.
     * @return Mage_Checkout_OnepageController
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $action = $this->getRequest()->getActionName();
        if($customerGroupId != self::WHOLESALE_GROUP_ID){
            
            $isAmazonPaymentForGeneralCustomer = false;
            if(Mage::helper('core')->isModuleEnabled("Amazon_Payments")){
                $_helper = Mage::helper('amazon_payments/data');
                if($_helper->getConfig()->isEnabled() && $_helper->isCheckoutAmazonSession() && $_helper->isEnableProductPayments()){
                    $isAmazonPaymentForGeneralCustomer = true;
                }
            }
            
            if(strtolower($action) == "success"){
               return $this;                
            }elseif($isAmazonPaymentForGeneralCustomer){
                return $this;
            }elseif($customerGroupId == self::GUEST_GROUP_ID){
                return $this;
            }elseif(!$this->getOnepage()->getQuote()->isVirtual()){
                $this->_redirect("*/multishipping");
            }
        }
        
        return $this;
    }
    
    /**
     * save checkout billing address
     */
    public function saveBillingAction ()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('billing', array());
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

            if ($this->getLogStatus()){
                $customerInfo = $this->getCustomerCartInfo();
                $this->writeOnepageLog("Checkout saveBilling:: {$customerInfo}");
            }
            
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

//                    if (Mage::helper('allure_multicheckout')->isQuoteContainOutOfStockProducts()) {
                      if (false){
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
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->getRequest()->isPost()) {
            /** gift item */
            $giftItems = $this->getRequest()->getParam("ship");
            $this->getOnepage()->saveGiftItem($giftItems);

            if ($this->getLogStatus()){
                $customerInfo = $this->getCustomerCartInfo();
                $this->writeOnepageLog("Checkout saveShipping:: {$customerInfo}");
            }
            
            $data = $this->getRequest()->getPost('shipping', array());
            $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
            $result = $this->getOnepage()->saveShipping($data, $customerAddressId);

            /** Save customer billing address. */
            $dataBilling = $this->getRequest()->getPost('billing', array());
            $customerBillingAddressId = $this->getRequest()->getPost('billing_address_id', false);
            $this->getOnepage()->saveBilling($dataBilling, $customerBillingAddressId);
            

            if (! isset($result['error'])) {
                if ($this->getOnepage()->getQuote()->isVirtual()) 
                {
                    $result['goto_section'] = 'payment';
                    $result['update_section'] = array(
                        'name' => 'payment-method',
                        'html' => $this->_getPaymentMethodsHtml()
                    );
//                }elseif (Mage::getSingleton('customer/session')->isLoggedIn() && Mage::helper('allure_multicheckout')->isQuoteContainOutOfStockProducts()) {
                }elseif (false) {
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
                $result["totals_html"] = $this->_getRefreshTotalsHtml();
                $result["sidebar_html"] = $this->getSidebarHtml();
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
    
    protected function getSidebarHtml(){
        $content = $this->getLayout()
        ->createBlock('checkout/cart_sidebar')
        ->addItemRender('simple', 'checkout/cart_item_renderer', 'checkout/cart/sidebar/default.phtml')
        ->addItemRender('grouped', 'checkout/cart_item_renderer_grouped', 'checkout/cart/sidebar/default.phtml')
        ->addItemRender('configurable', 'checkout/cart_item_renderer_configurable', 'checkout/cart/sidebar/default.phtml')
        ->setTemplate('checkout/cart/sidebar.phtml')
        ->toHtml();
        return $content;
    }

    /**
     * Get shipping method step html
     *
     * @return string
     */
    protected function _getShippingMethodsHtml ()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();

        $_checkoutHelper = Mage::helper('allure_multicheckout');
        $deliveryMethod = $this->getOnepage()->getQuote()
            ->getDeliveryMethod();
        
       if (strtolower($deliveryMethod) == strtolower($_checkoutHelper::TWO_SHIP))
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
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();

        if ($groupId == self::WHOLESALE_GROUP_ID)
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
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_deliveryoption');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    public function saveShippingMethodAction ()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping_method', '');

	        if ($this->getLogStatus()){
	            $customerInfo = $this->getCustomerCartInfo();
	            $dataRequest = json_encode($this->getRequest()->getPost());
				$this->writeOnepageLog("Checkout saveShippingMethod::{$customerInfo} Shipping Method : {$dataRequest}");
	        }

            Mage::getSingleton('checkout/session')->setInStockOrderShippingMethod($data);
            $no_signature_delivery = $this->getRequest()->getPost('no_signature_delivery', '');
            $no_signature_delivery = ($no_signature_delivery) ? 1 : 0;
            
            $result = $this->getOnepage()
                ->saveShippingMethod($this->getRequest()->getPost());
            $this->getOnepage()->getQuote()
                ->setData('no_signature_delivery', $no_signature_delivery)
                ->save();
            /*
             * $result will have error data if shipping method is empty
             */
            if (! $result) {
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method',
                    array(
                        'request' => $this->getRequest(),
                        'quote' => $this->getOnepage()->getQuote()
                    ));
                $this->getOnepage()->getQuote()->collectTotals();
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                $_checkoutHelper = Mage::helper('allure_multicheckout');
                $deliveryMethod = $this->getOnepage()->getQuote()->getDeliveryMethod();
                $errorWhileDividingQuote = 0;
                if (strtolower($deliveryMethod) == strtolower($_checkoutHelper::TWO_SHIP)) {
                    /*this condition check if normal order and backorder contain same quote id*/
                    if ($this->getOnepage()->getQuoteOrdered()->getId() != $this->getOnepage()->getQuoteBackordered()->getId())
                    {
                        $giftMessageId = $this->getOnepage()
                            ->getQuote()
                            ->getGiftMessageId();
                        $quoteItems = $this->getOnepage()
                            ->getQuote()
                            ->getAllVisibleItems();
                        if ($this->getOnepage()->getQuoteOrdered()) {
                            if (isset($giftMessageId) && !empty($giftMessageId)) {
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
                            if (isset($giftMessageId) && !empty($giftMessageId)) {
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
                    else{
                        Mage::log("Same Quote Id for Backorder And Normal Order= ".$this->getOnepage()->getQuoteBackordered()->getId(),Zend_log::DEBUG,'quotedividingissue.log',true);

                        $errorWhileDividingQuote=1;
                    }
                }

                if ($errorWhileDividingQuote) {
                    $result['goto_section'] = 'delivery_option';

                    $result['update_section'] = array(
                        'name' => 'delivery-option',
                        'html' => $this->_getDeliveryinstuctionsHtml()
                    );

                } else {
                    $result["goto_section"] = "payment";
                    $result["update_section"] = array(
                        "name" => "payment-method",
                        "html" => $this->_getPaymentMethodsHtml(),
                        "totals_html" => $this->_getRefreshTotalsHtml()
                    );
                }
            }
            $this->getOnepage()
                ->getQuote()
                ->collectTotals()
                ->save();


            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
    
    /**
     * Get refresh totals html
     * @return string
     */
    protected function _getRefreshTotalsHtml ()
    {
        $block = $this->getLayout()
            ->createBlock('checkout/cart_totals')
            ->setTemplate('checkout/cart/totals.phtml');
        $childBlock = $this->getLayout()
            ->createBlock('checkout/cart_shipping')
            ->setTemplate('checkout/cart/shipping.phtml');
        $block->setChild("shipping", $childBlock);
        $output = $block->toHtml();
        return $output;
    }

    public function saveDeliveryOptionAction ()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->getOnepage()->saveDeliveryOptions($data);
            
            if (! $result) {
                Mage::dispatchEvent("checkout_controller_onepage_save_deliveryoption",array("request" => $this->getRequest(),"quote" => $this->getOnepage()->getQuote()));
                $this->getResponse()->setBody(Zend_Json::encode($result));
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
        if ($this->_expireAjax()) {
            return;
        }
        try {
            if (! $this->getRequest()->isPost()) {
                $this->_ajaxRedirectResponse();
                return;
            }
            
            if ($this->getLogStatus()){
                $customerInfo = $this->getCustomerCartInfo();
                $this->writeOnepageLog("Checkout savePayment::{$customerInfo}");
            }
            

            $data = $this->getRequest()->getPost('payment', array());
            $result = $this->getOnepage()->savePayment($data);

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
                    $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
                    if($customerGroupId == 2){
                        $this->loadLayout('checkout_onepage_review');
                    }else{
                        $this->loadLayout('checkout_onepage_review_general_customer');
                    }
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
			$customerInfo = $this->getCustomerCartInfo();
			$this->writeOnepageLog("Checkout saveOrder::{$customerInfo}");
        }

        if ($this->_expireAjax()) {
            return;
        }
        $result = array();
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        $checkoutalert = 0;
        $isuuemessage ='';
        try {
            
            /** save gift item data */
            Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method',
                array(
                    'request' => $this->getRequest(),
                    'quote' => $this->getOnepage()->getQuote()
                ));
            
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
            $quote = $this->getOnepage()->getQuote();
            $this->writeOnepageLog("Mage_Payment_Model_Info_Exception :: Quote Id : {$quote->getId()} Email:{$quote->getCustomerEmail()}");
            $this->writeOnepageLog("Quote Id : {$quote->getId()} Error Code:{$e->getCode()} Error Message: {$e->getMessage()}");
            $this->writeOnepageLog("Quote Id : {$quote->getId()} " . $e->getTraceAsString());
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
            $quote = $this->getOnepage()->getQuote();
            $this->writeOnepageLog("Mage_Core_Exception :: Quote Id : {$quote->getId()} Email:{$quote->getCustomerEmail()}");
            $this->writeOnepageLog("Error Code:{$e->getCode()} Error Message: {$e->getMessage()}");
            $this->writeOnepageLog("Quote Id : {$quote->getId()} " .$e->getTraceAsString());
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
            $quote = $this->getOnepage()->getQuote();
            $this->writeOnepageLog("Exception :: Quote Id : {$quote->getId()} Email:{$quote->getCustomerEmail()}");
            $this->writeOnepageLog("Error Code:{$e->getCode()} Error Message: {$e->getMessage()}");
            $this->writeOnepageLog("Quote Id : {$quote->getId()} " .$e->getTraceAsString());
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

    /**
     * Order success action
     */
    public function successAction() {
        $session = $this->getOnepage()->getCheckout();
        if (!$session->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

		$lastRealOrderId = $session->getLastRealOrderId();
        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();

        if ($this->getLogStatus()){
            $this->writeOnepageLog("Checkout SUCCESS::#{$lastRealOrderId} Order Id: {$lastOrderId} Quote Id: {$lastQuoteId}");
        }

        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
        if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
            $this->_redirect('checkout/cart');
            return;
        }

        $session->clear();
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));
        $this->renderLayout();
    }

    /*
     * Added by mt-allure
     */
    public function successorderAction ()
    {
        $session = $this->getOnepage()->getCheckout();

        $sessionOrdered = $this->getOnepage()->getCheckoutOrdered();
        $sessionBackordered = $this->getOnepage()->getCheckoutBackordered();
        if (! $sessionOrdered->getLastSuccessQuoteId() && ! $sessionBackordered->getSecondLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

		$lastRealOrderId = $sessionOrdered->getLastRealOrderId();

        $lastQuoteId = $sessionOrdered->getLastQuoteId();
        $lastOrderId = $sessionOrdered->getLastOrderId();
        $lastRecurringProfiles = $sessionOrdered->getLastRecurringProfileIds();

        $secondLastQuoteId = $sessionBackordered->getSecondLastQuoteId();
        $secondLastOrderId = $sessionBackordered->getSecondLastOrderId();
        $secondLastRecurringProfiles = $sessionBackordered->getSecondLastRecurringProfileIds();

        if ($this->getLogStatus()){
            $this->writeOnepageLog("Checkout SUCCESS::#{$lastRealOrderId} Order Id: {$lastOrderId},{$secondLastOrderId} Quote Id: {$lastQuoteId},{$secondLastQuoteId}");
        }

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
}
