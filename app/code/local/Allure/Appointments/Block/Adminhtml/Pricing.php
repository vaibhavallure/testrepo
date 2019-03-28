<?php

class Allure_Appointments_Block_Adminhtml_Pricing extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct ()
    {
        $this->_controller = "adminhtml_pricing";
        $this->_blockGroup = "appointments";
        $this->_headerText = Mage::helper("appointments")->__("Manage Piercing Price");
        $this->_addButtonLabel = Mage::helper("appointments")->__("Add Piercing Price");
        parent::__construct();
    }
}