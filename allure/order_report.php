<?php
require_once('../app/Mage.php'); 
umask(0);
Mage::app();

Mage::app()->setCurrentStore(0);

$counterPointCollection = Mage::getModel("sales/order")
                          ->getCollection()
                          ->addFieldToFilter("create_order_method",1);

header ( "Content-type: application/vnd.ms-excel" );
header ( "Content-Disposition: attachment; filename=magento_order.xls" );

$str = "<table><tr><th>magento_order</th><th>ct_order</th></tr>" ;  
echo $str;
foreach ($counterPointCollection as $order){
    $orderId = $order->getIncrementId();
    $ctpntOrderId = $order->getCounterpointOrderId();
    $str = "<tr><td>{$orderId}</td><td>{$ctpntOrderId}</td></tr>";
    echo $str;
}
$str = "</table>";

echo $str;
die;
