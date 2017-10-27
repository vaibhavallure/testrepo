<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Stockstatus
*/
class Amasty_Stockstatus_Model_Rewrite_Sales_Quote_Item extends Mage_Sales_Model_Quote_Item
{
    public function getMessage($string = true)
    {
            if (('checkout' == Mage::app()->getRequest()->getModuleName() || 'amscheckout' == Mage::app()->getRequest()->getModuleName()) && Mage::getStoreConfig('amstockstatus/general/displayincart'))
            {
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$this->getSku());
		if(!$product){
			$product = Mage::getModel('catalog/product')->load($this->getProduct()->getId());
		}
		if(('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Preorder/active') && Mage::helper('ampreorder')->getIsProductPreorder($product))) return parent::getMessage($string);

                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                if ( !(Mage::getStoreConfig('amstockstatus/general/displayforoutonly') && $product->isSaleable()) || ($product->isInStock() && $stockItem->getData('qty') <= Mage::helper('amstockstatus')->getBackorderQnt() ) )
                {
		    $status = Mage::helper('amstockstatus')->getCustomStockStatusText(Mage::getModel('catalog/product')->load($product->getId()));
                    if ($status  && (!Mage::registry('am_is_duplicate') || (Mage::registry('am_is_duplicate') && !array_key_exists($product->getId(), Mage::registry('am_is_duplicate')))))
                    {

                           $this->addMessage($status);
			   if(Mage::registry('am_is_duplicate')) $massKey = Mage::registry('am_is_duplicate');
			   else $massKey = array();
			   $massKey[$this->getId()] = $this->getId();
			   Mage::unregister('am_is_duplicate');
			   Mage::register('am_is_duplicate', $massKey);
                    }
                }
            }
        return parent::getMessage($string);
    }
    
    /**
     * Adding quantity to quote item
     *
     * @param float $qty
     * @return Mage_Sales_Model_Quote_Item
     */
    public function addQtyasNew($qty)
    {
        $oldQty = $this->getQty();
        //$qty = $this->_prepareQtyasNew($qty);
        if($qty < 0){
            $qty = $qty * (-1);
        }
        
        /**
         * We can't modify quontity of existing items which have parent
         * This qty declared just once duering add process and is not editable
         */
        if (!$this->getParentItem() || !$this->getId()) {
            $this->setQtyToAdd($qty);
            $this->setQtyasNew($oldQty + $qty);
        }
        return $this;
    }
    
    /**
     * Declare quote item quantity
     *
     * @param float $qty
     * @return Mage_Sales_Model_Quote_Item
     */
    public function setQtyasNew($qty)
    {
        //$qty = $this->_prepareQty($qty);
        $oldQty = $this->_getData('qty');
        $this->setData('qty', $qty);
        
        Mage::dispatchEvent('sales_quote_item_qty_set_after', array('item' => $this));
        
        if ($this->getQuote() && $this->getQuote()->getIgnoreOldQty()) {
            return $this;
        }
        if ($this->getUseOldQty()) {
            $this->setData('qty', $oldQty);
        }
        
        return $this;
    }
    
    /**
     * Prepare quantity
     *
     * @param float|int $qty
     * @return int|float
     */
    protected function _prepareQtyasNew($qty)
    {
        $qty = Mage::app()->getLocale()->getNumber($qty);
        $qty = ($qty > 0) ? $qty : 1;
        return $qty;
    }
}