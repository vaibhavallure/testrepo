<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');
$logFile = "cntr_customer_prepare.log";

/* $page = $_GET["page"];
$size = $_GET['size'];
if(empty($page)){
    die("Please add page");
}

if(empty($size)){
    $size = 100;
} */

try{
    $collection1 = Mage::getModel("allure_teamwork/customer")->getCollection();
    //$collection->addFieldToFilter( 'create_order_method', array('eq'=>1));
    //$collection->setCurPage($page);
    //$collection->setPageSize($size);
    /* $collection1->getSelect()->group('cust_no');
    $collection1->getSelect()->having("count(cust_no) > 1");
    $collection1->setOrder('cust_no', 'asc'); */
    
    //echo "<pre>";
    //print_r($collection1->getAllIds());die;
    $ids = $collection1->getAllIds();
    $ids = implode(",", $ids);
    //print_r($ids);die;
    
    
    $collection = Mage::getModel("allure_teamwork/customer")->getCollection();
    /* $collection->getSelect()->where("cust_no in(".$ids.")");
    $collection->setOrder('cust_no', 'asc'); */
    
    /* echo "<pre>";
    print_r((string) $collection->getSelect());die; */
    
    Mage::log("count = ".$collection->getSize(),Zend_log::DEBUG,$logFile,true);
    $cnt = 0;
    $resource       = Mage::getSingleton('core/resource');
    $writeAdapter   = $resource->getConnection('core_write');
    $writeAdapter->beginTransaction();
    foreach ($collection as $cust){
        $customerId1 = $cust->getCustomerId();
        $custNo1 = $cust->getCustNo();
        try{
            foreach ($collection as $cust1){
                $customerId2 = $cust1->getCustomerId();
                $custNo2 = $cust1->getCustNo();
                if($custNo1 == $custNo2){
                    if($customerId1 < $customerId2){
                        var_dump(" 1 ".$customerId2);
                    }elseif ($customerId1 > $customerId2){
                        var_dump(" 2 ".$customerId1);
                    }
                    var_dump($custNo1);
                    break;
                }
            }
        }catch (Exception $exc){
            Mage::log("Exc:".$exc->getMessage(),Zend_log::DEBUG,$logFile,true);
        }
        $cnt++;
    }
   $writeAdapter->commit();
}catch (Exception $e){
    Mage::log("Exception:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
}
Mage::log("Finish...",Zend_log::DEBUG,$logFile,true);
