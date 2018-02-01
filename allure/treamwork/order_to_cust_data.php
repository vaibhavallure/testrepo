<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');
$logFile = "cntr_customer_prepare.log";

try{
    $collection = Mage::getModel("sales/order")->getCollection();
    $collection->addFieldToFilter( 'create_order_method', array('eq'=>1));
    $collection->getSelect()->group('customer_email');
    Mage::log("count = ".$collection->getSize(),Zend_log::DEBUG,$logFile,true);
    $cnt = 0;
    $resource       = Mage::getSingleton('core/resource');
    $writeAdapter   = $resource->getConnection('core_write');
    $writeAdapter->beginTransaction();
    foreach ($collection as $order){
        $customerId = $order->getCustomerId();
        $email      = $order->getCustomerEmail();
        try{
            $customer   = Mage::getModel("customer/customer")->load($customerId);
            if($customer->getId()){
                $extraInfo = unserialize($order->getCounterpointExtraInfo());
                $custNo    = $extraInfo['cust_no'];
                $customer->setCustomerType(1);
                $customer->setCounterpointCustNo($custNo);
                $customer->setTempEmail($email);
                $customer->save();
                if (($cnt % 100) == 0) {
                    $writeAdapter->commit();
                    $writeAdapter->beginTransaction();
                }
                Mage::log($cnt ." customer_id:".$customerId,Zend_log::DEBUG,$logFile,true);
            }
        }catch (Exception $exc){
            Mage::log("customer_id:".$customerId." Exc:".$exc->getMessage(),Zend_log::DEBUG,$logFile,true);
        }
        $cnt++;
    }
    $writeAdapter->commit();
}catch (Exception $e){
    Mage::log("Exception:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
}
Mage::log("Finish...",Zend_log::DEBUG,$logFile,true);
