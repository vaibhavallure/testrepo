<?php
/**
 * Created by PhpStorm.
 * User: swapnil
 * Date: 8/20/18
 * Time: 3:23 PM
 */
class Allure_Virtualstore_Block_Adminhtml_Website extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct()
    {
//        die("hi");
        $this->_blockGroup = 'allure_virtualstore';
        $this->_controller = 'adminhtml_website';
        $this->_headerText = $this->__('Website');
        $this->_addButtonLabel = Mage::helper("allure_virtualstore")->__("Add New Website");
        parent::__construct();
    }
}