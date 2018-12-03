<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$logFile = "category.log";
$logErrorFile = "categoryError.log";
$skuIndex = 0;
$index=0;

$productModel = Mage::getSingleton('catalog/product');
$csv = Mage::getBaseDir('var').DS."teamwork".DS.'category.csv';
$io = new Varien_Io_File();
$io->streamOpen($csv, 'r');
$counter=0;
$csvData1 = $io->streamReadCsv();
$catCount = count($csvData1)-1;

while($csvData = $io->streamReadCsv()){
    $categoryIds=array();
    $newCategoryIds = array();
    $sku = trim($csvData[$skuIndex]);

    //if ($sku == 'ZSN5BKD') {

    for ($i=1; $i <= $catCount ; $i++) { 
        $cat = trim($csvData[$i]);
        if(!empty($cat)){
            $counter=0;
            $cat=explode('/', $cat);
            $counter=count($cat);
            //Last element
            $cat=explode('.', $cat[$counter-1]);
            $url=$cat[0];
            //echo $url ."<br>";
            $category = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToFilter('url_key', $url)
            ->getFirstItem();
            if($category->getId()){
                array_push($categoryIds,$category->getId()) ;
            }else {
                Mage::log($sku.":: CAT NOT FOUND-:".$csvData[$i],Zend_log::DEBUG,$logErrorFile,true);
            }
            
        }
    }

        $id = $productModel->getIdBySku($sku);
        $product=Mage::getModel('catalog/product')->load($id);        

        try {
            if($id){
                $index++;
                $product=Mage::getModel('catalog/product')->load($id);
                //getting old category IDs
                $oldCatId=$product->getCategoryIds();
                $newCat = array_unique(array_merge($categoryIds,$oldCatId));
                foreach ($newCat as $catid) {
                    $newCategoryIds[]=$catid;
                }

                $product->setCategoryIds($newCategoryIds);
                $product->save();
            }else{
                Mage::log(" SKU NOT FOUND-:".$sku,Zend_log::DEBUG,$logErrorFile,true);
            }
        } catch (Exception $e) {
            Mage::log(" Exception Occured::".$sku."  Message:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
        }
       
        Mage::log($index."- SKU -:".$sku.'CAT in CSV :- '.json_encode($categoryIds),Zend_log::DEBUG,$logFile,true);
        Mage::log($index."- SKU -:".$sku.'CAT in old CAT :- '.json_encode($oldCatId),Zend_log::DEBUG,$logFile,true);
        Mage::log($index."- SKU -:".$sku.'CAT in CSV+old CAT Assign :- '.json_encode($newCategoryIds),Zend_log::DEBUG,$logFile,true);
       
    //}
}
die("finish");
