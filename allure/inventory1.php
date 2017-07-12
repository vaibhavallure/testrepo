<?php
require_once('../app/Mage.php'); 
umask(0);
Mage::app();
$stockId=2;
echo "<pre>";
//Subtracting  purchased qty 
$purchasedItems=Mage::getModel('allure_londoninventory/inventory')->getCollection();
foreach ($purchasedItems as $item){
	$productid = Mage::getModel('catalog/product')->getIdBySku(trim($item->getSku()));
	$productStock=Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($productid,$stockId);
	$productStock->setQty($productStock->getQty()-$item->getQty())->save();
}
echo "Done";