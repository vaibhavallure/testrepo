<?php

class Allure_Inventory_Controller_Action extends Mage_Adminhtml_Controller_Action {
    
    /**
     * Menu Path
     * 
     * @var string
     */
    protected $_menu_path;
    
    /**
     * Define active menu item in inventoryplus menu block
     *
     * @return Magestore_Inventoryplus_Adminhtml_Controller_Action
     */
    protected function _setActiveMenu($menuPath)
    {
        if($inventoryMenu = $this->getLayout()->getBlock('inventory_menu')){
            $inventoryMenu->setActive($menuPath);
        }
        return $this;
    }    
    
    /**
     * Check permission
     * 
     * @return boolean
     */
    protected function _isAllowed() {
        if($this->_menu_path)
            return Mage::getSingleton('admin/session')->isAllowed($this->_menu_path);
        else 
            return true;
    }
    
}