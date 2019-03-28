<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$logFile = "tw_update_eid.log";
$logFileError = "tw_update_eid_error.log";
$styleIndex = 0;
$att1Index = 1;
$att2Index = 2;
$att3Index = 3;
$eidIndex  = 4;
$productModel = Mage::getSingleton('catalog/product');
$csv = Mage::getBaseDir('var').DS."teamwork".DS.'duplicateeid.csv';
$io = new Varien_Io_File();
$io->streamOpen($csv, 'r');
$counter=0;
while($csvData = $io->streamReadCsv()){
    if (count($csvData) < 2) {
        continue;
    }
    
    $sku = trim($csvData[$styleIndex]);
   
    $att1 = trim($csvData[$att1Index]);
    $att2 = trim($csvData[$att2Index]);
    $att3 = trim($csvData[$att3Index]);
    
    if (! empty($att1))
        $sku = $sku . '|' . $att1;
    if (! empty($att2))
        $sku = $sku . '|' . $att2;
    if (! empty($att3))
        $sku = $sku . '|' . $att3;
    
    $eid = trim($csvData[$eidIndex]);
    
    $id = $productModel->getIdBySku($sku);
    try {
        if($id){
            $counter++;
            $product=Mage::getModel('catalog/product')->load($id)->setTeamworkId($eid);
            $product->save();
            Mage::log($counter."::id-:".$id." SKU-:".$sku." == teamwork_id-:".$eid,Zend_log::DEBUG,$logFile,true);
        }else{
            Mage::log(" SKU NOT FOUND-:".$sku." == teamwork_id-:".$eid,Zend_log::DEBUG,$logFileError,true);
        }
    } catch (Exception $e) {
        Mage::log(" Exception Occured::".$sku."  Message:".$e->getMessage(),Zend_log::DEBUG,$logFileError,true);
    }
}
die("Finish");