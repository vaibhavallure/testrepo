<?php

class Ecp_Tattoo_Block_Adminhtml_Consultations extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_consultations';
        $this->_blockGroup = 'ecp_tattoo';
        $this->_headerText = Mage::helper('ecp_tattoo')->__('Free Consultations');
        $this->_addButtonLabel = Mage::helper('ecp_tattoo')->__('Add Item');
        parent::__construct();
        
        $this->removeButton('add');
    }
}