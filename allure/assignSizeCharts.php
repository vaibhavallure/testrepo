<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
$app = Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
$cntr=0;
$collection=Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('type_id', array('eq' => 'configurable'));
foreach ($collection as $product){
    
    $product=Mage::getModel('catalog/product')->load($product->getId());
    $sizeChartId='';
    
    if(strpos($product->getName(), '3/16')!== false) {
        $sizeChartId=11;
        
    }elseif(strpos($product->getName(), '1/4')!== false || strpos($product->getName(), '6.5mm')!== false){
        $sizeChartId=12;
        
    }elseif(strpos($product->getName(), '5/16')!== false){
        $sizeChartId=13;
        
    }elseif(strpos($product->getName(), '3/8')!== false || strpos($product->getName(), '9.5mm')!== false){
        $sizeChartId=14;
        
    }elseif(strpos($product->getName(), '7/16')!== false || strpos($product->getName(), '11mm')!== false){
        $sizeChartId=15;
        
    }elseif(substr($product->getSku(), 0, 1)=='V' || substr($product->getSku(), 0, 1)=='v'){
        $sizeChartId=7;
        
    }elseif(substr($product->getSku(), 0, 1)=='K' || substr($product->getSku(), 0, 1)=='k'){
        $sizeChartId=8;
        
    }elseif(substr($product->getSku(), 0, 1)=='F' || substr($product->getSku(), 0, 1)=='f'){
        $sizeChartId=6;
    }
    if(!empty($sizeChartId))
        $product->setSizeChart($sizeChartId);
    $product->save();
    $cntr++;
    Mage::log($cntr.'-'."SKU::".$product->getSku().'-- NAME::'.$product->getName(),Zend_log::DEBUG,'sizecharts.log',true);
    Mage::log("ID::".$product->getId().'-- CHART ID::'.$sizeChartId,Zend_log::DEBUG,'sizecharts.log',true);
    
}
die("finished");