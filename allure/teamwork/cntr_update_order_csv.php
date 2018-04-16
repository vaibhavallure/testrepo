<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$logFile = "cntr_update_ord.log";

/* $fileName = $_GET["file"];
if(empty($fileName)){
    die("Please specify file name");
} */

try{
    $collection = Mage::getModel("allure_teamwork/customer")->getCollection();
    $collection->addFieldToFilter( 'is_non_mag_cust', array('eq'=>1));
    $collection->addFieldToFilter('cust_no', array('nlike' => '%WALK%'));
    $resource       = Mage::getSingleton('core/resource');
    $writeAdapter   = $resource->getConnection('core_write');
    $writeAdapter->beginTransaction();
    $cnt = 0;
    Mage::log("size = ".$collection->getSize(),Zend_log::DEBUG,$logFile,true);
    foreach ($collection as $cust){
        $model = Mage::getModel("allure_teamwork/customer")->load($cust->getId());
        $custNo = $model->getCustNo();
        try{
            if(!empty($custNo)){
                $writeAdapter->update(
                    "sales_flat_order",
                    array("customer_id"  => $model->getCustomerId(),
                        "customer_email" => $model->getEmail()
                    ),
                    "counterpoint_cust_no = '{$custNo}'"
                );
                Mage::log($cnt." customer_id:".$model->getCustomerId(),Zend_log::DEBUG,$logFile,true);
            }
           
            if (($cnt % 100) == 0) {
                $writeAdapter->commit();
                $writeAdapter->beginTransaction();
            }
        }catch (Exception $e){
            Mage::log("customer_id:".$model->getCustomerId()." Exception:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
        }
        $cnt++;
    }
    $writeAdapter->commit();
    
}catch (Exception $e){
    Mage::log("Exception:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
}
Mage::log("Finish...",Zend_log::DEBUG,$logFile,true);
