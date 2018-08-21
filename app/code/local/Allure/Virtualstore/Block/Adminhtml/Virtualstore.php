<?php
/**
 * Created by PhpStorm.
 * User: swapnil
 * Date: 8/14/18
 * Time: 6:09 PM
 */
class Allure_Virtualstore_Block_Adminhtml_Virtualstore extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct()
    {
        $this->_blockGroup = 'virtualstore';
        $this->_controller = 'adminhtml_virtualstore';
        $this->_headerText = $this->__('Virtual Store');
        $this->_addButtonLabel = Mage::helper("virtualstore")->__("Add New Virtual Store");
        parent::__construct();
    }
}