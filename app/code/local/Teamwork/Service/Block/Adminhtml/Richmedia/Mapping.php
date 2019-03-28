<?php

class Teamwork_Service_Block_Adminhtml_Richmedia_Mapping extends Mage_Adminhtml_Block_Widget_Form_Container
{   
    public function __construct()
    {
        $channel_id = $this->getRequest()->getParam('channel');
        
        $channel_name = Mage::registry('channel_name');
        
        $helper = Mage::helper('teamwork_service');
        $this->_blockGroup = 'teamwork_service';
        $this->_controller = 'adminhtml_richmedia_mapping';
        $this->_headerText = $helper->__('Create new mapping: "'.$channel_name[0]['channel_name'].'"');
       
        parent::__construct();
        
        $this->_removeButton('reset');
        
        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/*/index', array('channel' => $channel_id)) . '\');');
    }
}