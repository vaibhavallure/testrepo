<?php
require_once ('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);

const NEWYORK_TIME_OFFSET = 5;
$log_file = "update_teamwork_order.log";

$cnt = 1;

$orderCollection = Mage::getModel("sales/order")->getCollection()
->addFieldToFilter("create_order_method",2);
$orderCollection->getSelect()->where("salesforce_order_id is not null and created_at < '2018-11-20'");

$coreResource = Mage::getSingleton('core/resource');
$writeAdapter = $coreResource->getConnection('core_write');
//echo $orderCollection->getSelect()->__toString();
foreach ($orderCollection as $order){
    $teamworkReceiptId = $order->getTeamworkReceiptId();
    $orderId = $order->getId();
    if($teamworkReceiptId){
        $modelObj = Mage::getModel("allure_teamwork/tmorder")
        ->load($teamworkReceiptId,"tm_receipt_id");
        if($modelObj->getEntityId()){
            $tmOrderData = unserialize($modelObj->getTmdata());
            $orderDetails = $tmOrderData["order_detail"];
            $extraDetails = $tmOrderData["extra_details"];
            $tmOrderDateStr = $orderDetails["RecCreated"];
            $createdAtAr = explode(".", trim($tmOrderDateStr));
            $createdAt = $createdAtAr[0];
            $oldStoreId = $order->getOldStoreId();
            $locationCode = $extraDetails["LocationCode"];
            $storeObj = Mage::getModel("allure_virtualstore/store")->load($oldStoreId);
            if($storeObj->getId()){
                if($locationCode != 1){
                    $createAtOtherStr = explode(".", trim($orderDetails["StateDate"]));
                    $timeDate = strtotime($createAtOtherStr[0]);
                    $orderDate = strtotime("+5 hour", $timeDate);
                    $createdAt = date('Y-m-d H:i:s', $orderDate);
                }
                $sql_order = "UPDATE sales_flat_order SET created_at = '".$createdAt."' WHERE entity_id ='".$orderId."'";
                $writeAdapter->query($sql_order);
                Mage::log($cnt." - order_id - ".$orderId." date - ".$createdAt." changed.",Zend_Log::DEBUG,$log_file,true);
            }else{
                Mage::log($cnt." - order_id - ".$orderId." store not assigned.",Zend_Log::DEBUG,$log_file,true);
            }
            $modelObj = null;
        }else{
            Mage::log($cnt." - order_id - ".$orderId." sync data not available.",Zend_Log::DEBUG,$log_file,true);
        }
    }
    $cnt++;
}

Mage::log("Total count - ".$orderCollection->getSize(),Zend_Log::DEBUG,$log_file,true);
Mage::log("Finish.",Zend_Log::DEBUG,$log_file,true);
die("Finish");
