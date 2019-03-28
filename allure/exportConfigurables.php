<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');


$csvFile = 'catalog';

header('Content-Disposition: attachment; filename=' . $csvFile . '.csv');
header('Content-type: text/csv');
header('Pragma: no-cache');
header('Expires: 0');
$file = fopen('php://output', 'w');

fputcsv($file, array('id','SKU','NAME','Category 1','Category 2','Category 3','Category 4','Category 5'));

$collection=Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('type_id','configurable');
$data=array();
foreach ($collection as $product){
    $product = Mage::getModel('catalog/product')->load($product->getId());
    
    
    $cats = $product->getCategoryIds();
    $cntr=0;
    $id=$product->getId();
    $name=$product->getName();
    $sku=$product->getSku();
    $category1='';
    $category2='';
    $category3='';
    $category4='';
    $category5='';
    
    foreach ($cats as $category_id) {
        $_cat = Mage::getModel('catalog/category')->load($category_id) ;
        $cntr++;
        if ($cntr == 1)
            $category1 = $_cat->getName();
        if ($cntr == 2)
            $category2 = $_cat->getName();
        if ($cntr == 3)
            $category3 = $_cat->getName();
        if ($cntr == 4)
            $category4 = $_cat->getName();
        if ($cntr == 5)
            $category5 = $_cat->getName();
    } 
    $data[]=array($id,$sku,$name,$category1,$category2,$category3,$category4,$category5);
}
foreach ($data as $row)
{
    fputcsv($file, $row);
}

exit();