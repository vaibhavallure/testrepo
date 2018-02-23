<?php
require_once('../app/Mage.php'); 
umask(0);
Mage::app();

$mode 	    = $_GET['mode'];
$storeId 	= $_GET['store'];
$type 	    = $_GET['type'];
$id 	    = $_GET['id'];
$from 	    = (int) $_GET['from'];
$to 	    = (int) $_GET['to'];

$products = array();

if (empty($storeId) || $storeId == 1) {
	die("Invalid Store Selected");
}

if (empty($mode)) {
	die("Please provide update mode");
}

if (empty($type)) {
	die("Please provide update type");
}

try {
    $store = Mage::getModel('core/store')->load($storeId);
    $websiteId = $store->getWebsiteId();
    $website = Mage::getModel('core/website')->load($websiteId);
    $stockId = $website->getStockId();
    	
    Mage::log('storeId::'.$storeId, Zend_Log::DEBUG, 'sure.log', true);
    Mage::log('websiteId::'.$websiteId, Zend_Log::DEBUG, 'sure.log', true);
    Mage::log('stockId::'.$stockId, Zend_Log::DEBUG, 'sure.log', true);
    
    if (!$stockId) {
    	die("Wrong Inventory.You cannot perform the opration.");
    }
    
    if ($type == 'manual') {
        if (!isset($id) || empty($id)) {
            die("Please provide id");
        }
        
        $products = explode(",", $id);
        
        $products = array_unique($products);
    } else {
        $products = Mage::getResourceModel('catalog/product_collection')->addStoreFilter($stockId)->getAllIds();
    }
    
    //sort($products);

    $productModel = Mage::getSingleton('catalog/product');
    
    $resource     = Mage::getSingleton('core/resource');
    $writeAdapter   = $resource->getConnection('core_write');
	
	$priceRule = $website->getWebsitePriceRule();
	
	Mage::log('priceRule::'.$priceRule, Zend_Log::DEBUG, 'sure.log', true);
	
	$selectedProducts = array();
	
	foreach ($products as $productId) {
	    
	    if ($from || $to) {
	        if ($from && $productId < $from) {
	            continue;
	        }
	        if ($to && $productId > $to) {
	            continue;
	        }
	    }
	
	    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
	    
	    $product = Mage::getSingleton('catalog/product')->load($productId);
	    
	    $oldPrice = $product->getPrice();
	    $newPrice = $oldPrice * $priceRule;
	
    	Mage::log('productId::'.$productId, Zend_Log::DEBUG, 'sure.log', true);
    	Mage::log('oldPrice::'.$oldPrice, Zend_Log::DEBUG, 'sure.log', true);
    	Mage::log('newPrice::'.$newPrice, Zend_Log::DEBUG, 'sure.log', true);
    	
    	$selectedProducts[$newPrice][] = $productId;
	} 
    
	$writeAdapter->beginTransaction();
	$recordIndex = 0;
	
	foreach ($selectedProducts as $price => $productIds) {
		
		$recordIndex += 1;
	    
    	Mage::log('RUNNING Price::'.$price, Zend_Log::DEBUG, 'sure.log', true);
    	Mage::log('RUNNING Ids::'.json_encode($productIds), Zend_Log::DEBUG, 'sure.log', true);
    	
	    Mage::getResourceSingleton('catalog/product_action')->updateAttributes($productIds, array ('price' => $price), $storeId );
	    
		if (($recordIndex % 100) == 0) {
		    $writeAdapter->commit();
            $writeAdapter->beginTransaction();
            Mage::log('COMMIT COUNT :: '.$recordIndex, Zend_Log::DEBUG, 'sure.log', true);
		}
	}
	
    Mage::log('DONE COUNT :: '.$recordIndex, Zend_Log::DEBUG, 'sure.log', true);
	
    $writeAdapter->commit();
} catch (Exception $e) {
	$writeAdapter->rollback();
}
	
die("DONE");
	
