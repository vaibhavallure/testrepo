<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$logFile = "cp_cust_update.log";

$page = $_GET['page'];
$size = $_GET['size'];
if(empty($page)){
    die("please specify page");
}


if(empty($size)){
    $size   = 100;
}

try{
    $cnt    = 0;
    $resource       = Mage::getSingleton('core/resource');
    
    $writeAdapter   = $resource->getConnection('core_write');
    $writeAdapter->beginTransaction();
    
    $collection = Mage::getModel("allure_teamwork/temp")->getCollection();
    $collection->setCurPage($page);
    $collection->setPageSize($size);
    $collection->setOrder('id', 'asc');
    
    foreach ($collection as $cust){
        $cust_note = $cust->getCustNote();
        $cust_no = $cust->getCustNo();
        $customerId = $cust->getCustomerId();
        $email      = $cust->getEmail();
        try{
            //add cust note from csv
            $customerObj = Mage::getModel("customer/customer")->load($customerId);
            if($customerObj->getId()){
                $customerObj->setCustNote($cust_note);
                if(!$customerObj->getCustomerType() ){
                    $customerObj->setCustomerType(6);   //magento cust
                }
                $customerObj->setCounterpointCustNo($cust_no);
                $customerObj->setTempEmail($email);
                $customerObj->save();
                Mage::log($cnt." customer_id:".$customerId,Zend_log::DEBUG,$logFile,true);
            }
            $customerObj = null;
            
            if (($cnt % 100) == 0) {
                $writeAdapter->commit();
                $writeAdapter->beginTransaction();
            }
            
        }catch (Exception $e){
            Mage::log("cust_no:".$cust_no." exc:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
        }
        $cnt++;
    }
    $writeAdapter->commit();
}catch (Exception $e){
    Mage::log("Exception:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
}
Mage::log("Finish...",Zend_log::DEBUG,$logFile,true);
