<?php
require_once ('../app/Mage.php');
umask(0);
Mage::app();

$store = $_GET['store'];
$name = $_GET['file'];
if (empty($store))
    die("Please provide store id");

if (empty($name))
    die("Please provide file path");

$app = Mage::app('default');
Mage::getSingleton('core/session', array(
    'name' => 'adminhtml'
));
Mage::app()->setCurrentStore(0);

$skuIndex = 0;
$stockIndex = 1;
$stockBackIndex = 2;

$websiteId = Mage::getModel('core/store')->load($store)->getWebsiteId();
$stockId = Mage::getModel('core/website')->load($websiteId)->getStockId();

$prodCount = 0;
$prodFailCount = 0;
$csv = Mage::getBaseDir('var') . DS . "stockImport" . DS . $name;

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
        Mage::log("sku:" . $sku, Zend_log::DEBUG, 'stock_script', true);
        $qty = (int) trim($csvData[$stockIndex]);
        $qtyBack = (int) trim($csvData[$stockBackIndex]);
        $qty = $qty + $qtyBack;
        $id = $productModel->getIdBySku($sku);
        if ($id) {
            if (isset($qty)) {
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($id, $stockId);
                $stock->setQty($qty)->save();
                $prodCount = $prodCount + 1;
                Mage::log("store:" . $store, Zend_log::DEBUG, 'stock_script', true);
                Mage::log("qty:" . $qty . " #Id:" . $id, Zend_log::DEBUG, 'stock_script', true);
                Mage::log("qtyBack:" . $qtyBack . " #Id:" . $id, Zend_log::DEBUG, 'stock_script', true);
                Mage::log("Product Count:" . $prodCount, Zend_log::DEBUG, 'stock_script', true);
            }
        } else {
            $prodFailCount ++;
            Mage::log("Product fail Count:" . $prodFailCount, Zend_log::DEBUG, 'stock_script', true);
            Mage::log("Product fail Sku:" . $sku, Zend_log::DEBUG, 'stock_script', true);
        }
    }
    $writeAdapter->commit();
    Mage::log("Product fail Count:" . $prodFailCount, Zend_log::DEBUG, 'stock_script', true);
    Mage::log("Product Count:" . $prodCount, Zend_log::DEBUG, 'stock_script', true);
} catch (Exception $e) {
    Mage::log("Exception - " . $e->getMessage(), Zend_log::DEBUG, 'stock_script', true);
    $writeAdapter->rollback();
}

die("Operation end...");
        
        