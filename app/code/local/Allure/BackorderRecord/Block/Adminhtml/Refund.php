<?php

class Allure_BackorderRecord_Block_Adminhtml_Refund extends Mage_Adminhtml_Block_Widget_Form_Container {
    public function __construct()
    {
        $this->_blockGroup = 'backorderrecord';
        $this->_controller = 'adminhtml_refund';
        $this->_headerText = $this->__('Detail Refund Report');


        parent::__construct();
    }
}