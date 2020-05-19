<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

$fileName 	= $_GET['file'];
$showSkus = $_GET["show_sku"];
    
if(empty($fileName)){
    die("Please provide file path");
}

$csv = Mage::getBaseDir('var') . DS ."allure". DS . "PostLength" . DS . $fileName;
$io = new Varien_Io_File();
$io->streamOpen($csv, 'r');

//Read header 
$csvData = $csvData = $io->streamReadCsv();
if(count($csvData) == 3){
    foreach ($csvData as $headerName){
        if(!preg_match("/mm/", $headerName)){
            die("Invalid header format of post length data csv");
        }
    }
}

//Prepare product array data
$postLengthProductArray = array();
$productModel = Mage::getSingleton('catalog/product');
while($csvData = $io->streamReadCsv()){
    if (count($csvData) < 2) {
        continue;
    }
    foreach ($csvData as $sku){
        if(isset($sku) && !empty($sku)){
            $productId = $productModel->getIdBySku($sku);
            if(!$productId){
                continue;
            }
            $postLengthProductArray[$productId] = "'".$sku."'";
        }
    }
}

$isShowsSku = false;
if(isset($showSkus) && !empty($showSkus)){
    if(strtolower($showSkus) == strtolower("yes")){
        echo "<br/>Products SKU";
        echo "<br/>" . implode(",", $postLengthProductArray);
        echo "</br>";
        $isShowsSku = true;
    }
}

if($isShowsSku){
    die("<br/>Finish");
}


//update post length product status data
try{
    $resource       = Mage::getSingleton('core/resource');
    $writeAdapter   = $resource->getConnection('core_write');
    
    $recordIndex = 1;
    $updatedCount = 0;
    $writeAdapter->beginTransaction();
    $isPostLength = 1;
    foreach ($postLengthProductArray as $productId => $sku){
        Mage::getResourceSingleton('catalog/product_action')->updateAttributes(
            array($productId),
            array('is_post_length_product' => $isPostLength),
            Mage_Core_Model_App::ADMIN_STORE_ID
            );
        if (($recordIndex % 250) == 0) {
            $writeAdapter->commit();
            $writeAdapter->beginTransaction();
        }
        $recordIndex += 1;
        $updatedCount += 1;
    }
    $writeAdapter->commit();
}catch (Exception $e){
    $writeAdapter->rollback();
    echo "<br/>".$e->getMessage();
}
echo "<br/>Number of products = " . count($postLengthProductArray);
echo "<br/>Number of updated products = {$updatedCount}";
die("<br/>Finish");

