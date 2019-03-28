<?php


class Allure_Inventory_Block_Adminhtml_Purchaseorder_Receive extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

		$this->_controller = "adminhtml_purchaseorder_receive";
		$this->_blockGroup = "inventory";
		$this->_headerText = Mage::helper("inventory")->__("Receive Purchase Orders");
		$this->_addButtonLabel = Mage::helper("core")->__("Add New Item");
		parent::__construct();

	}

}