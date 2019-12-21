<?php
require_once('app/Mage.php'); 
umask(0);
Mage::app();

$websiteIds = trim($_REQUEST['websites']);
$websiteId = trim($_REQUEST['target_website']);
$storeId = trim($_REQUEST['target_store']);

if (!empty($websiteIds)) {
    $websiteIds = explode(',', $websiteIds);
    
    $websiteIds = array_unique($websiteIds);
    
    if (count($websiteIds) > 1) {
        try {
            $productIds = Mage::getResourceModel('catalog/product_collection')->getAllIds();
            Mage::getModel('catalog/product_website')->addProducts($websiteIds, $productIds);
        } catch (Exception $e) {
            die('FAILED');
        }
        
        if(!empty($websiteId)){
        	$webisteModel = Mage::getModel('core/website')->load($websiteId);
        	if($webisteModel->getWebsiteId()!=0){
	        	$newStockId = $webisteModel->getStockId();
	        	$priceRule = $webisteModel->getWebsitePriceRule();
	        	Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
	        	try {
			        foreach ($productIds as $_product){
			        	$product = Mage::getModel('catalog/product')->load($_product);
			        	if(!empty($storeId)){
			        		$product->setStoreId($storeId)->setPrice($product->getPrice() * $priceRule)->save();
			        		Mage::log("product id - ".$product->getId()." price updated",Zend_Log::DEBUG,'abc',true);
			        	}
			        	$stockItem = Mage::getModel('cataloginventory/stock_item')
			        		->assignProductToNewStockByScript($product,$newStockId);
			        	if(!$stockItem){
			        		$stockItem = Mage::getModel('cataloginventory/stock_item');
			        	}		
			        	$item = Mage::getModel('cataloginventory/stock_item')
			        		->assignProductToNewStockByScript($product,1);
			        	$data = $item->getData();
			        			
			        	if(array_key_exists('item_id',$data)){
			        		unset($data['item_id']);
			        	}
			        	
			        	$data[stock_id] = $newStockId;
			        	$data[website_id] = $websiteId;
			        	$stockItem->addData($data);
			        	$stockItem->save();
			        	Mage::log("Stock item id - ".$stockItem->getItemId()."  updated",Zend_Log::DEBUG,'abc',true);
			        }
	        	}
	        	catch (Exception $e) {
	        		Mage::log("Exception -".$e->getMessage(),Zend_Log::DEBUG,'abc',true);
	        	}
        }
        die('SUCCESS');
    	}
	}
}
die('NA');