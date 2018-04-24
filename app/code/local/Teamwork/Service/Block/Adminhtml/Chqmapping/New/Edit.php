<?php
class Teamwork_Service_Block_Adminhtml_Chqmapping_New_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $helper = Mage::helper('teamwork_service');
        $this->_blockGroup = 'teamwork_service';
        $this->_controller = 'adminhtml_chqmapping_new';
        $this->_headerText = $helper->__('New mapping CHQ field');
        
        parent::__construct();
        
        $this->_removeButton('save');
    }
}