<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');
$logFile = "cntr_order_prepare.log";

$page = $_GET["page"];
$size = $_GET['size'];
if(empty($page)){
    die("Please add page");
}

if(empty($size)){
    $size = 100;
}

try{
    $collection = Mage::getModel("sales/order")->getCollection();
    $collection->addFieldToFilter( 'create_order_method', array('eq'=>1));
    //$collection->getSelect()->group('customer_email');
    $collection->setCurPage($page);
    $collection->setPageSize($size);
    $collection->setOrder('entity_id', 'asc');
    
    Mage::log("count = ".$collection->getSize(),Zend_log::DEBUG,$logFile,true);
    $cnt = 0;
    $resource       = Mage::getSingleton('core/resource');
    $writeAdapter   = $resource->getConnection('core_write');
    $writeAdapter->beginTransaction();
    foreach ($collection as $order){
        $orderId = $order->getId();
        try{
            $_order   = Mage::getModel("sales/order")->load($orderId);
            if($_order->getId()){
                $extraInfo = unserialize($_order->getCounterpointExtraInfo());
                $custNo    = $extraInfo['cust_no'];
                $_order->setCounterpointCustNo($custNo);
                $_order->save();
                Mage::log($cnt ." order_id:".$orderId,Zend_log::DEBUG,$logFile,true);
            }
            if (($cnt % 100) == 0) {
                $writeAdapter->commit();
                $writeAdapter->beginTransaction();
            }
            $_order = null;
        }catch (Exception $exc){
            Mage::log("order_id:".$order_id." Exc:".$exc->getMessage(),Zend_log::DEBUG,$logFile,true);
        }
        $cnt++;
    }
    $writeAdapter->commit();
}catch (Exception $e){
    Mage::log("Exception:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
}
Mage::log("Finish...",Zend_log::DEBUG,$logFile,true);
