<?php


class Allure_Inventory_Block_Adminhtml_Reports_Minmax extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

		$this->_controller = "adminhtml_reports_minmax";
		$this->_blockGroup = "inventory";
		$this->_headerText = Mage::helper("inventory")->__("Min max Report");
		parent::__construct();
		$this->_removeButton('add');

	}

}