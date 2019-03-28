<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$logFile = "tw_update_eid.log";
$logFileError = "tw_update_eid_error.log";
$skuIndex = 0;
$eidIndex = 1;
$productModel = Mage::getSingleton('catalog/product');
$csv = Mage::getBaseDir('var').DS."teamwork".DS.'EID - Sheet1.csv';
$io = new Varien_Io_File();
$io->streamOpen($csv, 'r');
$counter=0;
while($csvData = $io->streamReadCsv()){
    if (count($csvData) < 2) {
        continue;
    }
    $counter++;
    $sku = trim($csvData[$skuIndex]);
    $eid = trim($csvData[$eidIndex]);
    
    $id = $productModel->getIdBySku($sku);
    try {
        if($id){
            $product=Mage::getModel('catalog/product')->load($id)->setTeamworkId($eid);
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