<?php
/**
 * 
 * @author allure
 *
 */
class Allure_RedesignCheckout_Model_Checkout_Type_Multishipping extends Mage_Checkout_Model_Type_Multishipping
{
    const XML_MULTI_ADDRESS_ORDER_EMAIL_ALLOW = 'sales_email/allure_multiaddress_sales_email/multi_order_allow_email';
    
    public function _init(){
        //$couponCode = $this->getCheckoutSession()->getCartCouponCode();
        parent::_init();
        /* if ($couponCode) {
            $this->getQuote()->setCouponCode($couponCode)->collectTotals()->save();
        } */
    }
    
    /**
     * prepare the the request data and add gift wrap 
     * related information into passed array data.
     * @param array $info
     * @param array $giftQty
     * @param array $giftWrapQtyArr
     */
    private function prepareRequestWithGiftWrap(&$info, &$giftQty, &$giftWrapQtyArr){
        try {
            $helper = Mage::helper("allure_redesigncheckout");
            $giftWrapQty = 0;
            foreach ($info as $itemData){
                foreach ($itemData as $quoteItemId => $data) {
                    if(isset($data["is_gift_item"]) && $data["is_gift_item"] ){
                        $giftQty[$quoteItemId][$data["address"]] = $giftQty[$quoteItemId][$data["address"]] + 1;
                        if(isset($data["is_gift_wrap"]) && $data["is_gift_wrap"] ){
                            $giftWrapQtyArr[$quoteItemId][$data["address"]] = $giftWrapQtyArr[$quoteItemId][$data["address"]] + 1;
                            $giftWrapQty++;
                        }
                    }
                }
            }
            
            $quote = $this->getQuote();
            $giftItem = null;
            foreach ($quote->getAllVisibleItems() as $_item){
                if($_item->getSku() == $helper::GIFT_WRAP_SKU){
                    $giftItem = $_item;
                    break;
                }
            }
            if($giftWrapQty){
                if(!$giftItem){
                    $storeId = Mage::app()->getStore()->getId();
                    $_product = $helper->getGiftWrap();
                    if($_product){
                        /* $_product->setPrice($_product->getPrice());
                        $giftItem = Mage::getModel('sales/quote_item')
                            ->setProduct($_product);
                        $giftItem->setStoreId($storeId)
                            ->setPrice($_product->getPrice())
                            ->setBasePrice($_product->getPrice());
                        $giftItem->setQty($giftWrapQty);
                        $quote->addItem($giftItem); */
                        /* $giftItem = $quote->addProduct($_product, $giftWrapQty);
                        Mage::dispatchEvent('checkout_cart_product_add_after', array('quote_item' => $giftItem, 'product' => $_product));
                        $this->getCheckoutSession()->setLastAddedProductId($_product->getId());
                        Mage::dispatchEvent('checkout_cart_save_after', array('cart'=>Mage::getSingleton('checkout/cart'))); */
                        $quote->setIsMultiShipping(0)->save();
                        $cart = Mage::getSingleton('checkout/cart');
                        $cart->init();
                        $cart->addProduct($_product, $giftWrapQty);
                        $cart->save();
                        $quote->setIsMultiShipping(1)->save();
                        $this->_init();
                    }
                }else{
                    $giftItem->setQty($giftWrapQty);
                }
            }else{
                if($giftItem){
                    $giftItem->delete();
                }
            }
            foreach ($this->getQuote()->getAllVisibleItems() as $_item){
                if($_item->getSku() == $helper::GIFT_WRAP_SKU){
                    $giftItem = $_item;
                    break;
                }
            }
            //$quote->setTotalsCollectedFlag(false);
            //$quote->collectTotals()->save();
            
            $quote->save();
            $this->getCheckoutSession()->clear();
            $this->getCheckoutSession()->setQuoteId($quote->getId());
            
            foreach ($info as $itemData) {
                foreach ($itemData as $quoteItemId => $data) {
                    if (isset($data["is_gift_item"]) && $data["is_gift_item"]) {
                        if (isset($data["is_gift_wrap"]) && $data["is_gift_wrap"]) {
                            $info[] = array(
                                $giftItem->getId() => array(
                                    "qty" => 1,
                                    "address" => $data["address"]
                            ));
                        }
                    }
                }
            }
            $giftItem = null;
        } catch (Exception $e) {
            Mage::log("addGiftWrap method - Exception:",Zend_Log::DEBUG,'abc.log',true);
            Mage::log($e->getMessage(),Zend_Log::DEBUG,'abc.log',true);
        }
    }
    
    /**
     * check the customer address contain
     * out of stock product.
     * if out of stock product found into address then
     * set is_separate_ship is to 1 else 0.
     */
    private function setBackorderFlag(){
        try {
            $addresses = $this->getQuote()->getAllShippingAddresses();
            foreach ($addresses as $address) {
                $isContainBackOrder = false;
                foreach ($address->getAllVisibleItems() as $item){
                    if($item->getIsSeparateShip()){
                        $isContainBackOrder = true;
                        break;
                    }
                }
                $address->setIsContainBackorder(0);
                if($isContainBackOrder){
                    $address->setIsContainBackorder(1);
                }
            }
            $this->save();
        } catch (Exception $e) {
            Mage::log("setBackorderFlag method - Exception:",Zend_Log::DEBUG,'abc.log',true);
            Mage::log($e->getMessage(),Zend_Log::DEBUG,'abc.log',true);
        }
    }
    
    /**
     * according to the is_separate_ship flag
     * generate the separate shipment means
     * create another shipping address.
     */
    private function createSeparetShipment(){
        try {
            $helper = Mage::helper("allure_redesigncheckout");
            $addresses = $this->getQuote()->getAllShippingAddresses();
            foreach ($addresses as $address) {
                if($address->getIsContainBackorder()){
                    if($helper->isAddressContainBackOrderItem($address)){
                        /** @var Mage_Sales_Model_Quote_Address $backOrderAddress */
                        $customerAddress = $this->getCustomer()->getAddressById($address->getCustomerAddressId());
                        $quoteAddress = Mage::getModel('sales/quote_address')->importCustomerAddress($customerAddress);
                        $this->getQuote()->addShippingAddress($quoteAddress);
                        
                        $backOrderAddress = Mage::getModel('sales/quote_address')->importCustomerAddress($customerAddress);
                        $this->getQuote()->addShippingAddress($backOrderAddress);
                        $instockGiftWrapQty = 0; $outofstockGiftWrapQty = 0;
                        $giftItem = null;
                        $storeId = Mage::app()->getStore()->getStoreId();
                        foreach($address->getAllItems() as $item){
                            if ($item->getParentItemId()) continue;
                            
                            if($item->getSku() == $helper::GIFT_WRAP_SKU){
                                $giftItem = $item;
                                continue;
                            }
                            
                            $_product = Mage::getModel('catalog/product')
                                ->setStoreId($storeId)
                                ->loadByAttribute('sku', $item->getSku());
                            $stock = Mage::getModel('cataloginventory/stock_item')
                                ->loadByProduct($_product);
                            $stockQty = $stock->getQty();
                            
                            $quoteItem = $this->getQuote()->getItemById($item->getQuoteItemId());
                            $quoteItem->setMultishippingQty((int)$quoteItem->getMultishippingQty());
                            $quoteItem->setQty($quoteItem->getMultishippingQty());
                            if (($stockQty < $item->getQty() && $stock->getManageStock() == 1)) {
                                if($item->getIsGiftWrap()){
                                    $outofstockGiftWrapQty += $item->getGiftWrapQty();
                                }
                                $backOrderAddress->addItem($quoteItem, $item->getQty());
                            }else{
                                if($item->getIsGiftWrap()){
                                    $instockGiftWrapQty += $item->getGiftWrapQty();
                                }
                                $quoteAddress->addItem($quoteItem, $item->getQty());
                            }
                            $_product = null;
                            $stock = null;
                        }
                        
                        if($giftItem){
                            $quoteGiftItem = $this->getQuote()->getItemById($giftItem->getQuoteItemId());
                            $quoteGiftItem->setMultishippingQty((int)$quoteItem->getMultishippingQty());
                            $quoteGiftItem->setQty($quoteGiftItem->getMultishippingQty());
                            if($outofstockGiftWrapQty){
                                $backOrderAddress->addItem($quoteGiftItem, $outofstockGiftWrapQty);
                            }
                            if($instockGiftWrapQty){
                                $quoteAddress->addItem($quoteGiftItem, $instockGiftWrapQty);
                            }
                        }
                        
                        $this->getQuote()->removeAddress($address->getId());
                        $quoteAddress->setCollectShippingRates(1);
                        $backOrderAddress->setCollectShippingRates(1);
                        $backOrderAddress->setIsContainBackorder(1);
                    }
                }
            }
        } catch (Exception $e) {
            Mage::log("createSeparetShipment method - Exception:",Zend_Log::DEBUG,'abc.log',true);
            Mage::log($e->getMessage(),Zend_Log::DEBUG,'abc.log',true);
        }
    }
    
    /**
     * Override the method.
     * @param array $info
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    public function setShippingItemsInformation($info)
    {
        Mage::log($info,Zend_Log::DEBUG,"abc.log",true);
        if (is_array($info)) {
            $giftQty = array(); $giftWrapQtyArr = array();
            $this->prepareRequestWithGiftWrap($info, $giftQty, $giftWrapQtyArr);
            
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
            
            foreach ($info as $itemData) {
                foreach ($itemData as $quoteItemId => $data) {
                    $data["gift_qty"] = 0;
                    $data["gift_wrap_qty"] = 0;
                    if( isset($giftQty[$quoteItemId][$data["address"]]) ){
                        $data["gift_qty"] = $giftQty[$quoteItemId][$data["address"]];
                    }
                    if( isset($giftWrapQtyArr[$quoteItemId][$data["address"]]) ){
                        $data["gift_wrap_qty"] = $giftWrapQtyArr[$quoteItemId][$data["address"]];
                    }
                    $this->_addShippingItem($quoteItemId, $data);
                }
            }
            
            /* $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals()->save(); */
            
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
                        
                        $_item->setIsSeparateShip(0);
                        $_item->setIsGiftItem(0);
                        $_item->setIsGiftWrap(0);
                        if(isset($itemsInfo[$_item->getId()]["is_gift_item"])
                                && $itemsInfo[$_item->getId()]["is_gift_item"] ){
                            $_item->setIsGiftItem(1);
                            if( isset($giftQty[$_item->getId()][$itemsInfo[$_item->getId()]["address"]]) ){
                                $_item->setGiftItemQty($giftQty[$_item->getId()][$itemsInfo[$_item->getId()]["address"]]);
                            }
                            if(isset($itemsInfo[$_item->getId()]["is_gift_wrap"])
                                    && $itemsInfo[$_item->getId()]["is_gift_wrap"]){
                                $_item->setIsGiftWrap(1);
                                if( isset($giftWrapQtyArr[$_item->getId()][$itemsInfo[$_item->getId()]["address"]]) ){
                                    $_item->setGiftWrapQty($giftWrapQtyArr[$_item->getId()][$itemsInfo[$_item->getId()]["address"]]);
                                }
                            }
                        }
                        
                        if(isset($itemsInfo[$_item->getId()]["is_separate_ship"]) 
                                && $itemsInfo[$_item->getId()]["is_separate_ship"]){
                            $_item->setIsSeparateShip(1);
                        }
                        
                        $quote->getBillingAddress()->addItem($_item);
                    } else {
                        $_item->setQty(0);
                        $quote->removeItem($_item->getId());
                    }
                }
            }
            
            /**  set customer billing address. */
            $params = Mage::app()->getRequest()->getParams();
            if(isset($params["billing_address_id"])){
                $this->setQuoteCustomerBillingAddress($params["billing_address_id"]);
            }
            /** set backorder flag to the address. */
            $this->setBackorderFlag();
            /** separate the out of stock items into separate address. */
            $this->createSeparetShipment();
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals()->save();
            Mage::dispatchEvent('checkout_type_multishipping_set_shipping_items', array('quote'=>$quote));
        }
        return $this;
    }
    
    /**
     * Override the method.
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
                    $quoteItem->setGiftWrapQty(0);
                    $quoteItem->setIsSeparateShip(0);
                    /** 
                     * set item is gift product & contain gift wrap 
                     * also set gift item qty & gift wrap qty.
                     */
                    if(isset($data["is_gift_item"]) && $data["is_gift_item"]){
                        $quoteItem->setIsGiftItem(1);
                        if(isset($data["gift_qty"])){
                            $quoteItem->setGiftItemQty($data["gift_qty"]);
                        }
                        if(isset($data["is_gift_wrap"]) && $data["is_gift_wrap"]){
                            $quoteItem->setIsGiftWrap(1);
                            if(isset($data["gift_wrap_qty"])){
                                $quoteItem->setGiftWrapQty($data["gift_wrap_qty"]);
                            }
                        }
                    }
                    
                    /** set item is_separate_ship to 0|1 */
                    if(isset($data["is_separate_ship"]) && $data["is_separate_ship"]){
                        $quoteItem->setIsSeparateShip(1);
                    }
                    $quoteAddress->addItem($quoteItem, $qty);
                }
                /**  Require shiping rate recollect */
                $quoteAddress->setCollectShippingRates(1);
            }
        }
        return $this;
    }
    
    public function changeShippingAddress2($data){
        $customerAddrId = $data["customer_address"];
        $addressId = $data["address_id"];
        $addresses = $this->getQuote()->getAllShippingAddresses();
        try {
            foreach ($addresses as $address) {
                if($address->getId() == $addressId){
                    $customerAddress = $this->getCustomer()->getAddressById($customerAddrId);
                    $address->setCollectShippingRates(true)
                    ->importCustomerAddress($customerAddress)
                    ->collectTotals();
                    
                    $quote = $this->getQuote();
                    $quote->setTotalsCollectedFlag(false);
                    $quote->collectTotals()->save();
                    break;
                }
            }
        } catch (Exception $e) {
        }
        
    }
    
    /**
     * change shipping address of customer
     * from shipping method step.
     * @param array $data
     */
    public function changeShippingAddress($data)
    {
        $customerAddrId = $data["customer_address"];
        $addressId = $data["address_id"];
        $addresses = $this->getQuote()->getAllShippingAddresses();
        $requestParams = array();
        $shippingMethodArray = array();
        $index = 0;
        foreach ($addresses as $address) {
            foreach ($address->getAllVisibleItems() as $item){
                if($address->getShippingMethod()){
                    $shippingMethodArray[$address->getCustomerAddressId()] = $address->getShippingMethod();
                }
                $requestParams[$index] = array(
                    $item->getQuoteItemId() => array(
                        "qty" => $item->getQty(),
                        "is_gift_item" => $item->getIsGiftItem(),
                        "is_gift_wrap" => $item->getIsGiftWrap(),
                        "address" => ($addressId == $address->getId()) ? $customerAddrId : $address->getCustomerAddressId(),
                        "is_separate_ship" => $item->getIsSeparateShip()
                    )
                ); 
                $index++;
            }
        }
        $this->setShippingItemsInformation($requestParams);
        $addresses = $this->getQuote()->getAllShippingAddresses();
        foreach ($addresses as $address) {
            if (isset($shippingMethodArray[$address->getCustomerAddressId()])) {
                $address->setShippingMethod($shippingMethodArray[$address->getCustomerAddressId()]);
            }
        }
        $this->save();
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
            $signatureArray = array();
            $addresses = $this->getQuote()->getAllShippingAddresses();
            foreach ($addresses as $address) {
                $signatureArray[$address->getCustomerAddressId()] = $address->getNoSignatureDelivery();
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
            
            //set signture once again
            $addresses = $this->getQuote()->getAllShippingAddresses();
            foreach ($addresses as $address) {
                try{
                    $address->setNoSignatureDelivery(0);
                    if (isset($signatureArray[$address->getCustomerAddressId()])) {
                        if($signatureArray[$address->getCustomerAddressId()]){
                            $address->setNoSignatureDelivery(1);
                        }
                    }
                }catch (Exception $e){
                    
                }
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
        $addressCount = count($shippingAddresses);
        
        if($addressCount > 1){
            if ($this->getQuote()->hasVirtualItems()) {
                $shippingAddresses[] = $this->getQuote()->getBillingAddress();
            }
        }
        
        try {
            if($addressCount > 1){
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
                
            }else{
                $this->getQuote()->setIsMultiShipping(false)->save();
                $this->getQuote()->collectTotals();
                $payment = Mage::app()->getRequest()->getPost('payment');
                $paymentInstance = $this->getQuote()->getPayment();
                if (isset($payment['cc_number'])) {
                    $paymentInstance->setCcNumber($payment['cc_number']);
                }
                if (isset($payment['cc_cid'])) {
                    $paymentInstance->setCcCid($payment['cc_cid']);
                }
                
                $service = Mage::getModel('sales/service_quote', $this->getQuote());
                $service->submitAll();
                //$this->getQuote()->save();
                $order = $service->getOrder();
                if ($order) {
                    $orderIds[$order->getId()] = $order->getIncrementId();
                    
                    try {
                        $order->queueNewOrderEmail();
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                }
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
