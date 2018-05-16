<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();


$from = $_GET['from'];
$to= $_GET['to'];


$from_date = date("Y-m-d 04:00:00",strtotime($from));
$to_date = date("Y-m-d 03:59:59",strtotime($to));
$orders=Mage::getModel("sales/order")->getCollection()->addAttributeToFilter('store_id',1);
$orders->addAttributeToFilter('status', array('in_production','processing','pending','in_counterpoint'));
if(!empty($from) && !empty($to)){
    $orders->addAttributeToFilter('created_at', array('from'=>$from_date, 'to'=>$to_date));
}
$counter=100;
foreach ($orders as $order){
    try {
        $email='test'.$counter.'@allureinc.co';
        Mage::log('#IncrementID::'.$order->getIncrementId().' OLD EMAIL::'.$order->getCustomerEmail().' NEW EMAIL::'.$email,Zend_log::DEBUG,'update_email.log',true);
        $order->setCustomerEmail($email);
        $order->save();
        $counter++;
    } catch (Exception $e) {
        Mage::log('Exception::'.$e->getMessage(),Zend_log::DEBUG,'update_email.log',true);
    }
   
}