<?php
/**
 * 
 * @author allure
 *
 */
class Allure_RedesignCheckout_Model_Checkout_Type_Multishipping extends Mage_Checkout_Model_Type_Multishipping
{
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
                $quoteAddress->setCollectShippingRates((boolean) $this->getCollectRatesFlag());
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
            }
            $index++;
        }
        $this->setShippingItemsInformation($requestParams);
        /* $address = $this->getCustomer()->getAddressById($customerAddrId);
        if ($address->getId()) {
            $quoteAddress = Mage::getModel('sales/quote_address')->load($addressId);
            if($quoteAddress->getId()){
                $quoteAddress->importCustomerAddress($address);
                $quoteAddress->save();
                $this->save();
            }
        }
        Mage::dispatchEvent('checkout_type_multishipping_set_shipping_items', array('quote'=>$this->getQuote())); */
    }
}
