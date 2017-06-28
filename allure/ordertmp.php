<?php 
require_once('../app/Mage.php'); 
umask(0);
Mage::app();
$products = array() ;
$stockId=1;
$storeId=1;

$model=Mage::getModel('inventory/cron');
$model->autoProcessLowstockReports();
echo "Done";
die;

$model=Mage::getModel('allure_ordernotifications/cron');
$model->processOrderNoftifications();
echo "Done";