<?php

class Allure_Productshare_Block_Adminhtml_Productshare extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct ()
    {
        $this->_controller = "adminhtml_productshare";
        $this->_blockGroup = "productshare";
        $this->_headerText = Mage::helper("productshare")->__("Productshare Manager");
        $this->_addButtonLabel = Mage::helper("productshare")->__("Add New Item");
        parent::__construct();
    }
}