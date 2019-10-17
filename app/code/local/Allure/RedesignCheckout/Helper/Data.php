<?php
/**
 * 
 * @author allure
 *
 */
class Allure_RedesignCheckout_Helper_Data extends Mage_Core_Helper_Abstract
{   
    /**
     *  gift wrap sku 
     */
    const GIFT_WRAP_SKU = "GIFT_WRAP";
    
    /**
     * Get quote
     * @return Mage_Sales_Model_Quote
     */
    private function getQuote(){
        return Mage::getSingleton("checkout/session")->getQuote();
    }
    
    /**
     * Check quote contain single qty or not
     * @return boolean
     */
    public function isQuoteContainSingleQty(){
        $quote = $this->getQuote();
        if($quote->getItemsQty() == 1)
            return true;
        return false;
    }
    
    /**
     * Check quote contain single product.
     * @return boolean
     */
    public function isQuoteContainSingleProduct(){
        $quote = $this->getQuote();
        if($quote->getItemsCount() == 1)
            return true;
        return false;
    }
    
    /**
     * Get the gift wrap product details by using sku.
     */
    public function getGiftWrap(){
        try {
            $giftWrapSku = self::GIFT_WRAP_SKU;
            $_product = Mage::getModel("catalog/product")->loadByAttribute("sku", $giftWrapSku);
            if($_product){
                return $_product;
            }
        } catch (Exception $e){
            //exception handling
        }
        return null;
    }
    
    public function getShippingAddressItems($address)
    {
        return $address->getAllVisibleItems();
    }
    
    public function isAddressContainBackOrderItem($address){
        $isBackOrderItem = false;
        $isOutofStockItem = $this->isAddressContainOutofItem($address);
        $isInstockItem = $this->isAddressContainInstockItem($address);
        $isBackOrderItem = $isInstockItem & $isOutofStockItem;
        return $isBackOrderItem;
    }
    
    private function isAddressContainOutofItem($address){
        $isOutofStock = false;
        $items = $this->getShippingAddressItems($address);
        $storeId = Mage::app()->getStore()->getStoreId();
        try {
            foreach ($items as $item){
                if($item->getSku() == self::GIFT_WRAP_SKU){
                    continue;
                }
                $_product = Mage::getModel('catalog/product')
                    ->setStoreId($storeId)
                    ->loadByAttribute('sku', $item->getSku());
                $stock = Mage::getModel('cataloginventory/stock_item')
                    ->loadByProduct($_product);
                $stockQty = $stock->getQty();
                if ($stockQty < $item->getQty() && $stock->getManageStock() == 1) {
                    $isOutofStock = true;
                    break;
                }
                $_product = null;
                $stock = null;
            }
        } catch (Exception $e) {
            Mage::log("isAddressContainOutofItem function - Exception:",Zend_Log::DEBUG,"abc.log",true);
            Mage::log($e->getMessage(),Zend_Log::DEBUG,"abc.log",true);
        }
        return $isOutofStock;
    }
    
    private function isAddressContainInstockItem($address){
        $isInstock = false;
        try { 
            $items = $this->getShippingAddressItems($address);
            $storeId = Mage::app()->getStore()->getStoreId();
            foreach ($items as $item){
                if($item->getSku() == self::GIFT_WRAP_SKU){
                    continue;
                }
                $_product = Mage::getModel('catalog/product')
                    ->setStoreId($storeId)
                    ->loadByAttribute('sku', $item->getSku());
                $stock = Mage::getModel('cataloginventory/stock_item')
                    ->loadByProduct($_product);
                $stockQty = $stock->getQty();
                if($stockQty > 0){
                    $isInstock = true;
                    break;
                }
                $_product = null;
                $stock = null;
            }
        } catch (Exception $e){
            Mage::log("isAddressContainInstockItem function - Exception:",Zend_Log::DEBUG,"abc.log",true);
            Mage::log($e->getMessage(),Zend_Log::DEBUG,"abc.log",true);
        }
        return $isInstock;
    }
}
