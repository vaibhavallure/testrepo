<?php
require_once('../app/Mage.php'); 
umask(0);
Mage::app();
$stockId=2;
echo "<pre>";
$stockId=2;
//Adding qty in receiving
$collection=Mage::getModel('inventory/inventory')->getCollection()->addFieldToFilter('stock_id',$stockId);
foreach ($collection as $item){
	$productid = Mage::getModel('catalog/product')->load(trim($item->getProductId()));
	$productStock=Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($productid,$stockId);
	$productStock->setQty($productStock->getQty()+$item->getAddedQty())->save();
}
//Addding qty in transfer
$collection=Mage::getModel('inventory/transfer')->getCollection()->addFieldToFilter('transfer_to',$stockId);
foreach ($collection as $item){
	$productid = Mage::getModel('catalog/product')->load(trim($item->getProductId()));
	$productStock=Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($productid,$stockId);
	$productStock->setQty($productStock->getQty()+$item->getQty())->save();
}
//Subtracting qty in transfer
$collection=Mage::getModel('inventory/transfer')->getCollection()->addFieldToFilter('transfer_from',$stockId);
foreach ($collection as $item){
	$productid = Mage::getModel('catalog/product')->load(trim($item->getProductId()));
	$productStock=Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($productid,$stockId);
	$productStock->setQty($productStock->getQty()-$item->getQty())->save();
}
echo "Done";