<?php

class Ecp_Menu_Block_Adminhtml_Menu_Sortorder extends Mage_Adminhtml_Block_Template
{

    public function __construct()
    {        
        parent::__construct();
        $this->setTemplate('sort/sortOrderMenu.phtml');
    }    
    
    public function getMenu()
    {
        return Mage::getModel('ecp_menu/menu')->getCollection()->setOrder('main_table.order','asc');
    }
    
    protected function _prepareLayout()
    {        
        return parent::_prepareLayout();
    }
    
}
