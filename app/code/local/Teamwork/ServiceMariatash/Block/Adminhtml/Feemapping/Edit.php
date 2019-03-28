<?php
class Teamwork_ServiceMariatash_Block_Adminhtml_Feemapping_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{   
    protected function _construct()
    {
        $this->_blockGroup = 'teamwork_servicemariatash';
        $this->_controller = 'adminhtml_feemapping';
    }

    public function getHeaderText()
    {
        $helper = Mage::helper('teamwork_service');
		
		return $helper->__("New mapping");
    }
}