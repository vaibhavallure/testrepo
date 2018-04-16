<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$logFile = "cntr_update_cust.log";

$fileName = $_GET["file"];
if(empty($fileName)){
    die("Please specify file name");
}

$csv = Mage::getBaseDir('var').DS."import".DS.$fileName;
try{
    $cnt    = 0;
    $io     = new Varien_Io_File();
    $io->streamOpen($csv, 'r');
    $resource       = Mage::getSingleton('core/resource');
    
    $csvData = $io->streamReadCsv();
   // var_dump($csvData);die;
    
    $custArr = array();
    while($csvData = $io->streamReadCsv()){
        try{
            $custNo = $csvData[0];
            $email  = $csvData[22];
            $custArr[$custNo] = $email;
        }catch (Exception $e){
            Mage::log("exc:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
        }
    }
    
    
    $collection = Mage::getModel("allure_teamwork/customer")->getCollection();
    $collection->addFieldToFilter( 'is_non_mag_cust', array('eq'=>1));
    $collection->addFieldToFilter('cust_no', array('nlike' => '%WALK%'));
    //print_r((string)$collection->getSelect());
    //die;
    $writeAdapter   = $resource->getConnection('core_write');
    $writeAdapter->beginTransaction();
    $cnt = 0;
    Mage::log("size = ".$collection->getSize(),Zend_log::DEBUG,$logFile,true);
    foreach ($collection as $cust){
        $model = Mage::getModel("allure_teamwork/customer")->load($cust->getId());
        $custNo = $model->getCustNo();
        $mEmail = $custArr[$custNo];
        if(!empty($mEmail)){
            if(preg_match("/@/", $mEmail)){
                $model->setEmail($mEmail);
                $model->save();
                Mage::log($cnt." update customer_id:".$model->getCustomerId(),Zend_log::DEBUG,$logFile,true);
            }
        }
        if (($cnt % 100) == 0) {
            $writeAdapter->commit();
            $writeAdapter->beginTransaction();
        }
        $cnt++;
    }
    $writeAdapter->commit();
    
}catch (Exception $e){
    Mage::log("Exception:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
}
Mage::log("Finish...",Zend_log::DEBUG,$logFile,true);
