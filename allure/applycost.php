<?php
require_once ('../app/Mage.php');
umask(0);
Mage::app();
$productModel = Mage::getSingleton('catalog/product');
$store=2;
$collection= $itemData=Mage::getModel('allure_londoninventory/inventory')->getCollection();
foreach ($collection as $item){
    $id = $productModel->getIdBySku($item->getSku());
    if($id){
        $cost=Mage::getModel('Catalog/product')->setStoreId($store)->load($id)->getCost();
        $remainingQty=($item->getAddedQty()-$item->getQty());
        $total=$remainingQty*$cost;
        $item->setCost($cost);
        $item->setReminingQty($remainingQty);
        $item->setTotal($total);
        $item->save();
       //echo $item->getSku().'----'.$remainingQty.'----'.$cost;
        //echo "<br>";
        
    }else {
        echo $item->getSku();
        echo "<br>";
    }
    
}