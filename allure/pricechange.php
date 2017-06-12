<?php
require_once('../app/Mage.php'); 
umask(0);
Mage::app();

$store 	= $_GET['store'];
$name 	= $_GET['file'];
if(empty($store))
	die("Please provide store id");

if(empty($name))
	die("Please provide file path");
	
$skuIndex = 0;
$priceIndex = 1;

$prodCount=0;
$csv = Mage::getBaseDir('var').DS."priceImport".DS.$name;


$io = new Varien_Io_File();
$productIdsByPrice = array();
$productModel = Mage::getSingleton('catalog/product');
$io->streamOpen($csv, 'r');
while($csvData = $io->streamReadCsv()){
	if (count($csvData) < 2) { 
		continue;
	}
	$sku = trim($csvData[$skuIndex]);
	$id = $productModel->getIdBySku($sku);
	if ($id) {
		$price = trim($csvData[$priceIndex]);
		if (!isset($productIdsByPrice[$price])) {
			$productIdsByPrice[$price] = array();
		}
		$productIdsByPrice[$price][] = $id;
	}
}

$resource     = Mage::getSingleton('core/resource');
$writeAdapter   = $resource->getConnection('core_write');

try{
	$writeAdapter->beginTransaction();
	$recordIndex = 1;
	foreach ($productIdsByPrice as $price => $ids) {
		/* Mage::getSingleton('catalog/product_action')->updateAttributes(
				$ids, 
				array('price' => $price), $store ); */
		
		Mage::getResourceSingleton('catalog/product_action')->updateAttributes(
				$ids,array('price' => $price), $store );
		
				$prodCount=$prodCount+count($ids);
				Mage::log("store:".$store,Zend_log::DEBUG,'priceupdate',true);
				Mage::log("Price:".$price." #Ids:".json_encode($ids),Zend_log::DEBUG,'priceupdate',true);
				Mage::log("Product Count:".$prodCount,Zend_log::DEBUG,'priceupdate',true);
				if (($recordIndex % 250) == 0) {
				    $writeAdapter->commit();
	                $writeAdapter->beginTransaction();
				}
				$recordIndex += 1;
	}
$writeAdapter->commit();
}catch (Exception $e) {
	$writeAdapter->rollback();
}
	
die("Operation end...");
	
	