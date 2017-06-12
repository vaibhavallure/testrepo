<?php

class Ecp_Footlinks_Block_Adminhtml_Footlinks_Sortorder extends Mage_Adminhtml_Block_Template
{

    public function __construct()
    {        
        parent::__construct();
        $this->setTemplate('sort/sortOrder.phtml');
    }    
    
    public function getLinks()
    {
        return Mage::getModel('ecp_footlinks/footlinks')->getCollection()->addOrder('sort_order','asc');
    }
    
    protected function _prepareLayout()
    {        
        return parent::_prepareLayout();
    }
    
}
