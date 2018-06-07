<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
$app = Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
$cntr=0;
$collection=Mage::getModel('catalog/product')->getCollection();
foreach ($collection as $product){
    $product=Mage::getModel('catalog/product')->load($product->getId());
    $product->setWeight('0.0117');
    $product->save();
    $cntr++;
    Mage::log($cntr.'-'."SKU::".$product->getSku().'-- NAME::'.$product->getName(),Zend_log::DEBUG,'changeWeight.log',true);
    
}