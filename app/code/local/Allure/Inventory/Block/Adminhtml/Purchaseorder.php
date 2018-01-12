<?php


class Allure_Inventory_Block_Adminhtml_Purchaseorder extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

		$this->_controller = "adminhtml_purchaseorder";
		$this->_blockGroup = "inventory";
		$this->_headerText = Mage::helper("inventory")->__("Purchase Orders");
		parent::__construct();
		if(Mage::helper('allure_vendor')->isUserVendor())
		{
			$this->_removeButton('add');
			$this->_headerText = Mage::helper("inventory")->__("Purchase Orders");
			
		}

	}

}