<?php
class Allure_PromoBox_Block_Adminhtml_Category_Renderer_Category extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{

    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }

    protected function _getValue(Varien_Object $row)
    {
        $val = $row->getData($this->getColumn()->getIndex());
        return $this->getCategoryName($val);
    }
    protected function getCategoryName($category_id)
    {
        $_category = Mage::getModel('catalog/category')->load($category_id);

        return $_category->getName();
    }

}