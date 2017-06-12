<?php


class Allure_Inventory_Block_Adminhtml_Reports_Transfer extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

		$this->_controller = "adminhtml_reports_transfer";
		$this->_blockGroup = "inventory";
		$this->_headerText = Mage::helper("inventory")->__("Stock Transfer Report");
		parent::__construct();
		$this->_removeButton('add');

	}

}