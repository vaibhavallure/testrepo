<?php


class Allure_Inventory_Block_Adminhtml_Title extends Mage_Adminhtml_Block_Template {
    
    public function getTitle() {
        $title = '<h3><a href="' . $this->getUrl('adminhtml/inventory_dashboard/index') .'">';
        $title .= '<span>'. $this->__('Inventory Management') .'</span></a></h3>';
        $titleObject = new Varien_Object(array('text' => $title));
        Mage::dispatchEvent('inventoryplus_before_show_title', array('title' => $titleObject));
        return $titleObject->getText();
    }
}