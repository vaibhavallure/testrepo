<?php
class Teamwork_Service_Block_Adminhtml_Service_Edit extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $entityName = $this->getRequest()->getParam('table');
        $helper = Mage::helper('teamwork_service');
        $this->_blockGroup = 'teamwork_service';
        $this->_controller = 'adminhtml_service_edit';
        $this->_headerText = $helper->__('Table '.$entityName.' values');
        
        parent::__construct();

        $this->_addButton('back', array(
            'label'   => Mage::helper('adminhtml')->__('Back'),
            'onclick' => 'setLocation(\'' . Mage::helper('adminhtml')->getUrl('*/*/') . '\')',
            'class'   => 'back',
            'level'   => -1
        ));
        
        $this->_removeButton('add');
    }
}