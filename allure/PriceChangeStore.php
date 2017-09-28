<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();

$storeId 	= $_GET['store'];
$start      = $_GET['start'];
$end        = $_GET['end'];
$user       = $_GET['user'];
$mode       = $_GET['mode'];
$memory_limit = $_GET['memory_limit'];

if(!empty($memory_limit)){
    ini_set('memory_limit', $memory_limit);
}

$file       = $_GET['file'];

if(empty($user)){
    die("Provide User");
}

if(!($user == "mariatash")){
    die("Invalid user");
}

$products = array();

if (empty($storeId)){
    die("Provide store");
}

if($storeId == 1){
    die("Dont use store - 1");
}

if(empty($mode)){
    die("Provide mode");
}


try {
    $store = Mage::getModel('core/store')->load($storeId);
    $storeName = "price_change_store_".$store->getCode();
    $websiteId = $store->getWebsiteId();
    $website = Mage::getModel('core/website')->load($websiteId);
    
    if(empty($file)){
        Mage::log('store name -:'.$store->getName()." file created.", Zend_Log::DEBUG, $storeName, true);
        die("log file created");
    }
    
    Mage::app()->setCurrentStore(0);
    $products = Mage::getResourceModel('catalog/product_collection');
    if($mode == "range"){
        if(empty($start) || empty($end)){
            die("Please provide start and end");
        }
        $products = $products->addAttributeToFilter('entity_id',
            array(
                'gteq' => $start
            ))
        
         ->addAttributeToFilter('entity_id', array(
                'lteq' => $end
            ))
        ->addStoreFilter($stockId)->getAllIds();
    }else if($mode == "all"){
        $products = $products->addStoreFilter($stockId)->getAllIds();
    }else {
        die("Invalid mode");
    }

    $resource     = Mage::getSingleton('core/resource');
    $writeAdapter   = $resource->getConnection('core_write');

    $priceRule = $website->getWebsitePriceRule();
    
    Mage::log('store name -:'.$store->getName(), Zend_Log::DEBUG, $storeName, true);
    Mage::log('price rule -:'.$priceRule, Zend_Log::DEBUG, $storeName, true);
    
    $selectedProducts = array();
    
    foreach ($products as $productId) {
        
        $product = Mage::getSingleton('catalog/product')->load($productId);
        
        $oldPrice = $product->getPrice();
        $newPrice = $oldPrice * $priceRule;
        
        Mage::log('product_id-:'.$productId, Zend_Log::DEBUG, $storeName, true);
        Mage::log('old Price-:'.$oldPrice, Zend_Log::DEBUG, $storeName, true);
        Mage::log('new Price-:'.$newPrice, Zend_Log::DEBUG, $storeName, true);
        
        $selectedProducts[$newPrice][] = $productId;
    }
    
    $writeAdapter->beginTransaction();
    $recordIndex = 0;
    
    foreach ($selectedProducts as $price => $productIds) {
        
        $recordIndex += 1;
        
        Mage::log('curent price-:'.$price, Zend_Log::DEBUG, $storeName, true);
        Mage::log('current product ids-:'.json_encode($productIds), Zend_Log::DEBUG, $storeName, true);
        
        Mage::getResourceSingleton('catalog/product_action')
                ->updateAttributes($productIds, 
                    array ('price' => $price), $storeId );
        
        if (($recordIndex % 100) == 0) {
            $writeAdapter->commit();
            $writeAdapter->beginTransaction();
            Mage::log('after commit-:'.$recordIndex, Zend_Log::DEBUG, $storeName, true);
        }
    }
    
    Mage::log('Total Count-:'.$recordIndex, Zend_Log::DEBUG, $storeName, true);
    
    $writeAdapter->commit();
} catch (Exception $e) {
    $writeAdapter->rollback();
}

die("Finish");
