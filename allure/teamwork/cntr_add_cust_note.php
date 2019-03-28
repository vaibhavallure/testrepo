<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$logFile = "cntr_cust_note.log";

$fileName = $_GET["file"];
if(empty($fileName)){
    die("Please specify file name");
}

$state = $_GET['state'];
if(empty($state)){
    die("state mention");
}

$csv = Mage::getBaseDir('var').DS."export".DS.$fileName;
try{
    $cnt    = 0;
    $io     = new Varien_Io_File();
    $io->streamOpen($csv, 'r');
    $csvData = $io->streamReadCsv();
    $resource       = Mage::getSingleton('core/resource');
    
    $writeAdapter   = $resource->getConnection('core_write');
    $writeAdapter->beginTransaction();
    
    $csvData = $io->streamReadCsv();
    
    while($csvData = $io->streamReadCsv()){
        $cust_no = $csvData[0];
        $cust_note   = $csvData[5];
        try{
            if($state == "1"){
                
                $customers  = Mage::getModel('customer/customer')
                ->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('counterpoint_cust_no', array('eq' => $custNo));
                
                foreach ($customers as $cust){
                    $customer    = Mage::getModel("customer/customer")
                    ->load($cust->getId());
                    if($customer->getId()){
                        $customer->setCustNote($cust_note);
                        $customer->save();
                        Mage::log($cnt." cust_no:".$cust_no." customer_id:".$customer->getId(),Zend_log::DEBUG,$logFile,true);
                    }
                    $customer = null;
                }
            }
            elseif($state == "2"){
            //state ==2
                //add cust note from csv
                $model = Mage::getModel("allure_teamwork/cpcustomer")->load($cust_no,"cust_no");
                if($model->getId()){
                    $model->setCustNote($cust_note);
                    $model->save();
                    Mage::log($cnt." cust_no:".$cust_no,Zend_log::DEBUG,$logFile,true);
                }
                $model = null;
            }
            
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
