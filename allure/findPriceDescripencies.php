<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);

$storeId 	= $_GET['store'];
//$fileName   = $_GET['file'];
$mode       = $_GET['mode'];
$start      = $_GET['start'];
$end        = $_GET['end'];

$products = array();

if (empty($storeId)){
    die("Provide store");
}

try {
    $store = Mage::getModel('core/store')->load($storeId);
    $csvPath = Mage::getBaseDir('var').'/export/';
    $storeName = "price_descripency_".$store->getCode();
    $websiteId = $store->getWebsiteId();
    $website = Mage::getModel('core/website')->load($websiteId);
   
    $csvFile = $csvPath.'/'.$storeName.'.csv';
    
    header('Content-type: text/csv');
   // header("'Content-Disposition: attachment; filename='.$csvFile.'");
    header('Content-Disposition: attachment; filename='.$storeName.'.csv');
    
    // do not cache the file
    header('Pragma: no-cache');
    header('Expires: 0');
    $file = fopen('php://output', 'w');
    
    fputcsv($file, array('Product Id', 'Sku', 'Main Store Price', 'Actual Current price', 'Expected Price'));
    $data = array();
    
   /*  if(empty($file)){
        Mage::log('store name -:'.$store->getName()." file created.", Zend_Log::DEBUG, $storeName, true);
        die("log file created");
    } */
    
   
    $products = Mage::getResourceModel('catalog/product_collection');
    $products->addAttributeToFilter('status', array('eq' => 1));
    $products->setOrder('sku', 'asc');
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
           ->getAllIds();
    }else if($mode == "all"){
        $products = $products->getAllIds();
    }else {
        die("Invalid mode");
    }
    

    
    $priceRule = $website->getWebsitePriceRule();
    
    Mage::log('store name -:'.$store->getName(), Zend_Log::DEBUG, $storeName, true);
    Mage::log('price rule -:'.$priceRule, Zend_Log::DEBUG, $storeName, true);
    
    
    foreach ($products as $productId) {
        
        $product = Mage::getModel('catalog/product')->load($productId);
        $productByStore = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
        
        $mainStorePrice = $product->setStoreId(1)->getPrice();
        $storePrice = $productByStore->getPrice();
        $newPrice = round($mainStorePrice * $priceRule);
        if($newPrice!=$storePrice){
        if(round($mainStorePrice * $priceRule, 0, PHP_ROUND_HALF_UP)==round($storePrice)|| round($mainStorePrice * $priceRule, 0, PHP_ROUND_HALF_DOWN)==round($storePrice)||(round($mainStorePrice * $priceRule, 0, PHP_ROUND_HALF_DOWN)-1)==round($storePrice))
           continue;
        
        $data[]=array($productId,$product->getSku(),$mainStorePrice,$storePrice,$newPrice);
        Mage::log('product_id-:'.$productId.' ::SKU '.$product->getSku(), Zend_Log::DEBUG, $storeName, true);
        Mage::log('Main Store Price-:'.$mainStorePrice, Zend_Log::DEBUG, $storeName, true);
        Mage::log('Old Price-:'.$storePrice, Zend_Log::DEBUG, $storeName, true);
        Mage::log('Expected New Price-:'.$newPrice, Zend_Log::DEBUG, $storeName, true);
        Mage::log('---------------------------------------', Zend_Log::DEBUG, $storeName, true);
        $recordIndex += 1;
       
        }
    }

    Mage::log('Total Count-:'.$recordIndex, Zend_Log::DEBUG, $storeName, true);
    
} catch (Exception $e) {

}
foreach ($data as $row)
{
    fputcsv($file, $row);
}

exit();

die("Finish");
