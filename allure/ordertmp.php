<?php 
require_once('../app/Mage.php'); 
umask(0);
Mage::app();
$products = array() ;
$stockId=4;
$storeId=5;

$model=Mage::getModel('inventory/cron');
$model->autoProcessLowstockReports();
echo "Done";
die;


$model=Mage::getModel('allure_ordernotifications/cron');
$model->processOrderNoftifications();
echo "Done";