<?php

class Ecp_Reviews_Block_Adminhtml_Reviews_Sortorder extends Mage_Adminhtml_Block_Template
{

    public function __construct()
    {        
        parent::__construct();
        $this->setTemplate('sort/sortOrderReviews.phtml');
    }    
    
    public function getReviews()
    {
        return Mage::getModel('ecp_reviews/reviews')->getCollection()->addOrder('sort_order','asc');
    }
    
    protected function _prepareLayout()
    {        
        return parent::_prepareLayout();
    }
    
}
