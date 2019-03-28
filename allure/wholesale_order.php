<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
$app = Mage::app('default');
Mage::getSingleton('core/session', array('name' => 'adminhtml'));
Mage::app()->setCurrentStore(0);

$_log_file = "wholesale_order_customer.log";

$_general_group     = 1;
$_wholesale_group   = 2;

try{
    $orderCollection = Mage::getModel("sales/order")
        ->getCollection()
        ->addFieldToFilter("customer_group_id",$_wholesale_group);
    
    $orderCollection->getSelect()->group('customer_id');
    
    Mage::log("order count:- ".$orderCollection->getSize(),Zend_log::DEBUG,$_log_file,true);
    
    foreach ($orderCollection as $order){
        $groupId    = $order->getCustomerGroupId();
        $customerId = $order->getCustomerId();
        $customer   = Mage::getModel("customer/customer")->load($customerId);
        $customerGroupId = $customer->getGroupId();
        if($groupId != $customerGroupId){
            try{
                $customer->setGroupId($_wholesale_group);
                $customer->save();
                Mage::log("email-:".$customer->getEmail()." of group switch to wholesale",Zend_log::DEBUG,$_log_file,true);
            }catch (Exception $e){
                Mage::log("Sub - ".$e->getMessage(),Zend_log::DEBUG,$_log_file,true);
            }
        }
    }
        
}catch (Exception $e){
    Mage::log("Main - ".$e->getMessage(),Zend_log::DEBUG,$_log_file,true);
}
Mage::log("Finish ",Zend_log::DEBUG,$_log_file,true);

die("Finish...");