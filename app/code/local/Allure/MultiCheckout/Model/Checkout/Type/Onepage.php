<?php
require_once ('app/code/local/Amasty/Customerattr/Model/Rewrite/Checkout/Type/Onepage.php');

class Allure_MultiCheckout_Model_Checkout_Type_Onepage extends Amasty_Customerattr_Model_Rewrite_Checkout_Type_Onepage
{

    protected $_checkoutSessionOrdered;

    protected $_checkoutSessionBackordered;

    public function initCheckout ()
    {
        $checkout = $this->getCheckout();
        $customerSession = $this->getCustomerSession();
        if (is_array($checkout->getStepData())) {
            foreach ($checkout->getStepData() as $step => $data) {
                if (! ($step === 'login' || $customerSession->isLoggedIn() && $step === 'billing')) {
                    $checkout->setStepData($step, 'allow', false);
                }
            }
        }
        
        $quoteSave = false;
        $collectTotals = false;
        
        /**
         * Reset multishipping flag before any manipulations with quote address
         * addAddress method for quote object related on this flag
         */
        if ($this->getQuote()->getIsMultiShipping()) {
            $this->getQuote()->setIsMultiShipping(false);
            $quoteSave = true;
        }
        
        /**
         * Reset customer balance
         */
        if ($this->getQuote()->getUseCustomerBalance()) {
            $this->getQuote()->setUseCustomerBalance(false);
            $quoteSave = true;
            $collectTotals = true;
        }
        /**
         * Reset reward points
         */
        if ($this->getQuote()->getUseRewardPoints()) {
            $this->getQuote()->setUseRewardPoints(false);
            $quoteSave = true;
            $collectTotals = true;
        }
        
        // mt-allure code
        $this->getQuote()->setDeliveryMethod('one_ship');
        // change custom quote status
        $this->changeCustsomQuoteStatus();
        
        if ($collectTotals) {
            $this->getQuote()->collectTotals();
        }
        
        if ($quoteSave) {
            $this->getQuote()->save();
        }
        
        /*
         * want to load the correct customer information by assigning to address
         * instead of just loading from sales/quote_address
         */
        $customer = $customerSession->getCustomer();
        if ($customer) {
            $this->getQuote()->assignCustomer($customer);
        }
        return $this;
    }

    public function __construct ()
    {
        parent::__construct();
        $this->_checkoutSessionOrdered = Mage::getSingleton("allure_multicheckout/ordered_session");
        $this->_checkoutSessionBackordered = Mage::getSingleton("allure_multicheckout/backordered_session");
    }

    public function getCheckoutOrdered ()
    {
        if ($this->_checkoutSessionOrdered == null)
            $this->_checkoutSessionOrdered = Mage::getSingleton("allure_multicheckout/ordered_session");
        return $this->_checkoutSessionOrdered;
    }

    public function getCheckoutBackordered ()
    {
        if ($this->_checkoutSessionBackordered == null)
            $this->_checkoutSessionBackordered = Mage::getSingleton("allure_multicheckout/backordered_session");
        return $this->_checkoutSessionBackordered;
    }

    public function getQuoteOrdered ()
    {
        return $this->_checkoutSessionOrdered->getQuote();
    }

    public function getQuoteBackordered ()
    {
        return $this->_checkoutSessionBackordered->getQuote();
    }

    public function saveShippingMethod ($data)
    {
        if (is_array($data))
            $shippingMethod = $data['shipping_method'];
        else
            $shippingMethod = $data;
        
        if (is_null($data))
            return array();
        Mage::log($data, Zend_Log::DEBUG, 'mylogs', true);
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        Mage::log("Type-" . $this->getQuote()->getDeliveryMethod(), Zend_Log::DEBUG, 'mylogs', true);
        if (strtolower($this->getQuote()->getDeliveryMethod()) == strtolower($_checkoutHelper::TWO_SHIP)) {
            $shippingMethod2 = $data['mt_shipping_method'];
            
            if (! $this->checkQuoteItemsWithGiftCard($this->getQuoteBackordered())) {
                if (! array_key_exists('mt_shipping_method', $data)) {
                    $res = array(
                            "error" => - 1,
                            "message" => Mage::helper("checkout")->__("Invalid shipping method.")
                    );
                    return $res;
                }
                if (empty($shippingMethod2)) {
                    $res = array(
                            "error" => - 1,
                            "message" => Mage::helper("checkout")->__("Invalid shipping method.")
                    );
                    return $res;
                }
                
                $rate = $this->getQuoteBackordered()
                    ->getShippingAddress()
                    ->getShippingRateByCode($shippingMethod2);
                if (! $rate) {
                    $res = array(
                            "error" => - 1,
                            "message" => Mage::helper("checkout")->__("Invalid shipping method.")
                    );
                    return $res;
                }
                $this->getQuoteBackordered()
                    ->getShippingAddress()
                    ->setShippingMethod($shippingMethod2);
                $this->getQuoteBackordered()
                    ->collectTotals()
                    ->save();
            }
            if (! $this->checkQuoteItemsWithGiftCard($this->getQuoteOrdered())) {
                if (empty($shippingMethod)) {
                    // Mage::log($data,Zend_log::DEBUG,'abc',true);die;
                    $res = array(
                            "error" => - 1,
                            "message" => Mage::helper("checkout")->__("Invalid shipping method.")
                    );
                    return $res;
                }
                
                $rate = $this->getQuoteOrdered()
                    ->getShippingAddress()
                    ->getShippingRateByCode($shippingMethod);
                if (! $rate) {
                    $res = array(
                            "error" => - 1,
                            "message" => Mage::helper("checkout")->__("Invalid shipping method.")
                    );
                    return $res;
                }
                
                $this->getQuoteOrdered()
                    ->getShippingAddress()
                    ->setShippingMethod($shippingMethod);
                $this->getQuoteOrdered()
                    ->collectTotals()
                    ->save();
            }
            
            /*
             * if(isset($data['shipping_method']) &&
             * !empty($data['shipping_method'])){
             * $this->getQuoteOrdered()->getShippingAddress()->setCollectShippingRates(true)
             * ->collectShippingRates()->setShippingMethod($data['shipping_method']);
             * $this->getQuoteOrdered()->collectTotals()->save();
             * }
             *
             * if(isset($data['mt_shipping_method']) &&
             * !empty($data['mt_shipping_method'])){
             * $this->getQuoteBackordered()->getShippingAddress()->setCollectShippingRates(true)
             * ->collectShippingRates()->setShippingMethod($data['mt_shipping_method']);
             * $this->getQuoteBackordered()->collectTotals()->save();
             * }
             */
            
            // $this->divideQuoteAsOrderAndBackorder($data);
        } else {
            if (empty($shippingMethod)) {
                $res = array(
                        "error" => - 1,
                        "message" => Mage::helper("checkout")->__("Invalid shipping method.")
                );
                return $res;
            }
            $rate = $this->getQuote()
                ->getShippingAddress()
                ->getShippingRateByCode($shippingMethod);
            if (! $rate) {
                $res = array(
                        "error" => - 1,
                        "message" => Mage::helper("checkout")->__("Invalid shipping method.")
                );
                return $res;
            }
            
            $this->getQuote()->setDeliveryMethod($_checkoutHelper::ONE_SHIP);
            $this->getQuote()->setOrderType($_checkoutHelper::SINGLE_ORDER);
            $this->getQuote()
                ->getShippingAddress()
                ->setShippingMethod($shippingMethod);
            $this->getQuote()
                ->collectTotals()
                ->save();
        }
        
        $this->getCheckout()
            ->setStepData("shipping_method", "complete", true)
            ->setStepData("payment", "allow", true);
        
        return array();
    }

    private function checkQuoteItemsWithGiftCard ($quote)
    {
        $isSingleProductWithGiftCard = false;
        if ($quote) {
            if (count($quote->getAllVisibleItems()) == 1) {
                foreach ($quote->getAllVisibleItems() as $item) {
                    if ($item->getProduct()->getTypeId() == 'giftcards') {
                        $isSingleProductWithGiftCard = true;
                        break;
                    }
                }
            }
        }
        return $isSingleProductWithGiftCard;
    }

    public function saveShippingMethod000 ($shippingMethod)
    {
        if (empty($shippingMethod)) {
            $res = array(
                    "error" => - 1,
                    "message" => Mage::helper("checkout")->__("Invalid shipping method.")
            );
            return $res;
        }
        $rate = $this->getQuote()
            ->getShippingAddress()
            ->getShippingRateByCode($shippingMethod);
        if (! $rate) {
            $res = array(
                    "error" => - 1,
                    "message" => Mage::helper("checkout")->__("Invalid shipping method.")
            );
            return $res;
        }
        
        $this->getQuote()
            ->getShippingAddress()
            ->setShippingMethod($shippingMethod);
        $this->getQuote()
            ->collectTotals()
            ->save();
        
        $this->getCheckout()
            ->setStepData("shipping_method", "complete", true)
            ->setStepData("delivery_option", "allow", true);
        
        return array();
    }

    public function saveDeliveryOptions ($data)
    {
        // Save the data here
        if (empty($data)) {
            $res = array(
                    "error" => - 1,
                    "message" => Mage::helper("checkout")->__("Invalid delivery method.")
            );
            return $res;
        }
        $this->getQuote()->setDeliveryMethod($data['delivery']['method']);
        $this->getQuote()
            ->collectTotals()
            ->save();
        
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        if (strtolower($this->getQuote()->getDeliveryMethod()) == strtolower($_checkoutHelper::TWO_SHIP)) {
            $this->divideQuoteAsOrderAndBackorder($data);
        } else {
            $this->getQuote()->setOrderType($_checkoutHelper::SINGLE_ORDER);
            $this->getQuote()
                ->collectTotals()
                ->save();
        }
        $this->getCheckout()
            ->setStepData("delivery_option", "complete", true)
            ->setStepData("shipping_method", "allow", true);
        return array();
    }

    public function saveDeliveryOptions000 ($data)
    {
        // Save the data here
        if (empty($data)) {
            $res = array(
                    "error" => - 1,
                    "message" => Mage::helper("checkout")->__("Invalid shipping method.")
            );
            return $res;
        }
        $this->getQuote()->setDeliveryMethod($data['delivery']['method']);
        $this->getQuote()
            ->collectTotals()
            ->save();
        
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        if (strtolower($this->getQuote()->getDeliveryMethod()) == strtolower($_checkoutHelper::TWO_SHIP)) {
            $this->divideQuoteAsOrderAndBackorder($data);
        } else {
            $this->getQuote()->setOrderType($_checkoutHelper::SINGLE_ORDER);
            $shippingMethod = $this->getCheckout()->getInStockOrderShippingMethod();
            if (isset($shippingMethod) && ! empty($shippingMethod)) {
                $this->getQuote()
                    ->getShippingAddress()
                    ->setShippingMethod($shippingMethod);
            }
            $this->getQuote()
                ->collectTotals()
                ->save();
        }
        
        $this->getCheckout()
            ->setStepData("delivery_option", "complete", true)
            ->setStepData("payment", "allow", true);
        
        return array();
    }

    public function savePayment1111 ($data)
    {
        if (empty($data)) {
            return array(
                    'error' => - 1,
                    'message' => Mage::helper('checkout')->__('Invalid data.')
            );
        }
        $quote = $this->getQuote();
        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        } else {
            $quote->getShippingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        }
        
        // shipping totals may be affected by payment method
        if (! $quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }
        
        $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT |
                 Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY |
                 Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY |
                 Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX |
                 Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
        
        $payment = $quote->getPayment();
        $payment->importData($data);
        
        $quote->save();
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        if (strtolower($quote->getDeliveryMethod()) == strtolower($_checkoutHelper::TWO_SHIP)) {
            $this->divideQuoteAsOrderAndBackorder();
        }
        
        $this->getCheckout()
            ->setStepData('payment', 'complete', true)
            ->setStepData('review', 'allow', true);
        
        return array();
    }

    public function savePayment ($data)
    {
        if (empty($data)) {
            return array(
                    'error' => - 1,
                    'message' => Mage::helper('checkout')->__('Invalid data.')
            );
        }
        $quote = $this->getQuote();
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        
        if (isset($data['wholesale_pay_option']) && ! empty($data['wholesale_pay_option'])) {
            if ($_checkoutHelper::PAY_AS_SHIP == $data['wholesale_pay_option']) {
                if ($this->isQuoteContainsBackorder() &&
                         strtolower($quote->getDeliveryMethod()) == strtolower($_checkoutHelper::ONE_SHIP))
                    $quote->setIsReadyToShip(1);
            }
        }
        
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        // apply payment method to one shipment by mt-allure.
        // Mage::log($quote->getIsReadyToShip(),Zend_log::DEBUG,'abc',true);
        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        } else {
            $quote->getShippingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        }
        
        // shipping totals may be affected by payment method
        if (! $quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }
        
        $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT |
                 Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY |
                 Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY |
                 Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX |
                 Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
        
        $payment = $quote->getPayment();
        $payment->importData($data);
        
        $quote->save();
        
        if (strtolower($quote->getDeliveryMethod()) == strtolower($_checkoutHelper::TWO_SHIP)) {
            // Mage::log($data,Zend_log::DEBUG,'abc',true);
            // apply payment method to two shipment by mt-allure.
            $this->applyPaymentToSecondShipment($data);
        } else {
            if (isset($data['wholesale_pay_option']) && ! empty($data['wholesale_pay_option'])) {
                if ($_checkoutHelper::PAY_AS_SHIP == $data['wholesale_pay_option']) {
                    $quote->setIsReadyToShip(1);
                } else {
                    $quote->setIsReadyToShip(0);
                }
                $quote->save();
            }
        }
        
        $this->getCheckout()
            ->setStepData('payment', 'complete', true)
            ->setStepData('review', 'allow', true);
        
        return array();
    }

    private function applyPaymentToSecondShipment ($data)
    {
        $quoteOrdered = $this->getQuoteOrdered();
        $quoteBackOrdered = $this->getQuoteBackordered();
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        if ($quoteOrdered) {
            $quoteOrdered->getPayment()->setId(null);
            if ($quoteOrdered->isVirtual()) {
                $quoteOrdered->getBillingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
            } else {
                $quoteOrdered->getShippingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
            }
            
            // shipping totals may be affected by payment method
            if (! $quoteOrdered->isVirtual() && $quoteOrdered->getShippingAddress()) {
                $quoteOrdered->getShippingAddress()->setCollectShippingRates(true);
            }
            // Mage::log("sip-1:".$quoteOrdered->getShippingAddress()->getShippingMethod(),Zend_log::DEBUG,'abc',true);
            $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT |
                     Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY |
                     Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY |
                     Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX |
                     Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
            
            $payment = $quoteOrdered->getPayment();
            $payment->importData($data);
            
            $quoteOrdered->save();
        }
        
        if ($quoteBackOrdered) {
            $quoteBackOrdered->getPayment()->setId(null);
            if (isset($data['wholesale_pay_option']) && ! empty($data['wholesale_pay_option'])) {
                if ($_checkoutHelper::PAY_AS_SHIP == $data['wholesale_pay_option'])
                    
                    $quoteBackOrdered->setIsReadyToShip(1);
            }
            
            if ($quoteBackOrdered->isVirtual()) {
                $quoteBackOrdered->getBillingAddress()->setPaymentMethod(
                        isset($data['method']) ? $data['method'] : null);
            } else {
                $quoteBackOrdered->getShippingAddress()->setPaymentMethod(
                        isset($data['method']) ? $data['method'] : null);
            }
            
            // shipping totals may be affected by payment method
            if (! $quoteBackOrdered->isVirtual() && $quoteBackOrdered->getShippingAddress()) {
                $quoteBackOrdered->getShippingAddress()->setCollectShippingRates(true);
            }
            // Mage::log("sip-2:".$quoteBackOrdered->getShippingAddress()->getShippingMethod(),Zend_log::DEBUG,'abc',true);
            $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT |
                     Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY |
                     Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY |
                     Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX |
                     Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
            
            $payment = $quoteBackOrdered->getPayment();
            $payment->importData($data);
            
            $quoteBackOrdered->save();
        }
    }

    private function divideQuoteAsOrderAndBackorder ($data)
    {
        // Mage::log(json_encode($this->getQuote()->getShippingAddress()->getData()),Zend_log::DEBUG,'abc',true);
        /* Mage::log($data['shipping_method'],Zend_log::DEBUG,'abc',true); */
        $isBackorder = $this->isQuoteContainsBackorder();
        $quoteMain = $this->getQuote(); // this is main quote object.
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        $this->changeCustsomQuoteStatus();
        
        if ($isBackorder) {
            $quoteItems = $quoteMain->getAllVisibleItems(); // $quoteMain->getAllItems();
            
            $backorder_quote = Mage::getModel('sales/quote')->load($quoteMain->getId()); // unavialable
                                                                                         // qty
                                                                                         // product
                                                                                         // qoute.
            $order_quote = Mage::getModel('sales/quote')->load($quoteMain->getId());
            $session = Mage::getSingleton('customer/session', array(
                    'name' => 'frontend'
            ));
            
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $backorder_quote->setId(null);
            $order_quote->setId(null);
            foreach ($quoteItems as $item) {
                $productInvryCount = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProduct())
                    ->getQty();
                $stock_qty = intval($item->getProduct()
                    ->getStockItem()
                    ->getQty());
                if ($stock_qty < $item->getQty()) {
                    // if( $productInvryCount <= 0 ){
                    // $backorder_quote->addItem($item->setId(null));
                    $backorder_quote->addProduct($item->getProduct(), $item->getBuyRequest());
                } else {
                    // $order_quote->addItem($item->setId(null));
                    $order_quote->addProduct($item->getProduct(), $item->getBuyRequest());
                }
            }
            
            if (count($backorder_quote->getAllItems()) > 0) {
                $backorder_quote->getBillingAddress()->addData($quoteMain->getBillingAddress()
                    ->setId(null)
                    ->getData());
                $backorder_quote->getShippingAddress()->addData(
                        $quoteMain->getShippingAddress()
                            ->setId(null)
                            ->getData());
                $backorder_quote->setOrderType($_checkoutHelper::MULTI_BACK_ORDER);
                $backorder_quote->save();
                $backorder_quote->setIsChildOrder(1)->save();
                
                $backorder_quote->getShippingAddress()->setCollectShippingRates(true);
                
                $backorder_quote->collectTotals()->save();
                // Mage::log(json_encode($backorder_quote->getShippingAddress()->getData()),Zend_log::DEBUG,'abc',true);
                $this->getCheckoutBackordered()->setBackorder($backorder_quote->getId());
                $this->getCheckoutBackordered()->setQuoteId($backorder_quote->getId());
                
                $this->getQuoteBackordered()
                    ->getShippingAddress()
                    ->setCollectShippingRates(true);
                $this->getQuoteBackordered()
                    ->collectTotals()
                    ->save();
            }
            
            if (count($order_quote->getAllItems()) > 0) {
                $order_quote->getBillingAddress()->addData($quoteMain->getBillingAddress()
                    ->setId(null)
                    ->getData());
                $order_quote->getShippingAddress()->addData($quoteMain->getShippingAddress()
                    ->setId(null)
                    ->getData());
                $order_quote->setOrderType($_checkoutHelper::MULTI_MAIN_ORDER);
                $order_quote->save();
                $order_quote->setIsChildOrder(1)->save();
                
                $order_quote->getShippingAddress()->setCollectShippingRates(true);
               
                $order_quote->collectTotals()->save();
                // Mage::log(json_encode($order_quote->getShippingAddress()->getData()),Zend_log::DEBUG,'abc',true);
                $this->getCheckoutOrdered()->setOrdered($order_quote->getId());
                $this->getCheckoutOrdered()->setQuoteId($order_quote->getId());
                
                $this->getQuoteOrdered()
                    ->getShippingAddress()
                    ->setCollectShippingRates(true);
                $this->getQuoteOrdered()
                    ->collectTotals()
                    ->save();
            }
            if (! (count($order_quote->getAllItems()) > 0 && count($backorder_quote->getAllItems()) > 0)) {
                $this->getQuote()->setOrderType($_checkoutHelper::SINGLE_ORDER);
                $this->getQuote()
                    ->setDeliveryMethod($_checkoutHelper::ONE_SHIP)
                    ->save();
            }
        } else {
            $this->getQuote()->setOrderType($_checkoutHelper::SINGLE_ORDER);
            $this->getQuote()
                ->setDeliveryMethod($_checkoutHelper::ONE_SHIP)
                ->save();
        }
    }

    /*
     * This function used for check quote contains back ordered
     * product is available or not.
     * output - true or false.
     *
     */
    private function isQuoteContainsBackorder ()
    {
        $isBackorderAvailable = false;
        $quote = $this->getQuote();
        $qouteItems = $quote->getAllVisibleItems(); // getAllItems();
        foreach ($qouteItems as $item) :
            $productInventoryQty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProduct())
                ->getQty();
            
            $stock_qty = intval($item->getProduct()
                ->getStockItem()
                ->getQty());
            if ($stock_qty < $item->getQty()) :
                $isBackorderAvailable = true;
                break;
			endif;
            
            /*
             * if($productInventoryQty<=0):
             * $isBackorderAvailable = true;
             * break;
             * endif;
             */
        endforeach
        ;
        return $isBackorderAvailable;
    }

    public function changeCustsomQuoteStatus ()
    {
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        $orederdQuoteId = $this->getCheckoutOrdered()->getOrdered();
        $backOrderdQuoteId = $this->getCheckoutBackordered()->getBackorder();
        if (isset($orederdQuoteId) && ! empty($orederdQuoteId)) {
            if ($this->getQuoteOrdered()->getId() != 0 && $this->getQuoteOrdered()->getId() != $this->getQuote()->getId()) {
                $this->getQuoteOrdered()
                    ->setIsActive(false)
                    ->save();
            }
            $this->getCheckoutOrdered()->setOrdered(null);
        }
        
        if (isset($backOrderdQuoteId) && ! empty($backOrderdQuoteId)) {
            if ($this->getQuoteBackordered()->getId() != 0 &&
                     $this->getQuoteBackordered()->getId() != $this->getQuote()->getId()) {
                $this->getQuoteBackordered()
                    ->setIsActive(false)
                    ->save();
            }
            $this->getCheckoutBackordered()->setBackorder(null);
        }
    }

    /**
     * Prepare quote for customer registration and customer order submit
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function _prepareNewCustomerQuoteForCustom ()
    {
        $quote = $this->getQuoteOrdered();
        // s$quote = $this->getQuoteBackordered();
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();
        
        // $customer = Mage::getModel('customer/customer');
        $customer = $quote->getCustomer();
        /* @var $customer Mage_Customer_Model_Customer */
        $customerBilling = $billing->exportCustomerAddress();
        $customer->addAddress($customerBilling);
        $billing->setCustomerAddress($customerBilling);
        $customerBilling->setIsDefaultBilling(true);
        if ($shipping && ! $shipping->getSameAsBilling()) {
            $customerShipping = $shipping->exportCustomerAddress();
            $customer->addAddress($customerShipping);
            $shipping->setCustomerAddress($customerShipping);
            $customerShipping->setIsDefaultShipping(true);
        } else {
            $customerBilling->setIsDefaultShipping(true);
        }
        
        Mage::helper('core')->copyFieldset('checkout_onepage_quote', 'to_customer', $quote, $customer);
        $customer->setPassword($customer->decryptPassword($quote->getPasswordHash()));
        $quote->setCustomer($customer)->setCustomerId(true);
    }

    /**
     * Prepare quote for customer order submit
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function _prepareCustomerQuoteForCustom ()
    {
        $quote = $this->getQuoteBackordered();
        // $quote = $this->getQuoteOrdered();
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();
        
        $customer = $this->getCustomerSession()->getCustomer();
        if (! $billing->getCustomerId() || $billing->getSaveInAddressBook()) {
            $customerBilling = $billing->exportCustomerAddress();
            $customer->addAddress($customerBilling);
            $billing->setCustomerAddress($customerBilling);
        }
        if ($shipping && ! $shipping->getSameAsBilling() &&
                 (! $shipping->getCustomerId() || $shipping->getSaveInAddressBook())) {
            $customerShipping = $shipping->exportCustomerAddress();
            $customer->addAddress($customerShipping);
            $shipping->setCustomerAddress($customerShipping);
        }
        
        if (isset($customerBilling) && ! $customer->getDefaultBilling()) {
            $customerBilling->setIsDefaultBilling(true);
        }
        if ($shipping && isset($customerShipping) && ! $customer->getDefaultShipping()) {
            $customerShipping->setIsDefaultShipping(true);
        } else 
            if (isset($customerBilling) && ! $customer->getDefaultShipping()) {
                $customerBilling->setIsDefaultShipping(true);
            }
        $quote->setCustomer($customer);
    }

    /**
     * Involve new customer to system
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function _involveNewCustomerForCustom ()
    {
        $customer = $this->getQuoteOrdered()->getCustomer();
        // $customer = $this->getQuoteBackordered()->getCustomer();
        if ($customer->isConfirmationRequired()) {
            $customer->sendNewAccountEmail('confirmation', '', $this->getQuote()
                ->getStoreId());
            $url = Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail());
            $this->getCustomerSession()->addSuccess(
                    Mage::helper('customer')->__(
                            'Account confirmation is required. Please, check your e-mail for confirmation link. To resend confirmation email please <a href="%s">click here</a>.',
                            $url));
        } else {
            $customer->sendNewAccountEmail('registered', '', $this->getQuote()
                ->getStoreId());
            $this->getCustomerSession()->loginById($customer->getId());
        }
        return $this;
    }

    public function saveOrder111 ($data)
    {
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        if (strtolower($this->getQuote()->getDeliveryMethod()) == strtolower($_checkoutHelper::ONE_SHIP)) {
            return parent::saveOrder();
        } else {
            $this->validate();
            $isNewCustomer = false;
            switch ($this->getCheckoutMethod()) {
                case self::METHOD_GUEST:
                    $this->_prepareGuestQuote();
                    break;
                case self::METHOD_REGISTER:
                    $this->_prepareNewCustomerQuoteForCustom();
                    $isNewCustomer = true;
                    break;
                default:
                    $this->_prepareCustomerQuote();
                    break;
            }
            
            /* 2nd : Out of stock product order Start */
            $backOrderedQuote = $this->getQuoteBackordered();
            $quote = $this->getQuoteBackordered();
            Mage::log("\n********************** OUT OF STOCK START **************************", Zend_Log::DEBUG,
                    'multiorder.log', true);
            Mage::log("\n----------Quote Data-------", Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log(json_encode($quote->getData()), Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log("\n----------Shipping Address-------", Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log(json_encode($quote->getShippingAddress()->getData()), Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log("\n----------Shipping Method-------", Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log(json_encode($quote->getShippingAddress()->getShippingMethod()), Zend_log::DEBUG, 'multiorder.log',
                    true);
            Mage::log("\n----------Payment Data-------", Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log(json_encode($data), Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log("\n********************** OUT OF STOCK END **************************", Zend_Log::DEBUG,
                    'multiorder.log', true);
            if (! $quote->getIsCheckoutCart()) {
                $quote->collectTotals()->save();
                $quote->getPayment()->importData($data);
                
                if ($this->getCheckoutMethod() == self::METHOD_REGISTER)
                    $quote['customer_id'] = true;
                
                $service = Mage::getModel('sales/service_quote', $quote);
                
                if ($backOrderedQuote->getIsReadyToShip())
                    $service->submitAll();
                else
                    $service->submitCustomQuote();
                
                if ($isNewCustomer) {
                    try {
                        $this->_involveNewCustomerForCustom();
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                }
                
                $this->getCheckoutBackordered()
                    ->setSecondLastQuoteId($quote->getId())
                    ->setSecondLastSuccessQuoteId($quote->getId())
                    ->clearHelperData();
                $order = $service->getOrder();
                $firstOrder = $order;
                
                if ($order) {
                    Mage::dispatchEvent('checkout_type_onepage_save_order_after',
                            array(
                                    'order' => $order,
                                    'quote' => $quote
                            ));
                    $redirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();
                    $redirectUrl1 = $redirectUrl;
                    
                    // add order information to the session
                    $this->getCheckoutBackordered()
                        ->setSecondLastOrderId($order->getId())
                        ->setRedirectUrl($redirectUrl)
                        ->setSecondLastRealOrderId($order->getIncrementId());
                    
                    // as well a billing agreement can be created
                    $agreement = $order->getPayment()->getBillingAgreement();
                    if ($agreement) {
                        $this->getCheckoutBackordered()->setSecondLastBillingAgreementId($agreement->getId());
                    }
                }
                
                // add recurring profiles information to the session
                $profiles = $service->getRecurringPaymentProfiles();
                if ($profiles) {
                    $ids = array();
                    foreach ($profiles as $profile) {
                        $ids[] = $profile->getId();
                    }
                    $this->getCheckoutBackordered()->setSecondLastRecurringProfileIds($ids);
                    // TODO: send recurring profile emails
                }
                
                Mage::dispatchEvent('checkout_submit_all_after',
                        array(
                                'order' => $order,
                                'quote' => $quote,
                                'recurring_profiles' => $profiles
                        ));
                // return $this;
            }
            /* Out of stock product order Start */
            
            /* 1st : In stock product order Start */
            $quote = $this->getQuoteOrdered();
            Mage::log("\n********************** IN STOCK START **************************", Zend_Log::DEBUG,
                    'multiorder.log', true);
            Mage::log("\n----------Quote Data-------", Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log(json_encode($quote->getData()), Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log("\n----------Shipping Address-------", Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log(json_encode($quote->getShippingAddress()->getData()), Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log("\n----------Shipping Method-------", Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log(json_encode($quote->getShippingAddress()->getShippingMethod()), Zend_log::DEBUG, 'multiorder.log',
                    true);
            Mage::log("\n----------Payment Data-------", Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log(json_encode($data), Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log("\n********************** IN STOCK END **************************", Zend_Log::DEBUG,
                    'multiorder.log', true);
            if (! $quote->getIsCheckoutCart()) {
                
                $quote->collectTotals()->save();
                $quote->getPayment()->importData($data);
                
                if ($isNewCustomer) {
                    $this->_prepareCustomerQuoteForCustom();
                }
                
                $service = Mage::getModel('sales/service_quote', $quote);
                if ($backOrderedQuote->getIsReadyToShip()) {
                    $service->submitAll();
                } else {
                    Mage::getSingleton('checkout/session')->setOutOfStockOrder($firstOrder->getId());
                    $service->submitOrdersPayment($firstOrder->getId());
                    Mage::getSingleton('checkout/session')->setOutOfStockOrder(0);
                }
                
                $this->getCheckoutOrdered()
                    ->setLastQuoteId($quote->getId())
                    ->setLastSuccessQuoteId($quote->getId())
                    ->clearHelperData();
                
                $order = $service->getOrder();
                $secondOrder = $order;
                if ($order) {
                    Mage::dispatchEvent('checkout_type_onepage_save_order_after',
                            array(
                                    'order' => $order,
                                    'quote' => $quote
                            ));
                    /**
                     * a flag to set that there will be redirect to third party
                     * after confirmation
                     * eg: paypal standard ipn
                     */
                    $redirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();
                    $redirectUrl2 = $redirectUrl;
                    /**
                     * we only want to send to customer about new order when
                     * there is no redirect to third party
                     */
                    
                    if ((! $redirectUrl1 && $firstOrder->getCanSendNewEmailFlag()) &&
                             (! $redirectUrl2 && $secondOrder->getCanSendNewEmailFlag())) {
                        try {
                            $secondOrder->queueNewOrderSplitEmail($firstOrder->getId());
                        } catch (Exception $e) {
                            Mage::logException($e);
                        }
                    }
                    
                    // add order information to the session
                    $this->getCheckoutOrdered()
                        ->setLastOrderId($order->getId())
                        ->setRedirectUrl($redirectUrl)
                        ->setLastRealOrderId($order->getIncrementId());
                    
                    // as well a billing agreement can be created
                    $agreement = $order->getPayment()->getBillingAgreement();
                    if ($agreement) {
                        $this->getCheckoutOrdered()->setLastBillingAgreementId($agreement->getId());
                    }
                }
                
                // add recurring profiles information to the session
                $profiles = $service->getRecurringPaymentProfiles();
                if ($profiles) {
                    $ids = array();
                    foreach ($profiles as $profile) {
                        $ids[] = $profile->getId();
                    }
                    $this->getCheckoutOrdered()->setLastRecurringProfileIds($ids);
                    // TODO: send recurring profile emails
                }
                
                Mage::dispatchEvent('checkout_submit_all_after',
                        array(
                                'order' => $order,
                                'quote' => $quote,
                                'recurring_profiles' => $profiles
                        ));
                return $this;
            }
            /* In stock product order end */
        }
        return $this;
    }

    public function saveOrder ()
    {
        Mage::getSingleton('checkout/session')->setIsSingleCharge(false);
        Mage::getSingleton('checkout/session')->setBaseTotal(0);
        return parent::saveOrder();
    }

    public function saveCustomOrder ($data)
    {
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        if (strtolower($this->getQuote()->getDeliveryMethod()) == strtolower($_checkoutHelper::ONE_SHIP) ||
                 $this->getQuote()->getDeliveryMethod() == "") {
            Mage::getSingleton('checkout/session')->setIsSingleCharge(false);
            Mage::getSingleton('checkout/session')->setBaseTotal(0);
            return parent::saveOrder();
        } else {
            $this->validate();
            $isNewCustomer = false;
            switch ($this->getCheckoutMethod()) {
                case self::METHOD_GUEST:
                    $this->_prepareGuestQuote();
                    break;
                case self::METHOD_REGISTER:
                    $this->_prepareNewCustomerQuoteForCustom();
                    $isNewCustomer = true;
                    break;
                default:
                    $this->_prepareCustomerQuote();
                    break;
            }
            
            /* 1st : In stock product order Start */
            $backOrderedQuote = $this->getQuoteBackordered();
            $quote = $this->getQuoteOrdered();
            Mage::log("\n********************** IN STOCK START **************************", Zend_Log::DEBUG,
                    'multiorder.log', true);
            Mage::log("\n----------Quote Data-------", Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log(json_encode($quote->getData()), Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log("\n----------Shipping Address-------", Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log(json_encode($quote->getShippingAddress()->getData()), Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log("\n----------Shipping Method-------", Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log(json_encode($quote->getShippingAddress()->getShippingMethod()), Zend_log::DEBUG, 'multiorder.log',
                    true);
            Mage::log("\n----------Payment Data-------", Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log(json_encode($data), Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log("\n********************** IN STOCK END **************************", Zend_Log::DEBUG,
                    'multiorder.log', true);
            if (! $quote->getIsCheckoutCart()) {
                $quote->collectTotals()->save();
                $quote->getPayment()->importData($data);
                
                if ($this->getCheckoutMethod() == self::METHOD_REGISTER)
                    $quote['customer_id'] = true;
                
                $service = Mage::getModel('sales/service_quote', $quote);
                // $service->submitAll();
                if ($backOrderedQuote->getIsReadyToShip()) {
                    $service->submitAll();
                } else {
                    // Mage::getSingleton('checkout/session')->setOutOfStockOrder($firstOrder->getId());
                    Mage::getSingleton('checkout/session')->setIsSingleCharge(true);
                    Mage::getSingleton('checkout/session')->setBaseTotal($backOrderedQuote->getBaseGrandTotal());
                    $service->submitOrdersPayment(0);
                    Mage::getSingleton('checkout/session')->setBaseTotal(0);
                    Mage::getSingleton('checkout/session')->setIsSingleCharge(false);
                    // Mage::getSingleton('checkout/session')->setOutOfStockOrder(0);
                }
                
                if ($isNewCustomer) {
                    try {
                        $this->_involveNewCustomerForCustom();
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                }
                
                $this->getCheckoutOrdered()
                    ->setLastQuoteId($quote->getId())
                    ->setLastSuccessQuoteId($quote->getId())
                    ->clearHelperData();
                
                $order = $service->getOrder();
                $firstOrder = $order;
                if ($order) {
                    Mage::dispatchEvent('checkout_type_onepage_save_order_after',
                            array(
                                    'order' => $order,
                                    'quote' => $quote
                            ));
                    /**
                     * a flag to set that there will be redirect to third party
                     * after confirmation
                     * eg: paypal standard ipn
                     */
                    $redirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();
                    $redirectUrl1 = $redirectUrl;
                    /**
                     * we only want to send to customer about new order when
                     * there is no redirect to third party
                     */
                    /*
                     * if (!$redirectUrl && $order->getCanSendNewEmailFlag()) {
                     * try {
                     * $order->queueNewOrderEmail();
                     * } catch (Exception $e) {
                     * Mage::logException($e);
                     * }
                     * }
                     */
                    
                    // add order information to the session
                    $this->getCheckoutOrdered()
                        ->setLastOrderId($order->getId())
                        ->setRedirectUrl($redirectUrl)
                        ->setLastRealOrderId($order->getIncrementId());
                    
                    // as well a billing agreement can be created
                    $agreement = $order->getPayment()->getBillingAgreement();
                    if ($agreement) {
                        $this->getCheckoutOrdered()->setLastBillingAgreementId($agreement->getId());
                    }
                }
                
                // add recurring profiles information to the session
                $profiles = $service->getRecurringPaymentProfiles();
                if ($profiles) {
                    $ids = array();
                    foreach ($profiles as $profile) {
                        $ids[] = $profile->getId();
                    }
                    $this->getCheckoutOrdered()->setLastRecurringProfileIds($ids);
                    // TODO: send recurring profile emails
                }
                
                Mage::dispatchEvent('checkout_submit_all_after',
                        array(
                                'order' => $order,
                                'quote' => $quote,
                                'recurring_profiles' => $profiles
                        ));
            }
            /* In stock product order end */
            
            /* 2nd : Out of stock product order Start */
            
            $quote = $this->getQuoteBackordered();
            Mage::log("\n********************** OUT OF STOCK START **************************", Zend_Log::DEBUG,
                    'multiorder.log', true);
            Mage::log("\n----------Quote Data-------", Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log(json_encode($quote->getData()), Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log("\n----------Shipping Address-------", Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log(json_encode($quote->getShippingAddress()->getData()), Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log("\n----------Shipping Method-------", Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log(json_encode($quote->getShippingAddress()->getShippingMethod()), Zend_log::DEBUG, 'multiorder.log',
                    true);
            Mage::log("\n----------Payment Data-------", Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log(json_encode($data), Zend_log::DEBUG, 'multiorder.log', true);
            Mage::log("\n********************** OUT OF STOCK END **************************", Zend_Log::DEBUG,
                    'multiorder.log', true);
            if (! $quote->getIsCheckoutCart()) {
                $quote->collectTotals()->save();
                $quote->getPayment()->importData($data);
                
                if ($isNewCustomer) {
                    $this->_prepareCustomerQuoteForCustom();
                }
                
                $service = Mage::getModel('sales/service_quote', $quote);
                // $service->submitAll();
                $isSingleCharge = false;
                if ($backOrderedQuote->getIsReadyToShip()) {
                    // $service->submitAll();
                    $isSingleCharge = false;
                } else {
                    $isSingleCharge = true; // will not charge back order payment
                }
                $service->submitCustomQuote($firstOrder->getId(), $isSingleCharge);
                
                $this->getCheckoutBackordered()
                    ->setSecondLastQuoteId($quote->getId())
                    ->setSecondLastSuccessQuoteId($quote->getId())
                    ->clearHelperData();
                $order = $service->getOrder();
                $secondOrder = $order;
                $firstOrderIncrementId = $firstOrder->getIncrementId();
                if ($order) {
                    Mage::dispatchEvent('checkout_type_onepage_save_order_after',
                            array(
                                    'order' => $order,
                                    'quote' => $quote
                            ));
                    $redirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();
                    $redirectUrl2 = $redirectUrl;
                    
                    if ((! $redirectUrl1 && $firstOrder->getCanSendNewEmailFlag()) &&
                             (! $redirectUrl2 && $secondOrder->getCanSendNewEmailFlag())) {
                        try {
                            $firstOrder->queueNewOrderSplitEmail($secondOrder->getId());
                        } catch (Exception $e) {
                            Mage::logException($e);
                        }
                    }
                    
                    // add order information to the session
                    $this->getCheckoutBackordered()
                        ->setSecondLastOrderId($order->getId())
                        ->setRedirectUrl($redirectUrl)
                        ->setSecondLastRealOrderId($order->getIncrementId());
                    
                    // as well a billing agreement can be created
                    $agreement = $order->getPayment()->getBillingAgreement();
                    if ($agreement) {
                        $this->getCheckoutBackordered()->setSecondLastBillingAgreementId($agreement->getId());
                    }
                }
                
                // add recurring profiles information to the session
                $profiles = $service->getRecurringPaymentProfiles();
                if ($profiles) {
                    $ids = array();
                    foreach ($profiles as $profile) {
                        $ids[] = $profile->getId();
                    }
                    $this->getCheckoutBackordered()->setSecondLastRecurringProfileIds($ids);
                    // TODO: send recurring profile emails
                }

                Mage::dispatchEvent('checkout_submit_all_after',
                        array(
                                'order' => $order,
                                'quote' => $quote,
                                'recurring_profiles' => $profiles
                        ));
                
                return $this;
            }
            /* Out of stock product order Start */
        }
        return $this;
    }
}
