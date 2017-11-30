<?php 
class Allure_Inventory_Block_Adminhtml_Reports_Stockreceive_Renderer_Total extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
	 
	public function render(Varien_Object $row)
	{
		$value      = $row->getData($this->getColumn()->getIndex());
		$inventory = Mage::getModel('inventory/inventory')->load($value);
		$total="0";
		if(isset($inventory) && $inventory) 
			$total=$inventory->getPreviousQty()+$inventory->getAddedQty();
		$output='<lable>'.$total.'</lable>';
		return $output;
	}
	public function renderExport(Varien_Object $row)
	{
	    $value      = $row->getData($this->getColumn()->getIndex());
	    $inventory = Mage::getModel('inventory/inventory')->load($value);
	    $total="0";
	    if(isset($inventory) && $inventory)
	        $total=$inventory->getPreviousQty()+$inventory->getAddedQty();
	    return $total;
	}
}
