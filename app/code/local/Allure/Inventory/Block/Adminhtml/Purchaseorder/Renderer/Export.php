<?php 
class Allure_Inventory_Block_Adminhtml_Purchaseorder_Renderer_Export extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
	 
	public function render(Varien_Object $row)
	{
		
		$value      = $row->getData($this->getColumn()->getIndex());
		$order=Mage::getModel('inventory/purchaseorder')->load($value);
		$output="";
		//$key = Mage::getSingleton('adminhtml/url')->getSecretKey("adminhtml/inventory_purchase/","export");
		$output.='<a href='.Mage::helper('adminhtml')->getUrl('adminhtml/inventory_purchase/export', array('_secure' => true,'id'=>$value)).'>Export</a>';
		return $output;
	}
	public function renderExport(Varien_Object $row)
	{
	    return '';
	}
}
