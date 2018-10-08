<?php

class Allure_BackorderRecord_Block_Adminhtml_Backorder extends Mage_Adminhtml_Block_Widget_Form_Container {
    public function __construct()
    {
        $this->_blockGroup = 'backorderrecord';
        $this->_controller = 'adminhtml_backorder';
        $this->_headerText = $this->__('Backoreder  Record');


        parent::__construct();
    }
}