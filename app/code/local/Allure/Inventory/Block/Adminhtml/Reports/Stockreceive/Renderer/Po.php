<?php 
class Allure_Inventory_Block_Adminhtml_Reports_Stockreceive_Renderer_Po extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
	 
	public function render(Varien_Object $row)
	{
		$value      = $row->getData($this->getColumn()->getIndex());
		if(!$value) 
		    $value='-';
		$output='<lable>'.$value.'</lable>';
		return $output;
	}
	public function renderExport(Varien_Object $row)
	{
	    $value      = $row->getData($this->getColumn()->getIndex());
	    return $value;
	}
}
