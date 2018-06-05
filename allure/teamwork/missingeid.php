<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$ids=Mage::getModel("catalog/product")->getCollection()->getAllIds();
$count=0;
foreach ($ids as $id){
    $product=Mage::getModel('catalog/product')->load($id);
    if(empty($product->getTeamworkId()) && $product->getTypeId()=="simple" && $product->getStatus()==1){
        echo $product->getSku();
        echo ",";
        $count++;
    }
}

echo "Finished";
echo "Count::".$count;
