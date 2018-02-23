<?php

require_once('../app/Mage.php');
umask(0);
Mage::app();
$from = $_GET['from'];
$to= $_GET['to'];
$store= $_GET['store'];
if(empty($from) || empty($to) || empty($store)){
    die('Please add Store Id ,from & to date limit');
}

$count=0;
$from= date("Y-m-d 00:00:00",strtotime($from));
$to = date("Y-m-d 23:59:59",strtotime($to));
$orders=Mage::getModel("sales/order")->getCollection()->addAttributeToFilter('store_id',$store)
->addAttributeToFilter('created_at', array('from'=>$from, 'to'=>$to))
->addAttributeToFilter('status', array('complete','processing','pending','canceled'));

foreach ($orders as $order){
    try {
        foreach ($order->getInvoiceCollection() as $invoice){
            $invoice->save();
        }
        $order->save();
        $count++;
        Mage::log('count:'.$count,Zend_log::DEBUG,'updategridtax.log',true);
        Mage::log($order->getIncrementId(),Zend_log::DEBUG,'updategridtax.log',true);
    }
    catch (Exception $e) {
        Mage::log("Exception For:".$order->getIncrementId(),Zend_log::DEBUG,'updategridtax.log',true);
        Mage::log($e->getMessage(),Zend_log::DEBUG,'updategridtax.log',true);
    }
}
echo "Done";
die;
