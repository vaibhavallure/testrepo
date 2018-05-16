<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$ids = Mage::getModel('catalog/product')->getCollection()->getAllIds();
foreach ($ids as $id){
    $product=Mage::getModel('catalog/product')->load($id);
    if($product->getId()){
       // echo $product->getSku().',';
       // echo "<br>";
    }else {
        echo $id;
        echo "<br>";
    }
}
//echo count($productCollection);
die;