<?php

class Allure_Inventory_Block_Adminhtml_Inventory_Renderer_Store
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
	
	public function render(Varien_Object $row)
	{
		$value      = $row->getData($this->getColumn()->getIndex());
		$helper=Mage::getModel('core/website')->load($value,'stock_id');
		return $helper->getName();
	}
}
