<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');
$logFile = "cntr_customer_prepare.log";

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
    $collection->setCurPage($page);
    $collection->setPageSize($size);
    $collection->setOrder('entity_id', 'asc');
    $collection->getSelect()->group('customer_id');
    
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
                $model = Mage::getModel("allure_teamwork/cpcustomer")->load($custNo,"cust_no");
                if($model->getId()){
                    $cust_note = $model->getCustNote();
                    $customer->setCustNote($cust_note);
                }
                
                if($customer->getCustomerType() == 0){
                    $customer->setCustomerType(4);   //magento cust
                }
                $customer->setCounterpointCustNo($custNo);
                $customer->setTempEmail($email);
                $customer->save();
                Mage::log($cnt ." customer_id:".$customerId,Zend_log::DEBUG,$logFile,true);
            }
            $customer = null;
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
