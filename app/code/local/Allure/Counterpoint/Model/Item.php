<?php

class Allure_Counterpoint_Model_Item extends Mage_Sales_Model_Quote_Item
{
    public function setQty($qty)
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
}
