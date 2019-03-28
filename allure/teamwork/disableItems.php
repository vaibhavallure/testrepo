<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$logFile = "disbaleItems.log";
$logFileError = "disbaleItems_error.log";
$skuIndex = 0;
$eidIndex = 1;
$productModel = Mage::getSingleton('catalog/product');
$csv = Mage::getBaseDir('var').DS."teamwork".DS.'disableitems.csv';
$io = new Varien_Io_File();
$io->streamOpen($csv, 'r');
$counter=0;
while($csvData = $io->streamReadCsv()){
    if (count($csvData) < 1) {
        continue;
    }
    $counter++;
    $sku = trim($csvData[$skuIndex]);
    $id = $productModel->getIdBySku($sku);
    try {
        if($id){
            $product=Mage::getModel('catalog/product')->load($id);
            $product->setStatus(2);
            $product->save();
            Mage::log($counter."::id-:".$id." SKU-:".$sku." == teamwork_id-:".$eid,Zend_log::DEBUG,$logFile,true);
        }else{
            Mage::log(" SKU NOT FOUND-:".$sku." == teamwork_id-:".$eid,Zend_log::DEBUG,$logFile,true);
        }
    } catch (Exception $e) {
        Mage::log(" Exception Occured::".$sku."  Message:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
    }
}
die("Finish");