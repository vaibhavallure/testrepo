<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();

$cat = Mage::getModel('catalog/category')->load(3);
$subCat=Mage::getModel('catalog/category')->load(9);
$subCat=$subCat->getChildren();
$cat=$cat->getChildren();
$productModel = Mage::getSingleton('catalog/product');
$store=$_GET['store'];
if(empty($store)){
    die('Please add store');
}


if(isset($subCat))
    $subcats=$cat.','.$subCat;
/*Returns comma separated ids*/
print_r($subcats);
echo "<br>";
$total=0;
$count=0;
$subcats=explode(',',$subcats);


$items=Mage::getModel('allure_londoninventory/inventory')->getCollection();
foreach ($items as $item){
    $sku=explode('|',$item['sku']);
    $id = $productModel->getIdBySku($sku[0]);
    if($id){
        $product=$productModel->setStoreId($store)->load($id);
        foreach ($product->getCategoryIds() as $catId){
            if(in_array($catId, $subcats)){
                $total=$total+$product->getPrice()*$item['qty'];
           
                Mage::log($product->getId().':Category:'.$catId.':Price:'.$product->getPrice(),Zend_log::DEBUG,'jewellery.log',true);
                break;
            }
            else {
                $count++;
                Mage::log($count.'-'.$product->getId().':Category:'.$catId.':Price:'.$product->getPrice(),Zend_log::DEBUG,'jewellery_not.log',true);
            }
         }
       
     
    }else {
        Mage::log($item['sku'],Zend_log::DEBUG,'jewellery_sku_not.log',true);
        
    }
}
echo $total;
die;