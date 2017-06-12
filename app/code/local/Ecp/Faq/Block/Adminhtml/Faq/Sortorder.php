<?php

class Ecp_Faq_Block_Adminhtml_Faq_Sortorder extends Mage_Adminhtml_Block_Template
{

    public function __construct()
    {        
        parent::__construct();
        $this->setTemplate('sort/sortOrderFaq.phtml');
    }    
    
    public function getMenu()
    {
        return Mage::getModel('ecp_faq/faq')->getCollection()->setOrder('main_table.order','asc');
    }
    
    protected function _prepareLayout()
    {        
        return parent::_prepareLayout();
    }
    
}
