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
    if(substr($product->getSku(), 0, 1)=='X' || substr($product->getSku(), 0, 1)=='E'){
        $product->setSizeSample(1);
        $product->save();
        $cntr++;
        Mage::log($cntr.'-'."SKU::".$product->getSku().'-- NAME::'.$product->getName(),Zend_log::DEBUG,'sizesameples.log',true);
    }
    
}