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

$model = Mage::getModel("allure_salesforce/observer_update");
$data = $model->getRequestData();
echo "<pre>";
print_r($data,true);
die;

