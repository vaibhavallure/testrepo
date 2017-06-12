<?php


class Allure_Noimages_Block_Adminhtml_Noimages extends Mage_Adminhtml_Block_Widget_Grid_Container{

	
	public function __construct()
	{

		$this->_controller = "adminhtml_noimages";
		$this->_blockGroup = "noimages";
		$this->_headerText = Mage::helper("core")->__("Products with No Images");
		parent::__construct();
		
	}

}