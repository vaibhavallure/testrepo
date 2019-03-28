<?php
class Teamwork_Service_Block_Adminhtml_Service extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $helper = Mage::helper('teamwork_service');
        $this->_blockGroup = 'teamwork_service';
        $this->_controller = 'adminhtml_service';
        $this->_headerText = $helper->__('Staging tables');
        
        parent::__construct();
        
        $this->_removeButton('add');
    }
}