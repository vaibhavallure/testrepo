<?php

class Ecp_DiscoverNavigation_Block_Adminhtml_DiscoverNavigation_Sortorder extends Mage_Adminhtml_Block_Template
{

    public function __construct()
    {        
        parent::__construct();
        $this->setTemplate('sort/sortOrderDiscoverMt.phtml');
    }    
    
    public function getItemsMenu()
    {
        return Mage::getModel('ecp_discovernavigation/discovernavigation')->getCollection()->addFieldToFilter('type',2)->addOrder('sort_order','asc');
    }
    
    protected function _prepareLayout()
    {        
        return parent::_prepareLayout();
    }
    
}
