<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
$app = Mage::app('default');
Mage::getSingleton('core/session', array('name' => 'adminhtml'));
Mage::app()->setCurrentStore(0);
$productModel = Mage::getSingleton('catalog/product');

$collection=Mage::getResourceModel('allure_instacatalog/feed_collection');
echo "<pre>";
$count=0;
foreach ($collection as $feed){
   // print_r(json_decode($feed->gethotspots()));
    $hotspot=json_decode($feed->gethotspots());
    foreach ($hotspot as $value){
            
        if(substr($value->text, 0, 1)=='C' || substr($value->text, 0, 1)=='c'){
            $sku= preg_replace('/C/', 'X', $value->text, 1); 
            
            $productId = $productModel->getIdBySku($sku);
            if(empty($productId)){
                $Id = $productModel->getIdBySku($value->text);
                $product=Mage::getModel('catalog/product')->load($Id);
                $parentSku=$product->getParentNumber();
                $sku=$parentSku;
                if(!empty($parentSku))
                    $productId = $productModel->getIdBySku($parentSku);
            }
            
            if($productId){
                $product_ids=str_replace($value->product,$productId,$feed->getProductIds());
                $count++;
                Mage::log($count."-Updating::".$value->text,Zend_log::DEBUG,'instagram.log',true);
                
                $value->text=$sku;
                $value->product=$productId;
                $feed->setProductIds($product_ids);
                
               
            }else {
                Mage::log("SKU NOT FOUND::".$sku,Zend_log::DEBUG,'instagram-error.log',true);
            }
           
           // print_r($value);
            
        }
       
    }
    $feed->sethotspots(json_encode($hotspot));
    
    try {
        $feed->save();
    } catch (Exception $e) {
    }
    print_r($feed->getData());
    echo '<br>'; 
}