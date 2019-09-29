<?php
/**
 * 
 * @author allure
 *
 */
class Allure_RedesignCheckout_Model_Checkout_Type_Multishipping extends Mage_Checkout_Model_Type_Multishipping
{
    const XML_MULTI_ADDRESS_ORDER_EMAIL_ALLOW = 'sales_email/allure_multiaddress_sales_email/multi_order_allow_email';
    
    /**
     * Assign quote items to addresses and specify items qty
     *
     * array structure:
     * array(
     *      $quoteItemId => array(
     *          'qty'       => $qty,
     *          'address'   => $customerAddressId
     *      )
     * )
     *
     * @param array $info
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    public function setShippingItemsInformation($info)
    {
        Mage::log($info,Zend_Log::DEBUG,"abc.log",true);
        if (is_array($info)) {
            $allQty = 0;
            $itemsInfo = array();
            foreach ($info as $itemData) {
                foreach ($itemData as $quoteItemId => $data) {
                    $allQty += $data['qty'];
                    $itemsInfo[$quoteItemId] = $data;
                }
            }
            
            $maxQty = (int)Mage::getStoreConfig('shipping/option/checkout_multiple_maximum_qty');
            if ($allQty > $maxQty) {
                Mage::throwException(Mage::helper('checkout')->__('Maximum qty allowed for Shipping to multiple addresses is %s', $maxQty));
            }
            $quote = $this->getQuote();
            $addresses  = $quote->getAllShippingAddresses();
            foreach ($addresses as $address) {
                $quote->removeAddress($address->getId());
            }
            
            $giftQty = array();
            foreach ($info as $itemData){
                foreach ($itemData as $quoteItemId => $data) {
                    $qty = isset($giftQty[$quoteItemId][$data["address"]]) ? 0 : $giftQty[$quoteItemId][$data["address"]] ;
                    if(isset($data["is_gift_item"])){
                        if($data["is_gift_item"]){
                            $giftQty[$quoteItemId][$data["address"]] = $giftQty[$quoteItemId][$data["address"]] + 1;
                        }
                    }
                }
            }
            
            foreach ($info as $itemData) {
                foreach ($itemData as $quoteItemId => $data) {
                    $data["gift_qty"] = 0;
                    if( isset($giftQty[$quoteItemId][$data["address"]]) ){
                        $data["gift_qty"] = $giftQty[$quoteItemId][$data["address"]];
                    }
                    $this->_addShippingItem($quoteItemId, $data);
                }
            }
            
            /**
             * Delete all not virtual quote items which are not added to shipping address
             * MultishippingQty should be defined for each quote item when it processed with _addShippingItem
             */
            foreach ($quote->getAllItems() as $_item) {
                if (!$_item->getProduct()->getIsVirtual() &&
                    !$_item->getParentItem() &&
                    !$_item->getMultishippingQty()
                    ) {
                        $quote->removeItem($_item->getId());
                    }
            }
            
            if ($billingAddress = $quote->getBillingAddress()) {
                $quote->removeAddress($billingAddress->getId());
            }
            
            if ($customerDefaultBilling = $this->getCustomerDefaultBillingAddress()) {
                $quote->getBillingAddress()->importCustomerAddress($customerDefaultBilling);
            }
            
            foreach ($quote->getAllItems() as $_item) {
                if (!$_item->getProduct()->getIsVirtual()) {
                    continue;
                }
                
                if (isset($itemsInfo[$_item->getId()]['qty'])) {
                    if ($qty = (int)$itemsInfo[$_item->getId()]['qty']) {
                        $_item->setQty($qty);
                        
                        $_item->setIsGiftItem(0);
                        $_item->setIsGiftWrap(0);
                        if(isset($itemsInfo[$_item->getId()]["is_gift_item"])){
                            if($itemsInfo[$_item->getId()]["is_gift_item"]){
                                $_item->setIsGiftItem(1);
                                if( isset($giftQty[$_item->getId()][$itemsInfo[$_item->getId()]["address"]]) ){
                                    $_item->setGiftItemQty($giftQty[$_item->getId()][$itemsInfo[$_item->getId()]["address"]]);
                                }
                                
                                if(isset($itemsInfo[$_item->getId()]["is_gift_wrap"])){
                                    if($itemsInfo[$_item->getId()]["is_gift_wrap"]){
                                        $_item->setIsGiftWrap(1);
                                    }
                                }
                            }
                        }
                        
                        $quote->getBillingAddress()->addItem($_item);
                    } else {
                        $_item->setQty(0);
                        $quote->removeItem($_item->getId());
                    }
                }
                
            }
            
            //set billing address
            $params = Mage::app()->getRequest()->getParams();
            if(isset($params["billing_address_id"])){
                Mage::getModel('checkout/type_multishipping')
                ->setQuoteCustomerBillingAddress($params["billing_address_id"]);
            }
            
            $this->save();
            Mage::dispatchEvent('checkout_type_multishipping_set_shipping_items', array('quote'=>$quote));
        }
        return $this;
    }
    
    
    /**
     * Add quote item to specific shipping address based on customer address id
     *
     * @param int $quoteItemId
     * @param array $data array('qty'=>$qty, 'address'=>$customerAddressId)
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    protected function _addShippingItem($quoteItemId, $data)
    {
        $qty       = isset($data['qty']) ? (int) $data['qty'] : 1;
        //$qty       = $qty > 0 ? $qty : 1;
        $addressId = isset($data['address']) ? $data['address'] : false;
        $quoteItem = $this->getQuote()->getItemById($quoteItemId);
        
        if ($addressId && $quoteItem) {
            /**
             * Skip item processing if qty 0
             */
            if ($qty === 0) {
                return $this;
            }
            $quoteItem->setMultishippingQty((int)$quoteItem->getMultishippingQty()+$qty);
            $quoteItem->setQty($quoteItem->getMultishippingQty());
            $address = $this->getCustomer()->getAddressById($addressId);
            if ($address->getId()) {
                if (!$quoteAddress = $this->getQuote()->getShippingAddressByCustomerAddressId($address->getId())) {
                    $quoteAddress = Mage::getModel('sales/quote_address')->importCustomerAddress($address);
                    $this->getQuote()->addShippingAddress($quoteAddress);
                    if ($couponCode = $this->getCheckoutSession()->getCartCouponCode()) {
                        $this->getQuote()->setCouponCode($couponCode);
                    }
                }
                
                $quoteAddress = $this->getQuote()->getShippingAddressByCustomerAddressId($address->getId());
                if ($quoteAddressItem = $quoteAddress->getItemByQuoteItemId($quoteItemId)) {
                    $quoteAddressItem->setQty((int)($quoteAddressItem->getQty()+$qty));
                } else {
                    $quoteItem->setIsGiftItem(0);
                    $quoteItem->setIsGiftWrap(0);
                    $quoteItem->setGiftItemQty(0);
                    if(isset($data["is_gift_item"])){
                        if($data["is_gift_item"]){
                            $quoteItem->setIsGiftItem(1);
                            if(isset($data["gift_qty"])){
                                $quoteItem->setGiftItemQty($data["gift_qty"]);
                            }
                            
                            if(isset($data["is_gift_wrap"])){
                                if($data["is_gift_wrap"]){
                                    $quoteItem->setIsGiftWrap(1);
                                }
                            }
                        }
                    }
                    
                    $quoteAddress->addItem($quoteItem, $qty);
                }
                /**
                 * Require shiping rate recollect
                 */
                Mage::log((boolean) $this->getCollectRatesFlag(),Zend_Log::DEBUG,'abc.log',true);
                $quoteAddress->setCollectShippingRates(1);
            }
        }
        return $this;
    }
    
    public function changeShippingAddress($data)
    {
        $customerAddrId = $data["customer_address"];
        $addressId = $data["address_id"];
        $addresses = $this->getQuote()->getAllShippingAddresses();
        $requestParams = array();
        $index = 0;
        foreach ($addresses as $address) {
            foreach ($address->getAllVisibleItems() as $item){
                $requestParams[$index] = array(
                    $item->getQuoteItemId() => array(
                        "qty" => $item->getQty(),
                        "is_gift_item" => $item->getIsGiftItem(),
                        "address" => ($addressId == $address->getId()) ? $customerAddrId : $address->getCustomerAddressId()
                    )
                ); 
                $index++;
            }
        }
        $this->setShippingItemsInformation($requestParams);
    }
    
    /**
     * Assign shipping methods to addresses
     *
     * @param  array $methods
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    public function setShippingMethodsAgain($methods)
    {
        Mage::log($methods,Zend_log::DEBUG,'abc.log',true);
        $addresses = $this->getQuote()->getAllShippingAddresses();
        foreach ($addresses as $address) {
            Mage::log($methods[$address->getCustomerAddressId()],Zend_log::DEBUG,'abc.log',true);
            if (isset($methods[$address->getCustomerAddressId()])) {
                $address->setShippingMethod($methods[$address->getCustomerAddressId()]);
            } elseif (!$address->getShippingMethod()) {
                Mage::throwException(Mage::helper('checkout')->__('Please select shipping methods for all addresses ssss'));
            }
        }
        $this->save();
        return $this;
    }
    
    public function removeBackOrderAddresses(){
        try{
            $addresses = $this->getQuote()->getAllShippingAddresses();
            foreach ($addresses as $address) {
                if($address->getIsContainBackorder()){
                    $address->delete();
                    continue;
                }
            }
        }catch (Exception $e){
            Mage::log("removeBackOrderAddresses function - Exception",Zend_log::DEBUG,'abc.log',true);
            Mage::log($e->getMessage(),Zend_log::DEBUG,'abc.log',true);
        }
    }
    
    public function removeIsbackOrderedAddress(){
        try{
            $index = 0;
            $requestParams = array();
            $shippingMethodArray = array();
            $addresses = $this->getQuote()->getAllShippingAddresses();
            foreach ($addresses as $address) {
                if($address->getShippingMethod()){
                    $shippingMethodArray[$address->getCustomerAddressId()] = $address->getShippingMethod();
                }
                foreach ($address->getAllVisibleItems() as $item){
                    $requestParams[$index] = array(
                        $item->getQuoteItemId() => array(
                            "qty" => $item->getQty(),
                            "is_gift_item" => $item->getIsGiftItem(),
                            "address" => $address->getCustomerAddressId()
                        )
                    );
                    $index++;
                }
                if($address->getIsContainBackorder()){
                    $address->delete();
                    continue;
                }
            }
            $this->setShippingItemsInformation($requestParams);
            Mage::log($shippingMethodArray,Zend_log::DEBUG,'abc.log',true);
            if(count($shippingMethodArray) > 0){
                $this->setShippingMethodsAgain($shippingMethodArray);
            }
            $quote = $this->getQuote();
            $quote->setTotalsCollectedFlag(false)
            ->collectTotals();
            $quote->save();
        }catch (Exception $e){
            Mage::log("removeIsbackOrderedAddress function - Exception",Zend_log::DEBUG,'abc.log',true);
            Mage::log($e->getMessage(),Zend_log::DEBUG,'abc.log',true);
        }
    }
    
    /**
     * Assign backorder 1|0 to addresses
     *
     * @param  array $methods
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    public function setDeliveryOptions($methods)
    {
        $addresses = $this->getQuote()->getAllShippingAddresses();
        foreach ($addresses as $address) {
            $this->spliteAddressOrder($address, $methods);
        }
        $this->save();
        return $this;
    }
    
    private function spliteAddressOrder($address, $methods){
        try{
            Mage::log("delivery option",Zend_Log::DEBUG,'abc.log',true);
            Mage::log($methods,Zend_Log::DEBUG,'abc.log',true);
            $isAllowBackorder = (isset($methods[$address->getId()])) ? ($methods[$address->getId()]) ? 1 : 0 : 0;
            if ($isAllowBackorder) {
                $helper = Mage::helper("allure_redesigncheckout");
                if($helper->isAddressContainBackOrderItem($address)){
                    /** @var Mage_Sales_Model_Quote_Address $backOrderAddress */
                    $backOrderAddress = clone $address;
                    $backOrderAddress->setId(null);
                    $backOrderAddress->setIsContainBackorder(1);
                    $backOrderAddress->save();
                    $storeId = Mage::app()->getStore()->getStoreId();
                    foreach($address->getAllItems() as $item){
                        if ($item->getParentItemId()) {
                            continue;
                        }
                        $_product = Mage::getModel('catalog/product')
                        ->setStoreId($storeId)
                        ->loadByAttribute('sku', $item->getSku());
                        $stock = Mage::getModel('cataloginventory/stock_item')
                        ->loadByProduct($_product);
                        $stockQty = $stock->getQty();
                        if ($stockQty < $item->getQty() && $stock->getManageStock() == 1) {
                            $backItem = clone $item;
                            $backItem->setId(null);
                            $backOrderAddress->addItem($backItem, $item->getQty());
                            $address->removeItem($item->getId());
                        }
                    }
                    Mage::log("shipping address - ".$address->getShippingMethod(),Zend_Log::DEBUG,'abc.log',true);
                    $backOrderAddress->setCollectShippingRates(1);
                    $backOrderAddress->setShippingMethod($address->getShippingMethod());
                    $backOrderAddress->save();
                    $address->save();
                    /* $backOrderAddress->collectTotals();
                    $backOrderAddress->save();
                    
                    $address->collectTotals();
                    $address->save(); */
                }
            } 
        }catch (Exception $e){
            Mage::log($e->getMessage(),Zend_log::DEBUG,'abc.log',true);
        }
    }
    
    /**
     * Create orders per each quote address
     *
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    public function createOrders()
    {
        $orderIds = array();
        $this->_validate();
        $shippingAddresses = $this->getQuote()->getAllShippingAddresses();
        $orders = array();
        
        if ($this->getQuote()->hasVirtualItems()) {
            $shippingAddresses[] = $this->getQuote()->getBillingAddress();
        }
        
        try {
            
            $_index = 1;
            $quote = $this->getQuote();
            $quote->unsReservedOrderId();
            $quote->reserveOrderId();
            $incrementId = $quote->getReservedOrderId();
            foreach ($shippingAddresses as $address) {
                
                $backOrderPrefix =  "";
                if($address->getIsContainBackorder()){
                    $backOrderPrefix = "B";
                }
                
                $newIncrementId = $incrementId."-".$_index . $backOrderPrefix;
                $order = $this->_prepareOrder($address, $newIncrementId);
                $_index++;
                
                $orders[] = $order;
                Mage::dispatchEvent(
                    'checkout_type_multishipping_create_orders_single',
                    array('order'=>$order, 'address'=>$address)
                    );
            }
            
            $storeId = $this->getQuote()->getStoreId();
            $isAllowCombinedEmail = Mage::getStoreConfig(self::XML_MULTI_ADDRESS_ORDER_EMAIL_ALLOW, $storeId);
            
            foreach ($orders as $order) {
                $order->place();
                $order->save();
                if ($order->getCanSendNewEmailFlag()){
                    if(!$isAllowCombinedEmail){
                        //$order->queueNewOrderEmail();
                        $orderArray = array($order->getId() => $order);
                        $order->queueMultiAddressNewOrderEmail($orderArray);
                    }
                }
                $orderIds[$order->getId()] = $order->getIncrementId();
            }
            
            if($isAllowCombinedEmail){
                $order->queueMultiAddressNewOrderEmail($orders);
            }
            
            Mage::getSingleton('core/session')->setOrderIds($orderIds);
            Mage::getSingleton('checkout/session')->setLastQuoteId($this->getQuote()->getId());
            
            $this->getQuote()
            ->setIsActive(false)
            ->save();
            
            Mage::dispatchEvent('checkout_submit_all_after', array('orders' => $orders, 'quote' => $this->getQuote()));
            
            return $this;
        } catch (Exception $e) {
            Mage::dispatchEvent('checkout_multishipping_refund_all', array('orders' => $orders));
            throw $e;
        }
    }
    
    /**
     * Prepare order based on quote address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Order
     * @throws  Mage_Checkout_Exception
     */
    protected function _prepareOrder(Mage_Sales_Model_Quote_Address $address, $newIncrementId)
    {
        $quote = $this->getQuote();
        /* $quote->unsReservedOrderId();
        $quote->reserveOrderId(); */
        
        // set custom increment id to order
        $quote->setReservedOrderId($newIncrementId);
        
        $quote->collectTotals();
        
        $convertQuote = Mage::getSingleton('sales/convert_quote');
        $order = $convertQuote->addressToOrder($address);
        $order->setQuote($quote);
        $order->setBillingAddress(
            $convertQuote->addressToOrderAddress($quote->getBillingAddress())
            );
        
        if ($address->getAddressType() == 'billing') {
            $order->setIsVirtual(1);
        } else {
            $order->setShippingAddress($convertQuote->addressToOrderAddress($address));
        }
        
        $order->setPayment($convertQuote->paymentToOrderPayment($quote->getPayment()));
        if (Mage::app()->getStore()->roundPrice($address->getGrandTotal()) == 0) {
            $order->getPayment()->setMethod('free');
        }
        
        foreach ($address->getAllItems() as $item) {
            $_quoteItem = $item->getQuoteItem();
            if (!$_quoteItem) {
                throw new Mage_Checkout_Exception(Mage::helper('checkout')->__('Item not found or already ordered'));
            }
            $item->setProductType($_quoteItem->getProductType())
            ->setProductOptions(
                $_quoteItem->getProduct()->getTypeInstance(true)->getOrderOptions($_quoteItem->getProduct())
                );
            $orderItem = $convertQuote->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $order->addItem($orderItem);
        }
        
        return $order;
    }
}
