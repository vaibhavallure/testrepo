<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();

$store 	= $_GET['store'];
$name 	= $_GET['file'];
$userId 	= $_GET['user'];
if(empty($store))
  die("Please provide store id");
    
if(empty($name))
  die("Please provide file path");

if(empty($userId))
  die("Please provide user Id");
        
$app = Mage::app('default');
Mage::getSingleton('core/session', array(
    'name' => 'adminhtml'
));
Mage::app()->setCurrentStore(0);

$skuIndex = 0;
$minIndex = 1;
$maxIndex = 2;

$websiteId = Mage::getModel('core/store')->load($store)->getWebsiteId();
$stockId = Mage::getModel('core/website')->load($websiteId)->getStockId();

$prodCount = 0;
$csv = Mage::getBaseDir('var') . DS . "minmaxImport" . DS . $name;

$io = new Varien_Io_File();
// an array to keep the products by price
$productIdsByStock = array();
// a product model instance
$productModel = Mage::getSingleton('catalog/product');
// read the csv
$io->streamOpen($csv, 'r');

$resource = Mage::getSingleton('core/resource');
$writeAdapter = $resource->getConnection('core_write');
$prodCount = 0;
try {
    $writeAdapter->beginTransaction();
    while ($csvData = $io->streamReadCsv()) {
        if (count($csvData) < 2) {
            continue;
        }
        $sku = trim($csvData[$skuIndex]);
        $min = trim($csvData[$minIndex]);
        $max = trim($csvData[$maxIndex]);
        
        $id = $productModel->getIdBySku($sku);
        if ($id) {
            if (isset($min) || isset($max)) {
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($id, $stockId);
                if ($stock->getItemId()) {
                 $table = $resource->getTableName('cataloginventory/stock_item');
                 $query = "update {$table} set";
                 if ($min){
                     $query .= " notify_stock_qty = '{$min}'";
                     $query .= " where product_id = '{$id}' AND stock_id = '{$stockId}'";
                     $writeAdapter->query($query);
                 }
                //$stock->setQty($qty)->save();
                $prodCount = $prodCount + 1;
                Mage::log("store:" . $store, Zend_log::DEBUG, 'minmax_script', true);
                Mage::log("Min:" . $min . " #Id:" . $id, Zend_log::DEBUG, 'minmax_script', true);
                Mage::log("Max:" . $max . " #Id:" . $id, Zend_log::DEBUG, 'minmax_script', true);
                Mage::log("Product Count:" . $prodCount, Zend_log::DEBUG, 'minmax_script', true);
                
                $product = Mage::getModel('catalog/product')->setStoreId($stockId)->load($id);
                $model = Mage::getModel('inventory/minmaxlog');
                $model->setProductId($product->getId());
                $model->setOldMin($stock->getNotifyStockQty());
                if ($min)
                    $model->setMin($min);
                else
                    $model->setMin($stock->getNotifyStockQty());
                $model->setOldMax($product->getMaxQty());
                if ($max)
                    $model->setMax($max);
                else
                    $model->setMax($product->getMaxQty());
                
                $model->setOldCost($product->getCost());
                $model->setCost($product->getCost());
                
                $model->setUpdatedAt(date("Y-m-d H:i:s"));
                $model->setStockId($stockId);
                $model->setUserId($userId);
                $model->save();
                
                if ($max)
                    $product->setMaxQty($max);
               
                $product->save();
                }else {
                    Mage::log("Stock not found:".$sku, Zend_log::DEBUG, 'minmax_script', true);
                }
            }
        }
    }
    $writeAdapter->commit();
} catch (Exception $e) {
    Mage::log("Exception - " . $e->getMessage(), Zend_log::DEBUG, 'minmax_script', true);
    $writeAdapter->rollback();
}

die("Operation end...");