<?php 
class Allure_Inventory_Block_Adminhtml_Reports_Lowstock_Renderer_Download extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
	 
	public function render(Varien_Object $row)
	{
		$value      = $row->getData($this->getColumn()->getIndex());
		$output='<a href="'.$value.'"download>Download</a>';
		return $output;
	}
}
