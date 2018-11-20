<?php
require_once ('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);

$orderCollection = Mage::getModel("sales/order")->getCollection()
->addFieldToFilter("create_order_method",2);
$orderCollection->getSelect()->where("salesforce_order_id is not null and created_at < '2018-11-20'");


foreach ($orderCollection as $order){
    
}

die("Finish");
