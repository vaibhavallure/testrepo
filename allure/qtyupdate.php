<?php 
/*
 * 	Update all product quantity as any number means same quantity
 *	to all products are save by this script. 
 *  It requires following params
 *  @param int store - It indicates that which store qty is changed.
 *  @param string key & string type
 *  Only those user change the qty of product that knows the type and key value.
 *	@copyright by Allure inc,2017
 *
 */


?>

<?php
require_once('../app/Mage.php'); 
umask(0);
Mage::app();

$store 	= $_GET['store'];
$type 	= $_GET['type'];
$key 	= $_GET['key'];

if(empty($store))
	die("Please Provide Store Id");

if(empty($type) || empty($key))
	die("You can't proceed further operation. Sorry your access is wrong.");

if(!($type == "allure" && $key == "mariatash"))
	die("You can't proceed further operation. Sorry your access is wrong.");

$websiteId = Mage::getModel('core/store')->load($store)->getWebsiteId();
$stockId = Mage::getModel('core/website')->load($websiteId)->getStockId();

if(!$stockId)
	die("Wrong Inventory.You cannot perform the opration.");

$collection = Mage::getResourceModel('catalog/product_collection')
	->addStoreFilter($stockId)->getAllIds();

$resource     = Mage::getSingleton('core/resource');
$writeAdapter = $resource->getConnection('core_write');

try{
	$io = new Varien_Io_File();
	$path = Mage::getBaseDir('var') . DS . 'export' ; 
	$name = "product_update_".round(microtime(true) * 1000)."_".date(Y_m_d)."_stockid_".$stockId."_store_".$store; 
	$file = $path . DS . $name . '.csv';
	$io->setAllowCreateFolders(true);
	$io->open(array('path' => $path));
	$io->streamOpen($file, 'w+');
	$io->streamLock(true);
	$header = array("product_id","stock_id","store","qty");
	$io->streamWriteCsv($header);
	$writeAdapter->beginTransaction();
	foreach ($collection as $productId) {
		$stock = Mage::getModel('cataloginventory/stock_item')
		->loadByProductAndStock($productId,$stockId);
		if($stock->getId()){
			$qty = $stock->getQty();
			$data = array("product_id"=>$productId,"stock_id"=>$stockId,"store"=>$store,"qty"=>$qty);
			$io->streamWriteCsv($data);
		}
	}
	$writeAdapter->commit();
}catch (Exception $e){
	Mage::log("Exception-:".$e->getMessage(),Zend_Log::DEBUG,$logFileName,true);
}

$logFileName = "update_product_qty_by_store_".$store;
try{
	$writeAdapter->beginTransaction();
	foreach ($collection as $productId){
		$stock = Mage::getModel('cataloginventory/stock_item')
			->loadByProductAndStock($productId,$stockId);
		if($stock->getId()){
			$qty = $stock->getQty();
			if($qty > 0 || $qty < 0){
				$stock->setQty(0)->save();
				echo "<br> Stock Updated: Stock Id-".$stock->getId()."#Qty-".$qty;
				Mage::log("Stock Updated: Stock Id-".$stock->getId()."#Qty-".$qty,Zend_Log::DEBUG,$logFileName,true);
			}else{ 
				echo "<br> Stock Not Updated: Stock Id-".$stock->getId()."#Qty-".$qty;
				Mage::log("Stock Not Updated: Stock Id-".$stock->getId()."#Qty-".$qty,Zend_Log::DEBUG,$logFileName,true);
			}
		}else{
			echo "<br> Inventory not Available for Product Id-".$productId;
			Mage::log("Inventory not Available for Product Id-".$productId,Zend_Log::DEBUG,$logFileName,true);
		}
	}
	$writeAdapter->commit();
}catch (Exception $e) {
	$writeAdapter->rollback();
	Mage::log("Exception-:".$e->getMessage(),Zend_Log::DEBUG,$logFileName,true);
}
	
die("Operation of Quantity Update end...");
	
	