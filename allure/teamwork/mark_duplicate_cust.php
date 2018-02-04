<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');
$logFile = "cntr_duplicate_cust.log";

$page = $_GET["page"];
$size = $_GET['size'];
if(empty($page)){
    die("Please add page");
}

if(empty($size)){
    $size = 100;
}

try{
    $collection = Mage::getModel("allure_teamwork/duplcustomer")->getCollection();
    $collection->setCurPage($page);
    $collection->setPageSize($size);
    $collection->setOrder('id', 'asc');
    
    Mage::log("count = ".$collection->getSize(),Zend_log::DEBUG,$logFile,true);
    $cnt = 0;
    $resource       = Mage::getSingleton('core/resource');
    $writeAdapter   = $resource->getConnection('core_write');
    $writeAdapter->beginTransaction();
    foreach ($collection as $cust){
        $customerId = $cust->getCustomerId();
        $email      = $cust->getCustomerEmail();
        try{
            $customer   = Mage::getModel("customer/customer")->load($customerId);
            if($customer->getId()){
                $custNo = $cust->getCustNo();
                $customer->setCustomerType(5); //duplicate
                $customer->setCounterpointCustNo($custNo);
                $customer->setTempEmail($email);
                $customer->setIsDuplicate(1);
                $customer->save();
                Mage::log($cnt ." customer_id:".$customerId,Zend_log::DEBUG,$logFile,true);
            }
            
            if (($cnt % 100) == 0) {
                $writeAdapter->commit();
                $writeAdapter->beginTransaction();
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
