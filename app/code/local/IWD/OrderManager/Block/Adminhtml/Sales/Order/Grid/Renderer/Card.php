<?php
class IWD_OrderManager_Block_Adminhtml_Sales_Order_Grid_Renderer_Card extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
    
    public function render(Varien_Object $row)
    {
        $value      = $row->getData($this->getColumn()->getIndex());
        $aType = Mage::getSingleton('payment/config')->getCcTypes();
        if (isset($aType[$value])) {
            $sName = $aType[$value];
        }
        else {
            $sName = Mage::helper('payment')->__('N/A');
        }
        return $sName;
    }
}