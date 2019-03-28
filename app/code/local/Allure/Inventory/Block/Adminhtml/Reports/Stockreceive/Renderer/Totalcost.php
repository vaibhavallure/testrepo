<?php 
class Allure_Inventory_Block_Adminhtml_Reports_Stockreceive_Renderer_Totalcost extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
	 
	public function render(Varien_Object $row)
	{
		$value      = $row->getData();
		$output='<label>'.round($value['cost']*$value['added_qty'],2).'</label>';
		return $output;
	}
	public function renderExport(Varien_Object $row)
	{
	    $value      = $row->getData();
	    return round($value['cost']*$value['added_qty'],2);
	}
}
