<?php
/**
 * Created by PhpStorm.
 * User: Indrajeet
 * Date: 11/7/19
 * Time: 6:08 PM
 */
require_once('../../app/Mage.php');
umask(0);
Mage::app();

$config = "allure_salesforce/general/bulk_cron_time";
var_dump(Mage::getStoreConfig($config));die;

//$requestData = array(
//    "orders" => array(454144,454145),
//    "order_items" => array(737038,737039),
//    "customers" => array(186464),
//    "contact" => array(186464),
//    "invoice" => array(499066,499067),
//    "credit_memo" => array(22535,22536),
//    "shipment" => array(329824,329823),
//);

$requestData = array(
    "orders" => array(454158),
);

$lastRunTime = new DateTime("1 hour ago");  //static right now only for test purpose
//print_r($lastRunTime);die;
$model = Mage::getModel("allure_salesforce/observer_update");
$model->getRequestData(null,$requestData);
//$model->getRequestData($lastRunTime,null);
die;

//$model = Mage::getModel("allure_salesforce/observer_update");
//$data = $model->getRequestData();
//echo "<pre>";
//print_r($data,true);
//die;