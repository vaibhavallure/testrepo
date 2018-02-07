<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
$products = array() ;
$csvFile = 'Missingsku';
header('Content-Disposition: attachment; filename='.$csvFile.'.csv');
header('Content-type: text/csv');
header('Pragma: no-cache');
header('Expires: 0');
$file = fopen('php://output', 'w');

fputcsv($file, array('SKU'));
$data = array();

$collection=Mage::getModel('sales/order_item')->getCollection()
->distinct(true)
->addAttributeToSelect('sku');
//print_r(count($collection->getData()));
$productModel = Mage::getSingleton('catalog/product');

foreach ($collection as $item){
   // print_r($item->getSku());
    $id = $productModel->getIdBySku($item->getSku());
    if ($id) {
        
    }else{
        $data[]=array($item->getSku());
    }
}
foreach ($data as $row)
{
    fputcsv($file, $row);
}
exit();



