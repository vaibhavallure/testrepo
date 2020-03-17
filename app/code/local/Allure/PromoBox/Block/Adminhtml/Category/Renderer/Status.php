<?php
class Allure_PromoBox_Block_Adminhtml_Category_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{

    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }

    protected function _getValue(Varien_Object $row)
    {
        $val = $row->getData($this->getColumn()->getIndex());
        return $this->getStatusLabel($val);
    }
    protected function getStatusLabel($status_key)
    {
        $status=array(0=>"Disabled",1=>"Enabled");
        return $status[$status_key];
    }

}