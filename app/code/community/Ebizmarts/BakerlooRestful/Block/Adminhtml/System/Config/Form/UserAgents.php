<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_System_Config_Form_UserAgents extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('ua_name', array(
            'label' => Mage::helper('bakerloo_restful')->__('User Agent'),
            'style' => 'width:120px',
        ));

        $this->addColumn('ua_regex', array(
            'label' => Mage::helper('bakerloo_restful')->__('Regex'),
            'style' => 'width:120px',
        ));

        $this->_addAfter = false;
        parent::__construct();
    }
}