<?php 
class Allure_Inventory_Block_Adminhtml_Purchaseorder_Renderer_Items extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
	 
	public function render(Varien_Object $row)
	{
		
		$value      = $row->getData($this->getColumn()->getIndex());
		$entityTypeId = Mage::getModel('eav/entity')
		->setType('catalog_product')
		->getTypeId();
		$prodNameAttrId = Mage::getModel('eav/entity_attribute')
		->loadByCode($entityTypeId, 'name')
		->getAttributeId();
		$collection = Mage::getModel('inventory/orderitems')->getCollection();
		$collection =  $collection->addFieldToFilter( 'po_id', $value);
		
		
		$output='<div  style="position:relative"><ul class="order_items_in_grid" style="white-space: nowrap; max-height: 84px;overflow: hidden;">';
		foreach ($collection as $item):
		//print_r($item->getData());
		    if($item->getIsCustom())
		        $product=Mage::getModel('inventory/customitem')->load($item->getProductId());
		    else{
		        $product=Mage::getModel('Catalog/product')->load($item->getProductId());
		    }
		    $output.= '<li style="margin:3px;"><label>'.$item->getRequestedQty().' x '.'</label><label>'.$product->getName().'</label></li>';
		endforeach;
		$output.="</ul></div>";
		
		return $output;
	}
	public function renderExport(Varien_Object $row)
	{
	    
	    $value      = $row->getData($this->getColumn()->getIndex());
	    $entityTypeId = Mage::getModel('eav/entity')
	    ->setType('catalog_product')
	    ->getTypeId();
	    $prodNameAttrId = Mage::getModel('eav/entity_attribute')
	    ->loadByCode($entityTypeId, 'name')
	    ->getAttributeId();
	    $collection = Mage::getModel('inventory/orderitems')->getCollection();
	    $collection =  $collection->addFieldToFilter( 'po_id', $value);
	    
	    
	    $output='';
	    foreach ($collection as $item):
	    //print_r($item->getData());
	    if($item->getIsCustom())
	        $product=Mage::getModel('inventory/customitem')->load($item->getProductId());
	    else{
	            $product=Mage::getModel('Catalog/product')->load($item->getProductId());
	    }
	    $output.=$item->getRequestedQty().'-'.$product->getName() .",";
	    endforeach;
	    return $output;
	}
}

