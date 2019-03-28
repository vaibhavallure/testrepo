<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$logFile = "category.log";
$logErrorFile = "categoryError.log";
$skuIndex = 0;
$cat1Index = 1;
$cat2Index = 2;
$cat3Index = 3;
$cat4Index = 4;
$cat5Index = 5;
$cat6Index = 6;
$index=0;

$productModel = Mage::getSingleton('catalog/product');
$csv = Mage::getBaseDir('var').DS."teamwork".DS.'category.csv';
$io = new Varien_Io_File();
$io->streamOpen($csv, 'r');
$counter=0;
while($csvData = $io->streamReadCsv()){
    $categoryIds=array();
    $sku = trim($csvData[$skuIndex]);
    $cat1 = trim($csvData[$cat1Index]);
    $cat2 = trim($csvData[$cat2Index]);
    $cat3 = trim($csvData[$cat3Index]);
    $cat4 = trim($csvData[$cat4Index]);
    $cat5 = trim($csvData[$cat5Index]);
    $cat6 = trim($csvData[$cat6Index]);
    if(!empty($cat1)){
        $counter=0;
        $cat1=explode('/', $cat1);
        $counter=count($cat1);
        //Last element
        $cat1=explode('.', $cat1[$counter-1]);
        $url=$cat1[0];
        //echo $url;
        $category = Mage::getModel('catalog/category')
        ->getCollection()
        ->addAttributeToFilter('url_key', $url)
        ->getFirstItem();
        if($category->getId()){
            array_push($categoryIds,$category->getId()) ;
        }else {
            Mage::log($sku.":: CAT NOT FOUND-:".$csvData[$cat1Index],Zend_log::DEBUG,$logErrorFile,true);
        }
        
    }
    if(!empty($cat2)){
        $counter=0;
        $cat2=explode('/', $cat2);
        $counter=count($cat2);
        //Last element
        $cat2=explode('.', $cat2[$counter-1]);
        $url=$cat2[0];
        //echo $url;
        $category = Mage::getModel('catalog/category')
        ->getCollection()
        ->addAttributeToFilter('url_key', $url)
        ->getFirstItem();
        if($category->getId()){
            array_push($categoryIds,$category->getId()) ;
        }else {
            Mage::log($sku.":: CAT NOT FOUND-:".$csvData[$cat2Index],Zend_log::DEBUG,$logErrorFile,true);
        }
        
    }
    if(!empty($cat3)){
        $counter=0;
        $cat3=explode('/', $cat3);
        $counter=count($cat3);
        //Last element
        $cat3=explode('.', $cat3[$counter-1]);
        $url=$cat3[0];
        //echo $url;
        $category = Mage::getModel('catalog/category')
        ->getCollection()
        ->addAttributeToFilter('url_key', $url)
        ->getFirstItem();
        if($category->getId()){
            array_push($categoryIds,$category->getId()) ;
        }
        else {
            Mage::log($sku.":: CAT NOT FOUND-:".$csvData[$cat3Index],Zend_log::DEBUG,$logErrorFile,true);
        }
        
    }
    if(!empty($cat4)){
        $counter=0;
        $cat4=explode('/', $cat4);
        $counter=count($cat4);
        //Last element
        $cat4=explode('.', $cat4[$counter-1]);
        $url=$cat4[0];
        //echo $url;
        $category = Mage::getModel('catalog/category')
        ->getCollection()
        ->addAttributeToFilter('url_key', $url)
        ->getFirstItem();
        if($category->getId()){
            array_push($categoryIds,$category->getId()) ;
        }else {
            Mage::log($sku.":: CAT NOT FOUND-:".$csvData[$cat4Index],Zend_log::DEBUG,$logErrorFile,true);
        }
        
    }
    if(!empty($cat5)){
        $counter=0;
        $cat5=explode('/', $cat5);
        $counter=count($cat5);
        //Last element
        $cat5=explode('.', $cat5[$counter-1]);
        $url=$cat5[0];
        //echo $url;
        $category = Mage::getModel('catalog/category')
        ->getCollection()
        ->addAttributeToFilter('url_key', $url)
        ->getFirstItem();
        if($category->getId()){
            array_push($categoryIds,$category->getId()) ;
        }else {
            Mage::log($sku.":: CAT NOT FOUND-:".$csvData[$cat5Index],Zend_log::DEBUG,$logErrorFile,true);
        }
        
    }
    if(!empty($cat6)){
        $counter=0;
        $cat6=explode('/', $cat6);
        $counter=count($cat6);
        //Last element
        $cat6=explode('.', $cat6[$counter-1]);
        $url=$cat6[0];
        //echo $url;
        $category = Mage::getModel('catalog/category')
        ->getCollection()
        ->addAttributeToFilter('url_key', $url)
        ->getFirstItem();
        if($category->getId()){
            array_push($categoryIds,$category->getId()) ;
        }else {
            Mage::log($sku.":: CAT NOT FOUND-:".$csvData[$cat6Index],Zend_log::DEBUG,$logErrorFile,true);
        }
        
    }
    $id = $productModel->getIdBySku($sku);
    try {
        if($id){
            $index++;
            $product=Mage::getModel('catalog/product')->load($id);
            $product->setCategoryIds($categoryIds);
            $product->save();
        }else{
            Mage::log(" SKU NOT FOUND-:".$sku,Zend_log::DEBUG,$logErrorFile,true);
        }
    } catch (Exception $e) {
        Mage::log(" Exception Occured::".$sku."  Message:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
    }
   
    Mage::log($index."- SKU -:".$sku.json_encode($categoryIds),Zend_log::DEBUG,$logFile,true);
    //Mage::log("IDS  -:".json_encode($categoryIds)." == teamwork_id-:".$eid,Zend_log::DEBUG,$logFile,true);
    
}
die("fiunish");
