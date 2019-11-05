<?php
/**
 * override model
 * 
 */
require_once ('app/code/local/Amasty/Customerattr/Model/Rewrite/Checkout/Type/Onepage.php');
class Allure_MultiCheckout_Model_Checkout_Type_Onepage extends Amasty_Customerattr_Model_Rewrite_Checkout_Type_Onepage
{
    protected $_checkoutSessionOrdered;
    protected $_checkoutSessionBackordered;
    
    public function __construct(){
        parent::__construct();
        $this->_checkoutSessionOrdered = Mage::getSingleton("allure_multicheckout/ordered_session");
        $this->_checkoutSessionBackordered = Mage::getSingleton("allure_multicheckout/backordered_session");
    }
    
    /**
     * custom checkout session for
     * instock item handling.
     */
    public function getCheckoutOrdered(){
        if ($this->_checkoutSessionOrdered == null)
            $this->_checkoutSessionOrdered = Mage::getSingleton("allure_multicheckout/ordered_session");
        return $this->_checkoutSessionOrdered;
    }
    
    /**
     * custom checkout session for 
     * out of stock item handling.
     */
    public function getCheckoutBackordered(){
        if ($this->_checkoutSessionBackordered == null)
            $this->_checkoutSessionBackordered = Mage::getSingleton("allure_multicheckout/backordered_session");
        return $this->_checkoutSessionBackordered;
    }
    
    /**
     * get quote of instock item.
     */
    public function getQuoteOrdered(){
        return $this->_checkoutSessionOrdered->getQuote();
    }
    
    /**
     * get quote of out of stock item.
     */
    public function getQuoteBackordered(){
        return $this->_checkoutSessionBackordered->getQuote();
    }

    /**
     * override method.
     */
    public function initCheckout(){
        parent::initCheckout();
        $checkout = $this->getCheckout();
        $customerSession = $this->getCustomerSession();
        if (is_array($checkout->getStepData())) {
            foreach ($checkout->getStepData() as $step => $data) {
                if (! ($step === 'login' || $customerSession->isLoggedIn() && $step === 'shipping')) {
                    $checkout->setStepData($step, 'allow', false);
                }
            }
        }
        $quoteSave = false;
        $collectTotals = false;
        $this->getQuote()->setDeliveryMethod('one_ship');
        
        if (Mage::helper('core')->isModuleEnabled('Allure_Virtualstore')){
            $storeId = $this->getQuote()->getStoreId();
            $this->getQuote()->setOldStoreId($storeId);
            $quoteSave = true;
        }
        $this->changeCustomQuoteStatus();
        
        if ($collectTotals) {
            $this->getQuote()->collectTotals();
        }
        
        if ($quoteSave) {
            $this->getQuote()->save();
        }
        return $this;
    }

    /**
     * save shipping method for single shipment 
     * or two shipment.
     */
    public function saveShippingMethod ($data)
    {
        if (is_null($data)) return array();
        
        if (is_array($data)){
            $shippingMethod = $data['shipping_method'];
        }else {
            $shippingMethod = $data;
        }
        
        $deliveryMethod = $this->getQuote()->getDeliveryMethod();
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        if (strtolower($deliveryMethod) == strtolower($_checkoutHelper::TWO_SHIP)){
            $shippingMethod2 = $data['mt_shipping_method'];

            if (! $this->checkQuoteItemsWithGiftCard($this->getQuoteBackordered())) {
                if (! array_key_exists('mt_shipping_method', $data)) {
                   return array(
                        "error" => - 1,
                        "message" => Mage::helper("checkout")->__("Invalid shipping method.")
                    );
                }
                if (empty($shippingMethod2)) {
                    return array(
                        "error" => - 1,
                        "message" => Mage::helper("checkout")->__("Invalid shipping method.")
                    );
                }

                $rate = $this->getQuoteBackordered()
                    ->getShippingAddress()
                    ->getShippingRateByCode($shippingMethod2);
                if (! $rate) {
                    return array(
                        "error" => - 1,
                        "message" => Mage::helper("checkout")->__("Invalid shipping method.")
                    );
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
                    return array(
                        "error" => - 1,
                        "message" => Mage::helper("checkout")->__("Invalid shipping method.")
                    );
                }

                $rate = $this->getQuoteOrdered()
                    ->getShippingAddress()
                    ->getShippingRateByCode($shippingMethod);
                if (! $rate) {
                    return array(
                        "error" => - 1,
                        "message" => Mage::helper("checkout")->__("Invalid shipping method.")
                    );
                }

                $this->getQuoteOrdered()
                    ->getShippingAddress()
                    ->setShippingMethod($shippingMethod);
                $this->getQuoteOrdered()
                    ->collectTotals()
                    ->save();
            }

        } else {
            if (empty($shippingMethod)) {
                return array(
                    "error" => - 1,
                    "message" => Mage::helper("checkout")->__("Invalid shipping method.")
                );
            }
            $rate = $this->getQuote()
                ->getShippingAddress()
                ->getShippingRateByCode($shippingMethod);
            if (! $rate) {
                return array(
                    "error" => - 1,
                    "message" => Mage::helper("checkout")->__("Invalid shipping method.")
                );
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

    /**
     * save delivery option.
     */
    public function saveDeliveryOptions($data)
    {
        if (empty($data)) {
            return  array(
                "error" => - 1,
                "message" => Mage::helper("checkout")->__("Invalid delivery method.")
            );
        }
        
        $this->changeCustomQuoteStatus();
        
        $this->getQuote()->setDeliveryMethod($data['delivery']['method']);
        $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        $this->getQuote()->collectTotals()->save();

        $_checkoutHelper = Mage::helper('allure_multicheckout');
        $deliveryMethod = $this->getQuote()->getDeliveryMethod();
        if (strtolower($deliveryMethod) == strtolower($_checkoutHelper::TWO_SHIP)){
            $this->divideQuoteAsOrderAndBackorder($data);
        } else {
            $this->getQuote()->setOrderType($_checkoutHelper::SINGLE_ORDER);
            $this->getQuote()->collectTotals()->save();
        }
        $this->getCheckout()
            ->setStepData("delivery_option", "complete", true)
            ->setStepData("shipping_method", "allow", true);
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

        $quote->setIsReadyToShip(0);
        $quote->setWholesalePayOption($_checkoutHelper::PAY_NOW);

        if (isset($data['wholesale_pay_option']) && ! empty($data['wholesale_pay_option'])) {
            if ($_checkoutHelper::PAY_AS_SHIP == $data['wholesale_pay_option']) {

                if ($this->isQuoteContainsBackorder() && strtolower($quote->getDeliveryMethod()) == strtolower($_checkoutHelper::ONE_SHIP)) {
                    $quote->setWholesalePayOption($_checkoutHelper::PAY_AS_SHIP);
                    $quote->setIsReadyToShip(1);
                }
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
            // apply payment method to two shipment by mt-allure.
            $this->applyPaymentToSecondShipment($data);
        } else {
            if (isset($data['wholesale_pay_option']) && ! empty($data['wholesale_pay_option'])) {
                if ($_checkoutHelper::PAY_AS_SHIP == $data['wholesale_pay_option']) {
                    $quote->setIsReadyToShip(1);
                    $quote->setWholesalePayOption($_checkoutHelper::PAY_AS_SHIP);
                } else {
                    $quote->setIsReadyToShip(0);
                    $quote->setWholesalePayOption($_checkoutHelper::PAY_NOW);
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
        $_checkoutHelper = Mage::helper('allure_multicheckout');

        $quoteOrdered = $this->getQuoteOrdered();
        if ($quoteOrdered) {
            $quoteOrdered->getPayment()->setId(null);
            $quoteOrdered->setWholesalePayOption($_checkoutHelper::PAY_NOW);
            $quoteOrdered->setIsReadyToShip(0);

            if ($quoteOrdered->isVirtual()) {
                $quoteOrdered->getBillingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
            } else {
                $quoteOrdered->getShippingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
            }

            // shipping totals may be affected by payment method
            if (! $quoteOrdered->isVirtual() && $quoteOrdered->getShippingAddress()) {
                $quoteOrdered->getShippingAddress()->setCollectShippingRates(true);
            }
            $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT |
                Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY |
                Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY |
                Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX |
                Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;

            $payment = $quoteOrdered->getPayment();
            $payment->importData($data);
            $quoteOrdered->save();
        }

        $quoteBackOrdered = $this->getQuoteBackordered();

        if ($quoteBackOrdered) {
            $quoteBackOrdered->getPayment()->setId(null);
            $quoteBackOrdered->setWholesalePayOption($_checkoutHelper::PAY_NOW);
            $quoteBackOrdered->setIsReadyToShip(0);

            if (isset($data['wholesale_pay_option']) && ! empty($data['wholesale_pay_option'])) {
                if ($_checkoutHelper::PAY_AS_SHIP == $data['wholesale_pay_option']) {
                    $quoteBackOrdered->setWholesalePayOption($_checkoutHelper::PAY_AS_SHIP);
                    $quoteBackOrdered->setIsReadyToShip(1);
                }
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
    
    private function divideQuoteAsOrderAndBackorder($data)
    {
        $this->changeCustomQuoteStatus();
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        $quoteItemStatus = $_checkoutHelper->getQuoteItemStockStatus();
        $quoteMain = $this->getQuote();
        //$isBackorder = $_checkoutHelper->isQuoteContainsBackorderProduct();
        $isBackorder = isset($quoteItemStatus['backorder']) ? $quoteItemStatus['backorder'] : 0;
        if($isBackorder){
            $quoteItems = $quoteMain->getAllItems();
            $backorderQuote = Mage::getModel('sales/quote')->load($quoteMain->getId());
            $orderQuote = Mage::getModel('sales/quote')->load($quoteMain->getId());
            $backorderQuote->setId(null)->save();
            $orderQuote->setId(null)->save();
            $count = 1;
            foreach ($quoteItems as $item) {
                if(!$item->getParentItemId())
                    continue;
                $product = $item->getProduct();
                $stockItem = $product->getStockItem();
                $parentItem = $item->getParentItem();
                $stockQty = $stockItem->getQty();
                $itemQty = $item->getQty();
                if($parentItem){
                    $itemQty = $parentItem->getQty();
                }
                Mage::log("cnt = ".$count." product_id = ".$product->getId()." stock_item = ".$stockItem->getQty()." manage_qty = ".$stockItem->getManageStock()." item_qty = ".$item->getQty(),Zend_Log::DEBUG,'abc.log',true);
                Mage::log($itemQty,Zend_Log::DEBUG,'abc.log',true);
                $count++;
                
                /* $quoteItem = Mage::getModel('sales/quote_item')
                    ->setProduct($product);
                $quoteItem->setStoreId(1);
                $quoteItem->setQty($itemQty); */
                
                $quoteItem = $item->__clone();
                
                $buyRequest = $item->getBuyRequest();
                
                $quoteParentItem = null;
                if($parentItem){
                    $quoteParentItem = $parentItem->__clone();
                    /* $quoteParentItem = Mage::getModel('sales/quote_item')
                    ->setProduct($parentItem->getProduct());
                    $quoteParentItem->setStoreId(1);
                    $quoteParentItem->setQty($parentItem->getQty()); */
                    //$product = $parentItem->getProduct();
                    $buyRequest = $parentItem->getBuyRequest();
                }
                
                if ($stockQty < $itemQty && 
                    $stockItem->getManageStock() == 1){
                    if($stockQty > 0){
                        $availableQty = $stockQty;
                        $unAvailableQty = $itemQty - $availableQty;
                        
                        if($quoteParentItem){
                        	$quoteParentItem->setQty($availableQty);
                            $quoteParentItem->setQuote($orderQuote)->save();
                            //$orderQuote->addItem($quoteParentItem)->save();
                            $quoteItem->setQty($availableQty);
                            $quoteItem->setParentItemId($quoteParentItem->getId());
                            $quoteItem->setQuote($orderQuote)->save();
                            
                        }else{
                            /* $quoteItem->setQty($availableQty);
                            $orderQuote->addItem($quoteItem)->save(); */
                            $orderQuote->addItem($quoteItem)->save();
                        }
                        
                        
                        //$buyRequest->setQty($availableQty);
                        //$orderQuote->addProduct($product, $buyRequest);
                        if($unAvailableQty){
                        	$quoteParentItem = $parentItem->__clone();
                        	$quoteItem = $item->__clone();
                            if($quoteParentItem){
                            	$quoteParentItem->setQty($unAvailableQty);
                                $quoteParentItem->setQuote($backorderQuote)->save();
                                //$backorderQuote->addItem($quoteParentItem)->save();
                                //$quoteItem->setQty($unAvailableQty);
                                $quoteItem->setQty($unAvailableQty);
                                $quoteItem->setParentItemId($quoteParentItem->getId());
                                $quoteItem->setQuote($backorderQuote)->save();
                                
                            }else{
                                /* $quoteItem->setQty($unAvailableQty);
                                $backorderQuote->addItem($quoteItem)->save(); */
                                $backorderQuote->addItem($quoteItem)->save();
                            }
                            //$quoteItem->setQty($unAvailableQty);
                            //$backorderQuote->addItem($quoteItem)->save();
                            //$buyRequest->setQty($unAvailableQty);
                            //$backorderQuote->addProduct($product, $buyRequest);
                        }
                    }else{
                        if($quoteParentItem){
                            $quoteParentItem->setQuote($backorderQuote)->save();
                            //$backorderQuote->addItem($quoteParentItem)->save();
                            $quoteItem->setParentItemId($quoteParentItem->getId());
                            $quoteItem->setQuote($backorderQuote)->save();
                            
                        }else{
                            //$backorderQuote->addItem($quoteItem)->save();
                            $backorderQuote->addItem($quoteItem)->save();
                        }
                        //$backorderQuote->addItem($quoteItem)->save();
                        //$backorderQuote->addProduct($product, $buyRequest);
                        
                    }
                }else{
                    if($quoteParentItem){
                        $quoteParentItem->setQuote($orderQuote)->save();
                        //$orderQuote->addItem($quoteParentItem)->save();
                        $quoteItem->setParentItemId($quoteParentItem->getId());
                        $quoteItem->setQuote($orderQuote)->save();
                        
                    }else{
                        //$orderQuote->addItem($quoteItem)->save();
                        $orderQuote->addItem($quoteItem)->save();
                    }
                    //$orderQuote->addItem($quoteItem)->save();
                    //$orderQuote->addProduct($product, $buyRequest);
                    
                }
                $product = null;
                $stockItem = null;
                $buyRequest = null;
                $quoteItem = null;
                $quoteParentItem = null;
            }
                
            /** main quote billing & shipping address */
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $billingCustomerId = $quoteMain->getBillingAddress()->getCustomerAddressId();
            $shippingCustomerId = $quoteMain->getShippingAddress()->getCustomerAddressId();
            $billingAddress = $customer->getAddressById($billingCustomerId);
            $shippingAddress = $customer->getAddressById($shippingCustomerId);
                
            /** unvailable quote items */
            $unavailableItems = $backorderQuote->getAllItems();
            if (count($unavailableItems) > 0) {
                $backorderQuote->getBillingAddress()->importCustomerAddress($billingAddress);
                $backorderQuote->getShippingAddress()->importCustomerAddress($shippingAddress);

                $backorderQuote->setOrderType($_checkoutHelper::MULTI_BACK_ORDER);
                $backorderQuote->setIsChildOrder(1);
                $backorderQuote->getShippingAddress()->setCollectShippingRates(true);
                $backorderQuote->collectTotals()->save();

                $this->getCheckoutBackordered()->setBackorder($backorderQuote->getId());
                $this->getCheckoutBackordered()->setQuoteId($backorderQuote->getId());

                $this->getQuoteBackordered()
                    ->getShippingAddress()
                    ->setCollectShippingRates(true);
                $this->getQuoteBackordered()
                    ->collectTotals()
                    ->save();
            }

            /** available quote items */
            $availableItems = $orderQuote->getAllItems();
            if (count($availableItems) > 0) {
                $orderQuote->getBillingAddress()->importCustomerAddress($billingAddress);
                $orderQuote->getShippingAddress()->importCustomerAddress($shippingAddress);

                $orderQuote->setOrderType($_checkoutHelper::MULTI_MAIN_ORDER);
                $orderQuote->setIsChildOrder(1);
                $orderQuote->getShippingAddress()->setCollectShippingRates(true);
                $orderQuote->collectTotals()->save();

                $this->getCheckoutOrdered()->setOrdered($orderQuote->getId());
                $this->getCheckoutOrdered()->setQuoteId($orderQuote->getId());

                $this->getQuoteOrdered()
                    ->getShippingAddress()
                    ->setCollectShippingRates(true);
                $this->getQuoteOrdered()
                    ->collectTotals()
                    ->save();
            }
            
            if (! (count($availableItems) > 0 && count($unavailableItems) > 0)) {
                $this->getQuote()->setOrderType($_checkoutHelper::SINGLE_ORDER);
                $this->getQuote()
                ->setDeliveryMethod($_checkoutHelper::ONE_SHIP)
                ->save();
            }
            $this->getQuote()
            ->getShippingAddress()->setCollectShippingRates(true);
            $this->getQuote()->collectTotals()
            ->save();
            
        }else{
            $this->getQuote()->setOrderType($_checkoutHelper::SINGLE_ORDER);
            $this->getQuote()
                ->setDeliveryMethod($_checkoutHelper::ONE_SHIP)
                ->save();
        }
    }

    private function divideQuoteAsOrderAndBackorder1 ($data)
    {
        // Mage::log(json_encode($this->getQuote()->getShippingAddress()->getData()),Zend_log::DEBUG,'abc',true);
        /* Mage::log($data['shipping_method'],Zend_log::DEBUG,'abc',true); */
        //$isBackorder = $this->isQuoteContainsBackorder();
        $quoteMain = $this->getQuote(); // this is main quote object.
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        $this->changeCustomQuoteStatus();
        $isBackorder = $_checkoutHelper->isQuoteContainsBackorderProduct();
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


                //Commenting to add to backorder
                /*  $productInvryCount = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProduct())
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
                 } */

                //Added in parent child

                $storeId=Mage::app()->getStore()->getStoreId();
                $_product = Mage::getModel('catalog/product')->setStoreId($storeId)->loadByAttribute('sku',$item->getSku());
                $productInvryCount = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);

                $product = Mage::getModel("catalog/product")
                    ->load($item->getProduct()->getId());

                $stock_qty = intval($productInvryCount->getQty());
                if ($stock_qty < $item->getQty() && $productInvryCount->getManageStock()==1){
                    //if( $productInvryCount <= 0 ){
                    //$backorder_quote->addItem($item->setId(null));

                    //if($item->getProductType() !='configurable'){



                    /*split item  if minimum 1 qty available of Back ordered item
                     * jira number MT-906
                     * start-------------------------------------MT-906
                     * */
                    if($stock_qty>0)
                    {

                        $instockqty=$stock_qty;
                        $outstockqty=$item->getQty()-$stock_qty;

                        $inStockBuyReqArray=$item->getBuyRequest();
                        $inStockBuyReqArray->setQty($instockqty);
                        $order_quote->addProduct($product, $inStockBuyReqArray);


                        $outStockBuyReqArray=$item->getBuyRequest();
                        $outStockBuyReqArray->setQty($outstockqty);
                        $backorder_quote->addProduct($product, $outStockBuyReqArray);


                    }
                    /*end------------------------MT-906*/

                     else {
                    $backorder_quote->addProduct($product, $item->getBuyRequest());
                    }



                    //}
                }else{
                    //$order_quote->addItem($item->setId(null));
                    //if($item->getProductType() !='configurable'){
                    $order_quote->addProduct($product, $item->getBuyRequest());
                    //}
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
            $this->getQuote()
                ->getShippingAddress()->setCollectShippingRates(true);
            $this->getQuote()->collectTotals()
                ->save();
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
        $storeId=Mage::app()->getStore()->getStoreId();
        foreach ($qouteItems as $item) :
            $_product = Mage::getModel('catalog/product')->setStoreId($storeId)->loadByAttribute('sku',$item->getProduct()->getSku());
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
            if(!is_null($stock->getItemId())):
                $stock_qty= $stock->getQty();
                /* $stock_qty = intval($item->getProduct()
                    ->getStockItem()
                    ->getQty()); */
                if ($stock_qty < $item->getQty() && $stock->getManageStock()==1) :
                    $isBackorderAvailable = true;
                    break;
                endif;
            endif;

            /*
             * if($productInventoryQty<=0):
             * $isBackorderAvailable = true;
             * break;
             * endif;
             */
        endforeach ;

        return $isBackorderAvailable;
    }

    public function changeCustomQuoteStatus ()
    {
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        $orederdQuoteId = $this->getCheckoutOrdered()->getOrdered();
        $backOrderdQuoteId = $this->getCheckoutBackordered()->getBackorder();
        if (isset($orederdQuoteId) && ! empty($orederdQuoteId)) {
            if ($this->getQuoteOrdered()->getId() != 0 && $this->getQuoteOrdered()->getId() != $this->getQuote()->getId()) {
                $this->getQuoteOrdered()
                    ->setIsActive(false)
                    ->save();
                if($this->getQuoteOrdered()->getId()){
                    //$this->getQuoteOrdered()->delete();
                }
            }
            $this->getCheckoutOrdered()->setOrdered(null);
        }

        if (isset($backOrderdQuoteId) && ! empty($backOrderdQuoteId)) {
            if ($this->getQuoteBackordered()->getId() != 0 &&
                $this->getQuoteBackordered()->getId() != $this->getQuote()->getId()) {
                $this->getQuoteBackordered()
                    ->setIsActive(false)
                    ->save();
                if($this->getQuoteBackordered()->getId()){
                    //$this->getQuoteBackordered()->delete();
                }
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
                            Mage::log(get_class($firstOrder),Zend_log::DEBUG,'abc',true);

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
    
    public function saveGiftItem($giftItems = null){
        try{
            $newGiftItemArray = array();
            $giftWrapQty = 0;
            if(isset($giftItems) && !empty($giftItems)){
                foreach($giftItems as $data){
                    foreach ($data as $itemId => $item){
                        if(isset($item["is_gift_item"]) && $item["is_gift_item"]){
                            $newGiftItemArray[$itemId]["is_gift_item"] = $item["is_gift_item"];
                            $newGiftItemArray[$itemId]["gift_item_qty"] = $newGiftItemArray[$itemId]["gift_item_qty"] + 1;
                            if(isset($item["is_gift_wrap"]) && $item["is_gift_wrap"]){
                                $newGiftItemArray[$itemId]["is_gift_wrap"] = $item["is_gift_wrap"];
                                $newGiftItemArray[$itemId]["gift_wrap_qty"] = $newGiftItemArray[$itemId]["gift_wrap_qty"] + 1;
                                $giftWrapQty++;
                            }
                        }
                    }
                }
            }
            
            $quote =  $this->getQuote();
            
            foreach ($quote->getAllVisibleItems() as $quoteItem){
                if($quoteItem){
                    $quoteItem->setIsGiftItem(0);
                    $quoteItem->setGiftItemQty(0);
                    $quoteItem->setIsGiftWrap(0);
                    $quoteItem->setGiftWrapQty(0);
                    $itemId = $quoteItem->getId();
                    if(isset($newGiftItemArray[$itemId]["is_gift_item"]) && $newGiftItemArray[$itemId]["is_gift_item"]){
                        $quoteItem->setIsGiftItem($newGiftItemArray[$itemId]["is_gift_item"]);
                        $quoteItem->setGiftItemQty($newGiftItemArray[$itemId]["gift_item_qty"]);
                        if(isset($newGiftItemArray[$itemId]["is_gift_wrap"]) && $newGiftItemArray[$itemId]["is_gift_wrap"]){
                            $quoteItem->setIsGiftWrap($newGiftItemArray[$itemId]["is_gift_wrap"]);
                            $quoteItem->setGiftWrapQty($newGiftItemArray[$itemId]["gift_wrap_qty"]);
                        }
                    }
                    $quoteItem->save();
                }
            }
            
            /* foreach ($newGiftItemArray as $itemId){
                $quoteItem = $quote->getItemById($itemId);
                if($quoteItem){
                    if(isset($newGiftItemArray[$itemId]["is_gift_item"]) && $newGiftItemArray[$itemId]["is_gift_item"]){
                        $quoteItem->setIsGiftItem($newGiftItemArray[$itemId]["is_gift_item"]);
                        $quoteItem->setGiftItemQty($newGiftItemArray[$itemId]["gift_item_qty"]);
                        if(isset($newGiftItemArray[$itemId]["is_gift_wrap"]) && $newGiftItemArray[$itemId]["is_gift_wrap"]){
                            $quoteItem->setIsGiftWrap($newGiftItemArray[$itemId]["is_gift_wrap"]);
                            $quoteItem->setGiftWrapQty($newGiftItemArray[$itemId]["gift_wrap_qty"]);
                        }
                        $quoteItem->save();
                    }
                }
            } */
            $this->addGiftWrap($giftWrapQty);
            Mage::log($newGiftItemArray,Zend_Log::DEBUG,'abc.log',true);
        }catch (Exception $e){
            Mage::log($e->getMessage(),Zend_Log::DEBUG,'abc.log',true);
        }
    }
    
    public function addGiftWrap($giftWrapQty = 0){
        $helper = Mage::helper("allure_redesigncheckout");
        $quote =  $this->getQuote();
        $giftItem = null;
        foreach ($quote->getAllVisibleItems() as $_item){
            if($_item->getSku() == $helper::GIFT_WRAP_SKU){
                $giftItem = $_item;
                break;
            }
        }
        if($giftWrapQty){
            $_product = $helper->getGiftWrap();
            if(!$giftItem){
                if($_product){
                    $cart = Mage::getSingleton('checkout/cart');
                    $cart->init();
                    $cart->addProduct($_product, $giftWrapQty);
                    $cart->save();
                }
            }else{
                $giftItem->setQty($giftWrapQty);
            }
        }else{
            if($giftItem){
                $giftItem->delete();
            }
        }
        $quote->save();
        $this->getCheckout()->clear();
        $this->getCheckout()->setQuoteId($quote->getId());
    }
}
