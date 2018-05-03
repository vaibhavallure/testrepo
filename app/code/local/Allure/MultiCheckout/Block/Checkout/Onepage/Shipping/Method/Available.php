<?php

class Allure_MultiCheckout_Block_Checkout_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{

    protected $_rates_twoship_instock;

    protected $_address_twoship_instock;

    protected $_rates_twoship_outofstock;

    protected $_address_twoship_outofstock;
    
    /**
     * aws02
     * check quote contains only sample ring product's
     */
    private function checkSampleProduct($type = 0){
        $helper = Mage::helper("allure_multicheckout");
        $productSku = ($helper->getProductSku())?$helper->getProductSku():"";
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if($type == 1){
            $quote = Mage::getSingleton("allure_multicheckout/ordered_session")->getQuote();
        }elseif ($type == 2){
            $quote = Mage::getSingleton("allure_multicheckout/backordered_session")->getQuote();
        }
        
        $isSampleProduct = false;
        if($quote){
            $items = $quote->getAllItems();
            if(count($items) > 1){
                foreach ($items as $item){
                    if(strtolower($item->getSku()) == strtolower($productSku)){
                        $isSampleProduct = true;
                        break;
                    }
                }
            }
        }
        return $isSampleProduct;
    }
    
    public function getShippingRates()
    {
        
        if (empty($this->_rates)) {
            $this->getAddress()->collectShippingRates()->save();
            $storeId=Mage::app()->getStore()->getStoreId();
            $groups = $this->getAddress()->getGroupedAllShippingRates();
            
            //aws02 start
            $isSampleProduct = $this->checkSampleProduct(0); // 0 - for main order
            $helper = Mage::helper("allure_multicheckout");
            $removableShippingMethod = ($helper->getShippingMethods()) ?$helper->getShippingMethods():"";
            //aws02 end
            
            foreach ($groups as $code => $_rates){
                $code1 = $code;//aws02 code
                if($code="allure_pickinstore"){
                    $allowSpecificAttributeProducts=Mage::getStoreConfig('carriers/allure_pickinstore/specificproduct',$storeId);
                    $allowSpecificAttributeProductsArray=explode(',', $allowSpecificAttributeProducts);
                    $quote=Mage::getSingleton('checkout/session')->getQuote();
                    $quoteItems=$quote->getAllVisibleItems();
                    $isSingleGiftCardItem=true;
                    foreach ($quoteItems as $item){
                        if(!in_array($item->getProduct()->getAttributeSetId(), $allowSpecificAttributeProductsArray)){
                            $isSingleGiftCardItem=false;
                            break;
                        }
                    }
                    $attributeFlag=false;
                    foreach ($quoteItems as $item){
                        if(in_array($item->getProduct()->getAttributeSetId(), $allowSpecificAttributeProductsArray)){
                            $attributeFlag=true;
                            break;
                        }
                    }
                    if(!$attributeFlag || !$isSingleGiftCardItem){
                        unset($groups[$code]);
                    }
                }
                
                //aws02 - start
                if($isSampleProduct){
                    if($code1 == $removableShippingMethod){
                        unset($groups[$code1]);
                    }
                }
                //aws02 - end
            }
            
            return $this->_rates = $groups;
        }
        
        return $this->_rates;
    }

    /* Two ship in stock products shipping ratess */
    public function getShippingRatesForTwoShipInStockProducts ()
    {
        // Mage::log(json_encode($this->getAddressForTwoShipInStockProducts()->getData()),Zend_log::DEBUG,'abc',true);
        if (empty($this->_rates_twoship_instock)) {
            /*
             * $oldQuote = $this->getCheckout()->getQuote();
             * $this->getCheckout()->replaceQuote($this->getQuoteInstockProducts());
             */
            $this->getAddressForTwoShipInStockProducts()
                ->collectShippingRates()
                ->save();
            $groups = $this->getAddressForTwoShipInStockProducts()->getGroupedAllShippingRates();
            $storeId=Mage::app()->getStore()->getStoreId();
            
            //aws02 start
            $isSampleProduct = $this->checkSampleProduct(1); // 1 - for in stock order
            $helper = Mage::helper("allure_multicheckout");
            $removableShippingMethod = ($helper->getShippingMethods()) ?$helper->getShippingMethods():"";
            //aws02 end
            
            foreach ($groups as $code => $_rates){
                $code1 = $code;//aws02 code
                if($code="allure_pickinstore"){
                    $allowSpecificAttributeProducts=Mage::getStoreConfig('carriers/allure_pickinstore/specificproduct',$storeId);
                    $allowSpecificAttributeProductsArray=explode(',', $allowSpecificAttributeProducts);
                    $quote=Mage::getSingleton("allure_multicheckout/ordered_session")->getQuote();
                    $quoteItems=$quote->getAllVisibleItems();
                    
                    $isSingleGiftCardItem=true;
                    foreach ($quoteItems as $item){
                        if(!in_array($item->getProduct()->getAttributeSetId(), $allowSpecificAttributeProductsArray)){
                            $isSingleGiftCardItem=false;
                            break;
                        }
                    }
                    $attributeFlag=false;
                    foreach ($quoteItems as $item){
                        if(in_array($item->getProduct()->getAttributeSetId(), $allowSpecificAttributeProductsArray)){
                            $attributeFlag=true;
                            break;
                        }
                    }
                    if(!$attributeFlag || !$isSingleGiftCardItem){
                        unset($groups[$code]);
                    }
                }
                
                //aws02 - start
                if($isSampleProduct){
                    if($code1 == $removableShippingMethod){
                        unset($groups[$code1]);
                    }
                }
                //aws02 - end
            }
            // $this->getCheckout()->replaceQuote($oldQuote);
            return $this->_rates_twoship_instock = $groups;
        }
        return $this->_rates_twoship_instock = $groups;
    }

    /* Two ship out of stock products shipping ratess */
    public function getShippingRatesForTwoShipOutOfStockProducts ()
    {
        if (empty($this->_rates_twoship_outofstock)) {
            /*
             * $oldQuote = $this->getCheckout()->getQuote();
             * $this->getCheckout()->replaceQuote($this->getQuoteOutOfstockProducts());
             */
            $this->getAddressForTwoShipOutOfStockProducts()
                ->collectShippingRates()
                ->save();
            $groups = $this->getAddressForTwoShipOutOfStockProducts()->getGroupedAllShippingRates();
            $storeId=Mage::app()->getStore()->getStoreId();
            
            //aws02 start
            $isSampleProduct = $this->checkSampleProduct(2); // 2 - for back order quote
            $helper = Mage::helper("allure_multicheckout");
            $removableShippingMethod = ($helper->getShippingMethods()) ?$helper->getShippingMethods():"";
            //aws02 end
            
            foreach ($groups as $code => $_rates){
                $code1 = $code;//aws02 code
                if($code="allure_pickinstore"){
                    $allowSpecificAttributeProducts=Mage::getStoreConfig('carriers/allure_pickinstore/specificproduct',$storeId);
                    $allowSpecificAttributeProductsArray=explode(',', $allowSpecificAttributeProducts);
                    $quote=Mage::getSingleton("allure_multicheckout/backordered_session")->getQuote();
                    $quoteItems=$quote->getAllVisibleItems();
                    $isSingleGiftCardItem=true;
                    foreach ($quoteItems as $item){
                        if(!in_array($item->getProduct()->getAttributeSetId(), $allowSpecificAttributeProductsArray)){
                            $isSingleGiftCardItem=false;
                            break;
                        }
                    }
                    $attributeFlag=false;
                    foreach ($quoteItems as $item){
                        if(in_array($item->getProduct()->getAttributeSetId(), $allowSpecificAttributeProductsArray)){
                            $attributeFlag=true;
                            break;
                        }
                    }
                    if(!$attributeFlag || !$isSingleGiftCardItem){
                        unset($groups[$code]);
                    }
                }
                
                //aws02 - start
                if($isSampleProduct){
                    if($code1 == $removableShippingMethod){
                        unset($groups[$code1]);
                    }
                }
                //aws02 - end
            }
            // $this->getCheckout()->replaceQuote($oldQuote);
            return $this->_rates_twoship_outofstock = $groups;
        }
        return $this->_rates_twoship_outofstock = $groups;
    }

    /* Two ship in stock products shipping address */
    public function getAddressForTwoShipInStockProducts ()
    {
        if (empty($this->_address_twoship_instock)) {
            $this->_address_twoship_instock = $this->getQuoteInstockProducts()->getShippingAddress();
        }
        return $this->_address_twoship_instock;
    }

    /* Two ship Out of stock products shipping address */
    public function getAddressForTwoShipOutOfStockProducts ()
    {
        if (empty($this->_address_twoship_outofstock)) {
            $this->_address_twoship_outofstock = $this->getQuoteOutOfstockProducts()->getShippingAddress();
        }
        return $this->_address_twoship_outofstock;
    }

    public function getAddress ()
    {
        if (empty($this->_address)) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }
        return $this->_address;
    }

    private function getQuoteInstockProducts ()
    {
        return Mage::getSingleton("allure_multicheckout/ordered_session")->getQuote();
    }

    private function getQuoteOutOfstockProducts ()
    {
        return Mage::getSingleton("allure_multicheckout/backordered_session")->getQuote();
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

    public function checkGiftCardIsPresentInStockOrder ()
    {
        $quote = Mage::getSingleton("allure_multicheckout/ordered_session")->getQuote();
        return $this->checkQuoteItemsWithGiftCard($quote);
    }

    public function checkGiftCardIsPresentOutofStockOrder ()
    {
        $quote = Mage::getSingleton("allure_multicheckout/backordered_session")->getQuote();
        return $this->checkQuoteItemsWithGiftCard($quote);
    }

    public function checkGiftCardInQuotes ()
    {
        $isGiftCardInStockQuote = $this->checkGiftCardIsPresentInStockOrder();
        $isGiftCardOutOfStockQuote = $this->checkGiftCardIsPresentOutofStockOrder();
        $isGiftCardWithTwoQuotes = ($isGiftCardInStockQuote && $isGiftCardOutOfStockQuote);
        $status = array(
                'both' => $isGiftCardWithTwoQuotes,
                'first_ship_method' => $isGiftCardInStockQuote,
                'second_ship_method' => $isGiftCardOutOfStockQuote
        );
        return $status;
    }

    public function getOrderTotal ()
    {
        $type = array();
        $quote = $this->getQuote()->getData();
        if ($quote['delivery_method'] == 'two_ship') {
            $quote1 = Mage::getSingleton("allure_multicheckout/ordered_session")->getQuote()->getData();
            $quote2 = Mage::getSingleton("allure_multicheckout/backordered_session")->getQuote()->getData();
            $type['two_ship'] = array(
                    'in_stock_total' => $quote1['base_subtotal'],
                    'out_stock_total' => $quote2['base_subtotal']
            );
        } else {
            $type['one_ship'] = array(
                    'total' => $quote['base_subtotal']
            );
        }
        
        return $type;
    }
}
