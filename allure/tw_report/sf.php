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
echo "<pre>";

$order = Mage::getModel('sales/order')->load(437297);
var_dump($order->getCouponCode());

var_dump($order->getCouponRuleName());


die;

$product = Mage::getModel('catalog/product')->load(49132);
var_dump($product->getData('weight'));die;

//$sd = Mage::getModel('eav/attribute_option_value')->load(1129);

$_collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
    ->setAttributeFilter(238)
    ->setStoreFilter(0)
    ->load();

$s = $_collection->toOptionArray();
foreach($s as $t) {
    if($t['value'] == $product->getData('custom_stock_status')) {
        var_dump($t['label']);
    }
}

die;




$_customer = Mage::getModel('customer/customer')->load(189547);

//$subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($_customer);
//var_dump($subscriber->isSubscribed());die;
//$_customer->setConfirmation(1);
//$_customer->save();die;
//var_dump($_customer->getCreatedAt());die;

$collection = Mage::getResourceModel('sales/sale_collection')
    ->setCustomerFilter($_customer)
    ->setOrderStateFilter(Mage_Sales_Model_Order::STATE_CANCELED, true)
    ->load()
;
var_dump($collection->getTotals()->getBaseLifetime());
var_dump($collection->getTotals()->getAvgsale());
die;
if (!$_customer->getConfirmation()) {
    var_dump(Mage::helper('customer')->__('Confirmed'));echo "adada";
}

if ($_customer->isConfirmationRequired()) {
    var_dump(Mage::helper('customer')->__('Not confirmed, cannot login'));echo "bsda";
}

var_dump(Mage::helper('customer')->__('Not confirmed, can login'));echo "ccsada";

die;

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