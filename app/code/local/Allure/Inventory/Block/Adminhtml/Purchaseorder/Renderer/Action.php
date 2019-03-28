<?php 
class Allure_Inventory_Block_Adminhtml_Purchaseorder_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
	 
	public function render(Varien_Object $row)
	{
		
		$value      = $row->getData($this->getColumn()->getIndex());
		$order=Mage::getModel('inventory/purchaseorder')->load($value);
		$output="";
		$key = Mage::getSingleton('adminhtml/url')->getSecretKey("adminhtml/inventory_purchase/","accept");
		if($order->getStatus()==Allure_Inventory_Helper_Data::ORDER_STATUS_NEW)
			//$output.='<button type="button" action='.Mage::helper('adminhtml')->getUrl('adminhtml/inventory_purchase/accept', array('_secure' => true,'id'=>$value,'key'=>key)).'>Accept</button>';
			$output.='<a href='.Mage::helper('adminhtml')->getUrl('adminhtml/inventory_purchase/accept', array('_secure' => true,'id'=>$value,'key'=>$key)).'>Accept</a>';
		return $output;
	}
	public function renderExport(Varien_Object $row)
	{
	    return '';
	}
}
