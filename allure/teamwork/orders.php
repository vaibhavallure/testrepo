<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');


$csvFile = 'orders';

header('Content-Disposition: attachment; filename=' . $csvFile . '.csv');
header('Content-type: text/csv');
header('Pragma: no-cache');
header('Expires: 0');
$file = fopen('php://output', 'w');

fputcsv($file, array('id','Increment Id','Status','Is Invoiced','Store Id'));
$data = array();
$status = array();

$collection = Mage::getResourceModel("sales/order_grid_collection");
$collection->getSelect()->order('store_id ASC');
$collection->getSelect()->order('status DESC');


foreach ($collection as $order){
    $order=Mage::getModel('sales/order')->load($order->getId());
    $invoiceIds = $order->getInvoiceCollection()->getAllIds();
    $invoiceFlag="No";
    if(count($invoiceIds) >=1){
        $invoiceFlag="Yes";
    }
    $data[]=array($order->getId(),$order->getIncrementId(),$order->getStatus(),$invoiceFlag,$order->getStoreId());
}
foreach ($data as $row)
{
    fputcsv($file, $row);
}

exit();