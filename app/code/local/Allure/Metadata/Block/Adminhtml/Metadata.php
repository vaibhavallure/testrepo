<?php


class Allure_Metadata_Block_Adminhtml_Metadata extends Mage_Adminhtml_Block_Widget_Grid_Container{



	public function __construct()
	{

	$this->_controller = "adminhtml_metadata";
	$this->_blockGroup = "metadata";
	$this->_headerText = Mage::helper("metadata")->__("Metadata Manager");
	$this->_addButtonLabel = Mage::helper("metadata")->__("Add New Item");
	parent::__construct();
	
	}

}