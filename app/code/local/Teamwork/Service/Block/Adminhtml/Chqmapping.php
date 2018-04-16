<?php
class Teamwork_Service_Block_Adminhtml_Chqmapping extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $helper = Mage::helper('teamwork_service');
        $this->_blockGroup = 'teamwork_service';
        $this->_controller = 'adminhtml_chqmapping';
        $this->_headerText = $helper->__('Mapping CHQ Fields');
        
        parent::__construct();
    }
}