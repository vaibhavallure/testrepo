<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);

ini_set('memory_limit', '-1');

$helper         = Mage::helper('alluremultistore_catalog');
$stockIds       = $helper->getStockIds();
$storeIds 		= $helper->getStoreIdsByUsingStockIds();

$collection = Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('type_id', array('eq' => 'configurable'));
/* $collection->addAttributeToFilter('entity_id',
    array(
        'gteq' => 12
    ))
    
    ->addAttributeToFilter('entity_id', array(
        'lteq' => 12
    )); */
    
$io = new Varien_Io_File();
$path = Mage::getBaseDir('var') . DS . 'export' ;
$name = "product_price_store_".round(microtime(true) * 1000)."_".date(Y_m_d);
$file = $path . DS . $name . '.csv';
$io->setAllowCreateFolders(true);
$io->open(array('path' => $path));
$io->streamOpen($file, 'w+');
$io->streamLock(true);
//$header = array("sku","main","london","qty");
//$io->streamWriteCsv($header);
    
$header = array('sku'=>'sku');
foreach ($stockIds as $stockId) {
    $website = Mage::getModel("core/website")->load($stockId,'stock_id');
    $code = $website->getCode();
    if(!(preg_match("/counterpoint/", $code))){
        $header[$code] = $code;
    }
    if($website->getWebsiteId() != 1 && !(preg_match("/counterpoint/", $code))){
        $header[$code."_expect"] = $code."_expect";
    }
}

$io->streamWriteCsv($header);

foreach ($collection as $product){
      $websites = $product->getWebsiteIds();
      $data = array("sku"=>$product->getSku());
      $basePrice = $product->getPrice();
      foreach ($stockIds as $stockId) {
        $website = Mage::getModel("core/website")->load($stockId,'stock_id');
        $websiteId = $website->getWebsiteId();
        $code = $website->getCode();
        $priceRule   = $website->getWebsitePriceRule();
        $expectPrice = $priceRule * $basePrice;
        if(in_array($websiteId, $websites)){
            $_product = Mage::getModel('catalog/product')
                ->setStoreId($storeIds[$stockId])
                ->load($product->getId());
            $batchPrice  = $_product->getData('price');
            if(!(preg_match("/counterpoint/", $code))){
                $data[$code] = $batchPrice;
            }
            if($website->getWebsiteId() != 1 && !(preg_match("/counterpoint/", $code))){
                $data[$code."_expect"] = ceil($expectPrice);
            }
         }else{
             if(!(preg_match("/counterpoint/", $code))){
                $data[$code] = 0;
             }
             if($website->getWebsiteId() != 1 && !(preg_match("/counterpoint/", $code))){
                 $data[$code."_expect"] = ceil($expectPrice);
             }
         }
      }
      $io->streamWriteCsv($data);
      $data = array();
}

