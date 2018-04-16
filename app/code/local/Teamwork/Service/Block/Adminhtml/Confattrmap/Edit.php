<?php

class Teamwork_Service_Block_Adminhtml_Confattrmap_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{   
    protected function _construct()
    {
        $this->_blockGroup = 'teamwork_service';
        $this->_controller = 'adminhtml_confattrmap';
    }
    
    public function __construct()
    {
        parent::__construct();
        $this->removeButton('reset');
        $this->removeButton('delete');
    }

    public function getHeaderText()
    {
        $model = Mage::registry('model');
        return Mage::helper('teamwork_service')->__("CHQ Attribute Mapping (CHQ Attribute Code: %s)", $model->getData('chq_code'));
    }
}
