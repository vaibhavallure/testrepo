<?php

class Teamwork_Service_Block_Adminhtml_Confattrmap extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $helper = Mage::helper('teamwork_service');
        $this->_blockGroup = 'teamwork_service';
        $this->_controller = 'adminhtml_confattrmap';
        $this->_headerText = $helper->__('Mapping configurable attributes');
        
        parent::__construct();

        $this->_removeButton('add');
    }
}
