<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$logFile='archive_orders.log';
$from = $_GET['from'];
$to= $_GET['to'];

if(empty($from) || empty($to)){
    die('Please add from and to limit');
}


//Calculating purchased qty for each item since 31 March 2017 to till Date
echo "<pre>";
$from_date = date("Y-m-d 04:00:00",strtotime($from));
$to_date = date("Y-m-d 03:59:59",strtotime($to));
$orders=Mage::getModel("sales/order")->getCollection()
->addAttributeToFilter('created_at', array('from'=>$from_date, 'to'=>$to_date))
->addAttributeToFilter('status', array('complete','needs_attention','canceled'));


$order_ids=$orders->getAllIds();

$result = Mage::getModel('iwd_ordermanager/archive')->addSalesToArchiveByIds($order_ids);
print_r($order_ids);
echo "Total order archive count:".count($order_ids);
Mage::log("Archive Orders::".json_encode($order_ids),Zend_log::DEBUG,$logFile,TRUE);
Mage::log("Archive Orders Count::".count($order_ids),Zend_log::DEBUG,$logFile,TRUE);
echo "--------------------------------------";
print_r($result);
