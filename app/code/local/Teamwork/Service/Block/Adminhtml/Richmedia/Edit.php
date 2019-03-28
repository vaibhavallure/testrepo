<?php

class Teamwork_Service_Block_Adminhtml_Richmedia_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{   
    public function __construct()
    {
        $helper = Mage::helper('teamwork_service');
        $this->_blockGroup = 'teamwork_service';
        $this->_controller = 'adminhtml_richmedia';
        $this->_headerText = $helper->__('Mapping Rich Media');
        
        parent::__construct();
        
        $this->_removeButton('back');
        $this->_removeButton('reset');
        
        $this->_updateButton('save', 'label', $helper->__('Save mapping'));
        
        $this->_addButton('add', array(
            'label'     => Mage::helper('teamwork_service')->__('Create new mapping'),
            'class'     => 'add',
            'onclick'   => 'setLocation(\'' . $this->getAddUrl() .'\')',
        ));
    }
    
    public function getAddUrl()
    {
        return $this->getUrl('*/*/mapping', array(
            'channel' => $this->getRequest()->getParam('channel')
            ));
    }
}