<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$logFile = "not_match_eid.log";
$skuIndex = 0;
$productModel = Mage::getSingleton('catalog/product');
$csv = Mage::getBaseDir('var').DS."teamwork".DS.'NOTMATCHEID.csv';
$io = new Varien_Io_File();
$io->streamOpen($csv, 'r');
$counter=0;
$activeSku=array();
$activeSkuMissingTW=array();
$notActiveSku=array();
$notFound=array();
$activeSkuWithEID=array();

while($csvData = $io->streamReadCsv()){
    $counter++;
    $sku = trim($csvData[$skuIndex]);
    $id = $productModel->getIdBySku($sku);
    if($id){
        $product=Mage::getModel('catalog/product')->load($id);
        if($product->getStatus()){
            $activeSku[]=$sku;
            if(empty($product->getTeamworkId()))
                $activeSkuMissingTW[]=$sku;
           
        }else{
            $notActiveSku[]=$sku;
        }
        
    }else {
        $notFound[]=$sku;
    }
}
Mage::log(" Active SKUs".json_encode($activeSku),Zend_log::DEBUG,$logFile,true);
Mage::log(" Active SKUs with Missing EID".json_encode($activeSkuMissingTW),Zend_log::DEBUG,$logFile,true);
Mage::log(" In Active SKUs".json_encode($notActiveSku),Zend_log::DEBUG,$logFile,true);
Mage::log(" Not Found SKUs".json_encode($notFound),Zend_log::DEBUG,$logFile,true);

die("Finish");