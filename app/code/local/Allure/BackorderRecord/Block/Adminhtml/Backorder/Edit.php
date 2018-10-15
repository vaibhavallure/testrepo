<?php

class Allure_BackorderRecord_Block_Adminhtml_Backorder_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
      $this->_objectId = "backorder_id";
        $this->_blockGroup = "backorderrecord";
        $this->_controller = "adminhtml_backorder";

        $this->_updateButton('save', 'label', Mage::helper('backorderrecord')->__('Download'));
        $this->_updateButton('delete', 'label', Mage::helper('backorderrecord')->__('Delete Item'));

    }

    public function getHeaderText()
    {

            return Mage::helper("backorderrecord")->__("Download Report");


    }
}