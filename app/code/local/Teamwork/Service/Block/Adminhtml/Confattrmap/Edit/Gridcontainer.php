<?php

class Teamwork_Service_Block_Adminhtml_Confattrmap_Edit_Gridcontainer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $helper = Mage::helper('teamwork_service');
        $this->_blockGroup = 'teamwork_service';
        $this->_controller = 'adminhtml_confattrmap_edit_gridcontainer';
        $this->_headerText = $helper->__('Products That Prevent a Change in Setting');
        
        parent::__construct();

        $this->_removeButton('add');
        $this->setTemplate('catalog/product.phtml');
    }
    
    /**
     * Check whether it is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        if (!Mage::app()->isSingleStoreMode()) {
               return false;
        }
        return true;
    }
}
