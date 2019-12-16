<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);

$escapeWebsiteArr = array("base" , "counterpoint");

$storeId    = $_GET['store_id'];
$store      = Mage::getModel('core/store')->load($storeId);
$websiteId  = $store->getWebsiteId();
$website    = Mage::getModel('core/website')->load($websiteId);
$websiteCode = $website->getCode();

if (empty($storeId)){
    die("Please specify the store_id.");
}

if (($storeId == 1) || ($storeId == 12)){
    die("Incorrect store_id. Don't use these store.");
}

if (($websiteCode == "base") || ($websiteCode == "counterpoint")){
    die("Invalid website.!!! Please mention correct website_id.");
}

$productIds = Mage::getResourceModel('catalog/product_collection')
              ->addWebsiteFilter($websiteId)
              ->getAllIds();

try{
Mage::getResourceSingleton('catalog/product_action')
   ->updateAttributes($productIds,array ('backorder_time' => ""), $storeId);
}catch (Exception $e){
    echo "<pre>";
    print_r($e->getMessage());
}

die("Complete...");