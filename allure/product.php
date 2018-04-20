<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
$name = $_GET['file'];
if (empty($name))
//    die("Please provide file path");

$teamworIndex = 1;
$skuIndex = 0;


$prodCount = 0;
$csv = Mage::getBaseDir('var') . DS . "teamwork" . DS .'teamwork1.csv';
$productNotFound = array();

$io = new Varien_Io_File();
$productIdsByTeamwork = array();
$productModel = Mage::getSingleton('catalog/product');
$io->streamOpen($csv, 'r');

while ($csvData = $io->streamReadCsv()) {
    if (count($csvData) < 2) {
        continue;
    }
    $sku = trim($csvData[$skuIndex]);
    $teamworkId=($csvData[$teamworIndex]);
    $id = $productModel->getIdBySku($sku);
    
    if ($id) {
        $productIdsByTeamwork[$id]=$teamworkId;
        
    } else {
        //$productNotFound[$sku] = $teamworkId;
        $productNotFound[]=array($teamworkId,$sku);
    }
}

$resource     = Mage::getSingleton('core/resource');
$writeAdapter   = $resource->getConnection('core_write');

try{
    $writeAdapter->beginTransaction();
    $recordIndex = 1;
    foreach ($productIdsByTeamwork as $id => $teamworkId) {
        /* Mage::getSingleton('catalog/product_action')->updateAttributes(
         $ids,
         array('price' => $price), $store ); */
        
       /*  Mage::getResourceSingleton('catalog/product_action')->updateAttributes(
            $id,array('teamwork_id' => $teamworkId), 1); */
        $product=Mage::getModel('catalog/product')->load($id)->setTeamworkId($teamworkId);
        $product->save();
        $prodCount=$prodCount+count($id);
       // Mage::log("Product Id:".$id." ##TEameork ID: ".$teamworkId,Zend_log::DEBUG,'teamwork_id.log',true);
        Mage::log("Product Count:".$prodCount,Zend_log::DEBUG,'teamwork_id.log',true);
        if (($recordIndex % 2000) == 0) {
            $writeAdapter->commit();
            $writeAdapter->beginTransaction();
        }
        $recordIndex += 1;
    }
    $writeAdapter->commit();
}catch (Exception $e) {
    $writeAdapter->rollback();
}
$writeAdapter->commit();

header('Content-Disposition: attachment; filename=NotFoundSku.csv');
header('Content-type: text/csv');
header('Pragma: no-cache');
header('Expires: 0');
$file = fopen('php://output', 'w');
//fputcsv($file, array('EID','SKU'));
Mage::log("Products not found:".json_encode($productNotFound),Zend_log::DEBUG,'teamwork_id.log',true);
foreach ($productNotFound as $row)
{
    fputcsv($file, $row);
}
die("Operation end...");
        
        
