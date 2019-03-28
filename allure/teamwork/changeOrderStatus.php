<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$orders=array("2018004284","2018004324","2018004341","2018004342","2018004327","2018004337","2018004329","2018004332","2018004224","2018004331","2018004334","2018004336","2018004345","2018004376","2018004374",
    "2017008183","2018004333","2018004362","2018004381",
    "2018004358","2018004353","2018003752","2018004387","2018004348:","2018004412","2018004400","2018004409","2018004485","2018004415-B","2018003921","2018004250","2018004321","2018004515");

foreach ($orders as $orderIncrementId){
    $order = Mage::getModel('sales/order')
    ->loadByIncrementId($orderIncrementId);
   
    if($order->getStatus()=='in_counterpoint'){
        try {
            //$order->setState(Mage_Sales_Model_Order::STATE_COMPLETE,true)->save();
            $order->setStatus('complete');
            $order->save();
            Mage::log($orderIncrementId,Zend_log::DEBUG,'statuschange.log',true);
        } catch (Exception $e) {
            echo "Exception ocuured for::".$orderIncrementId.'::'.$e->getMessage();
            echo "<br>";
        }
    }else {
        echo  $orderIncrementId."-". $order->getStatus();
        echo "<br>";
    }
   
}

