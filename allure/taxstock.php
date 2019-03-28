<?php
require_once ('../app/Mage.php');
umask(0);
Mage::app();

$name = $_GET['file'];

if (empty($name))
    die("Please provide file path");

$skuIndex = 1;
$qtyIndex = 2;
$prodCount = 0;
$csv = Mage::getBaseDir('var') . DS . "priceImport" . DS . $name;
$productNotFound = array();
$io = new Varien_Io_File();
$io->streamOpen($csv, 'r');

while ($csvData = $io->streamReadCsv()) {
    if (count($csvData) < 2) {
        continue;
    }
    $sku = trim($csvData[$skuIndex]);
    $received = trim($csvData[$qtyIndex]);
    $itemData=Mage::getModel('allure_londoninventory/inventory')->load($sku,'sku');
    $id=$itemData->getId();
    if(isset($id)){
        $itemData->setAddedQty($received)->save();
    }else {
        $itemData=Mage::getModel('allure_londoninventory/inventory');
        $itemData->setAddedQty($received);
        $itemData->setSku($sku);
        $itemData->save();
    }
    echo $sku.'----'.$received;
    echo "<br>";
}
        
     
        