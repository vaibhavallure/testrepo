<?php

class Allure_Productshare_Block_Adminhtml_Productshare_Edit_Renderer_Website extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render (Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()
            ->getIndex());
        $website = Mage::getModel('core/website')->load($value);
        return $website->getName();
    }
}
?>