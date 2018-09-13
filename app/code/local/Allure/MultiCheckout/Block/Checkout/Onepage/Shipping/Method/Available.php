<?php

class Allure_MultiCheckout_Block_Checkout_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{

    protected $_rates_twoship_instock;

    protected $_address_twoship_instock;

    protected $_rates_twoship_outofstock;

    protected $_address_twoship_outofstock;
    
    
    public function getShippingRates()
    {
        
        if (empty($this->_rates)) {
            $this->getAddress()->collectShippingRates()->save();
            $storeId=Mage::app()->getStore()->getStoreId();
            $groups = $this->getAddress()->getGroupedAllShippingRates();
            
            
            foreach ($groups as $code => $_rates){
                $quote=Mage::getSingleton('checkout/session')->getQuote();
                $quoteItems=$quote->getAllVisibleItems();
                
                if($code="allure_pickinstore"){
                    $allowSpecificAttributeProducts=Mage::getStoreConfig('carriers/allure_pickinstore/specificproduct',$storeId);
                    $allowSpecificAttributeProductsArray=explode(',', $allowSpecificAttributeProducts);
                   
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
               
                }
                if($code="flatrate"){
                    foreach ($quoteItems as $item){
                        if($item->getProduct()->getSku()!='SAMPLERINGS'){
                            unset($groups[$code]);
                            break;
                        }
                    }
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
            $quote=Mage::getSingleton("allure_multicheckout/ordered_session")->getQuote();
            $quoteItems=$quote->getAllVisibleItems();
            
            foreach ($groups as $code => $_rates){
                if($code="allure_pickinstore"){
                    $allowSpecificAttributeProducts=Mage::getStoreConfig('carriers/allure_pickinstore/specificproduct',$storeId);
                    $allowSpecificAttributeProductsArray=explode(',', $allowSpecificAttributeProducts);
                   
                    
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
                if($code="flatrate"){
                    foreach ($quoteItems as $item){
                        if($item->getProduct()->getSku()!='SAMPLERINGS'){
                            unset($groups[$code]);
                            break;
                        }
                    }
                }
                
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
            $quote=Mage::getSingleton("allure_multicheckout/backordered_session")->getQuote();
            $quoteItems=$quote->getAllVisibleItems();
            foreach ($groups as $code => $_rates){
                Mage::log($code, Zend_Log::DEBUG, 'ajay.log', true);
                
                if($code="allure_pickinstore"){
                    $allowSpecificAttributeProducts=Mage::getStoreConfig('carriers/allure_pickinstore/specificproduct',$storeId);
                    $allowSpecificAttributeProductsArray=explode(',', $allowSpecificAttributeProducts);
                    
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
                if($code="flatrate"){
                    foreach ($quoteItems as $item){
                        if($item->getProduct()->getSku()!='SAMPLERINGS'){
                            unset($groups[$code]);
                            break;
                        }
                    }
                }
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
