<?php


class Allure_Inventory_Block_Adminhtml_Reports_Stockreceive extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

		$this->_controller = "adminhtml_reports_stockreceive";
		$this->_blockGroup = "inventory";
		$this->_headerText = Mage::helper("inventory")->__("Stock Receiving Report");
		parent::__construct();
		$this->_removeButton('add');

	}

}