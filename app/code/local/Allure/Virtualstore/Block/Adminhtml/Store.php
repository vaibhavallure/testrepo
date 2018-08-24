<?php
/**
 * Created by PhpStorm.
 * User: swapnil
 * Date: 8/23/18
 * Time: 4:38 PM
 */
class Allure_Virtualstore_Block_Adminhtml_Store extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct()
    {
//        die("Hi");
        $this->_blockGroup = 'allure_virtualstore';
        $this->_controller = 'adminhtml_store';
        $this->_headerText = $this->__('Virtual Store');
        $this->_addButtonLabel = Mage::helper("allure_virtualstore")->__("Add New Virtual Store");
        parent::__construct();
    }
}