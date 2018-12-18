<?php
class Teamwork_ServiceMariatash_Block_Adminhtml_Feemapping extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $helper = Mage::helper('teamwork_service');
        $this->_blockGroup = 'teamwork_servicemariatash';
        $this->_controller = 'adminhtml_feemapping';
        $this->_headerText = $helper->__('Mapping for Shipping Methods');
        
        parent::__construct();
    }
}