<?php
class Allure_PromoBox_Block_Adminhtml_Banner_Renderer_Size extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{

    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }

    protected function _getValue(Varien_Object $row)
    {
        $val = $row->getData($this->getColumn()->getIndex());
        return $this->getLabel($val);
    }
    protected function getLabel($key)
    {
        $arr=array("one_by_two"=>"1 X 2","two_by_two"=>"2 X 2");
        return $arr[$key];
    }

}