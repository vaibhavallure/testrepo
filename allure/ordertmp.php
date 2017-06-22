<?php 
require_once('../app/Mage.php'); 
umask(0);
Mage::app();
$products = array() ;
$lower = $_GET['lower'];
$upper= $_GET['upper'];

$model=Mage::getModel('allure_ordernotifications/cron');
$model->processOrderNoftifications();
echo "Done";