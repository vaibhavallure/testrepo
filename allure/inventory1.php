<?php
require_once('../app/Mage.php'); 
umask(0);
Mage::app();
$stockId=2;
echo "<pre>";
$count=1;
//Subtracting  purchased qty 
$purchasedItems=Mage::getModel('allure_londoninventory/inventory')->getCollection();
foreach ($purchasedItems as $item){
	$productid = Mage::getModel('catalog/product')->getIdBySku(trim($item->getSku()));
	$productStock=Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($productid,$stockId);
	if($productStock->getId()){
	try {
		$productStock->setQty($productStock->getQty()-$item->getQty())->save();
	} catch (Exception $e) {
		Mage::log("Exception:".$e->getMessage(),Zend_log::DEBUG,'london_inventory',true);
	}
	Mage::log("Count:".$count,Zend_log::DEBUG,'london_inventory',true);
	Mage::log("Subtracting  purchased qty:",Zend_log::DEBUG,'london_inventory',true);
	Mage::log("ProductId:".$productid,Zend_log::DEBUG,'london_inventory',true);
	$count++;
  }
}
Mage::log("****************************************",Zend_log::DEBUG,'london_inventory',true);
$count=1;
$from_date = date("Y-m-d 00:00:00",strtotime('03/31/2017'));
//Adding qty in receiving
$collection=Mage::getModel('inventory/inventory')->getCollection()->addFieldToFilter('stock_id',$stockId);
$from_date = date("Y-m-d 00:00:00",strtotime('03/31/2017'));
$collection->addFieldToFilter('updated_at',array('gt' =>$from_date));
foreach ($collection as $item){
	//	$productid = Mage::getModel('catalog/product')->load($item->getProductId());
	$productid=$item->getProductId();
	Mage::log("before update -:".$productid,Zend_log::DEBUG,'london_inventory',true);
	$productStock=Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($productid,$stockId);
	if($productStock->getId()){
		try {
			$productStock->setQty($productStock->getQty()+$item->getAddedQty())->save();
		} catch (Exception $e) {
			Mage::log("Exception:".$e->getMessage(),Zend_log::DEBUG,'london_inventory',true);
		}
		Mage::log("Count:".$count,Zend_log::DEBUG,'london_inventory',true);
		Mage::log("Adding qty in receiving:",Zend_log::DEBUG,'london_inventory',true);
		Mage::log("ProductId:".$productid,Zend_log::DEBUG,'london_inventory',true);
		Mage::log("Previous Qty:".$productStock->getQty(),Zend_log::DEBUG,'london_inventory',true);
		Mage::log("Now Current Qty:".($productStock->getQty()+$item->getAddedQty()),Zend_log::DEBUG,'london_inventory',true);
		$count++;
	}
}
Mage::log("****************************************",Zend_log::DEBUG,'london_inventory',true);
$count=1;
//Addding qty in transfer
$collection=Mage::getModel('inventory/transfer')->getCollection()->addFieldToFilter('transfer_to',$stockId);
$from_date = date("Y-m-d 00:00:00",strtotime('03/31/2017'));
$collection->addFieldToFilter('updated_at',array('gt' =>$from_date));
foreach ($collection as $item){
	$productid=$item->getProductId();
	Mage::log("before update -:".$productid,Zend_log::DEBUG,'london_inventory',true);
	$productStock=Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($productid,$stockId);
	if($productStock->getId()){

		try {
			$productStock->setQty($productStock->getQty()+$item->getQty())->save();
		} catch (Exception $e) {
			Mage::log("Exception:".$e->getMessage(),Zend_log::DEBUG,'london_inventory',true);
		}
		Mage::log("Count:".$count,Zend_log::DEBUG,'london_inventory',true);
		Mage::log("Adding qty in transfer:",Zend_log::DEBUG,'london_inventory',true);
		Mage::log("ProductId:".$productid,Zend_log::DEBUG,'london_inventory',true);
		Mage::log("Previous Qty:".$productStock->getQty()." #Id:".$id,Zend_log::DEBUG,'london_inventory',true);
		Mage::log("Now Current Qty:".($productStock->getQty()+$item->getAddedQty()),Zend_log::DEBUG,'london_inventory',true);
		$count++;
	}
}
Mage::log("****************************************",Zend_log::DEBUG,'london_inventory',true);
$count=1;
//Subtracting qty in transfer
$collection=Mage::getModel('inventory/transfer')->getCollection()->addFieldToFilter('transfer_from',$stockId);
$from_date = date("Y-m-d 00:00:00",strtotime('03/31/2017'));
$collection->addFieldToFilter('updated_at',array('gt' =>$from_date));
foreach ($collection as $item){
	$productid=$item->getProductId();
	Mage::log("before update -:".$productid,Zend_log::DEBUG,'london_inventory',true);
	$productStock=Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($productid,$stockId);
	if($productStock->getId()){
		try {
			$productStock->setQty($productStock->getQty()-$item->getQty())->save();
		} catch (Exception $e) {
			Mage::log("Exception:".$e->getMessage(),Zend_log::DEBUG,'london_inventory',true);
		}
		Mage::log("Count:".$count,Zend_log::DEBUG,'london_inventory',true);
		Mage::log("Subtracting qty in transfer:",Zend_log::DEBUG,'london_inventory',true);
		Mage::log("ProductId:".$productid,Zend_log::DEBUG,'london_inventory',true);
		Mage::log("Previous Qty:".$productStock->getQty(),Zend_log::DEBUG,'london_inventory',true);
		Mage::log("Now Current Qty:".($productStock->getQty()-$item->getAddedQty()),Zend_log::DEBUG,'london_inventory',true);
		$count++;
	}
}
echo "Done";
