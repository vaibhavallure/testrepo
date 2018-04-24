<?php
class Teamwork_Service_Block_Adminhtml_Service_Edit_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $helper = Mage::helper('teamwork_service');
        $this->_blockGroup = 'teamwork_service';
        $this->_controller = 'adminhtml_service_edit';
        $this->_headerText = $helper->__('Edit values');
        
        parent::__construct();
        
        $this->_addButton('delete', array(
            'label'     => Mage::helper('adminhtml')->__('Delete'),
            'class'     => 'delete',
            'onclick'   => 'deleteConfirm(\''. Mage::helper('adminhtml')->__('Are you sure you want to do this?')
            .'\', \'' . $this->getDeleteUrl() . '\')',
        ));
        
        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/*/view', array('table' => 
            $this->getRequest()->getParam('entity'))) . '\');');
        
        $this->_removeButton('reset');
    }
    
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array(
            'entity_id' => $this->getRequest()->getParam('entity_id'),
            'entity' => $this->getRequest()->getParam('entity')
            ));
    }
}