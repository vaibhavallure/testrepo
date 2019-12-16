<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
$type= $_GET['type'];
if(empty($type)){
    die('Please add type uploaded/received/transfered/purchased/stock. ');
}

$resource = Mage::getSingleton('core/resource');
$table = $resource->getTableName('allure_inventory_count');
$writeAdapter = $resource->getConnection('core_write');

 if($type=='uploaded'){
     $io = new Varien_Io_File();
     $csv = Mage::getBaseDir('var') . DS . "csv" . DS . "uploaded.csv";
     $io->streamOpen($csv, 'r');
     $skuIndex = 0;
     $backIndex = 1;
     $qtyIndex = 2;
     
     while ($csvData = $io->streamReadCsv()) {
         if (count($csvData) < 2) {
             continue;
         }
         $sku = trim($csvData[$skuIndex]);
         $sku=str_replace("+AHw-","|",$sku);
         Mage::log($sku,Zend_log::DEBUG,'ajay.log',true);
         $backQty = trim($csvData[$backIndex]);
         $qty = trim($csvData[$qtyIndex]);
         $final=$backQty+$qty;
         $query="SELECT * FROM {$table} where sku = '{$sku}'";
         $result=$writeAdapter->fetchAll($query);
         if(count($result)<1){
             $query = "INSERT INTO {$table} (sku, uploaded, received, transfered, sold, invoiced,refunded,canceled,current_stock,expected_stock) VALUES ('{$sku}', '{$final}', '0', '0', '0', '0', '0', '0', '0', '0')";
             $writeAdapter->query($query);
         }
     }
 }
 if($type=='received'){
     $ioReceive = new Varien_Io_File();
     $csReceive = Mage::getBaseDir('var') . DS . "csv" . DS . "received.csv";
     $ioReceive->streamOpen($csReceive, 'r');
     
     $skuIndexReceive = 0;
     $qtyIndexReceive = 1;
     
     while ($csReceiveData = $ioReceive->streamReadCsv()) {
         if (count($csReceiveData) < 2) {
             continue;
         }
         $sku = trim($csReceiveData[$skuIndexReceive]);
         $sku=str_replace("+AHw-","|",$sku);
         // $sku=iconv("UTF-8", "CP1252", $sku);
         
         Mage::log($sku,Zend_log::DEBUG,'ajay.log',true);
         
         $qty = trim($csReceiveData[$qtyIndexReceive]);
         $query="SELECT * FROM {$table} where sku = '{$sku}'";
         
         $result=$writeAdapter->fetchAll($query);
         if(count($result)<1){
             $query = "INSERT INTO {$table} (sku, uploaded, received, transfered, sold, invoiced,refunded,canceled,current_stock,expected_stock) VALUES ('{$sku}', '0', '{$qty}', '0', '0', '0', '0', '0', '0', '0')";
             $writeAdapter->query($query);
         }else{
             $query = "UPDATE  {$table} set  received = '{$qty}' where sku = '{$sku}' ";
             $writeAdapter->query($query);
         }
     }
 }
 if($type=='transfered'){
     $ioReceive = new Varien_Io_File();
     $csReceive = Mage::getBaseDir('var') . DS . "csv" . DS . "transfered.csv";
     $ioReceive->streamOpen($csReceive, 'r');
     
     $skuIndexReceive = 0;
     $qtyIndexReceive = 1;
     
     while ($csReceiveData = $ioReceive->streamReadCsv()) {
         if (count($csReceiveData) < 2) {
             continue;
         }
         $sku = trim($csReceiveData[$skuIndexReceive]);
         $sku=str_replace("+AHw-","|",$sku);
         // $sku=iconv("UTF-8", "CP1252", $sku);
         
         Mage::log($sku,Zend_log::DEBUG,'ajay.log',true);
         
         $qty = trim($csReceiveData[$qtyIndexReceive]);
         $query="SELECT * FROM {$table} where sku = '{$sku}'";
         
         $result=$writeAdapter->fetchAll($query);
         if(count($result)<1){
             $query = "INSERT INTO {$table} (sku, uploaded, received, transfered, sold, invoiced,refunded,canceled,current_stock,expected_stock) VALUES ('{$sku}', '0', '0', '{$qty}', '0', '0', '0', '0', '0', '0')";
             $writeAdapter->query($query);
         }else{
             $query = "UPDATE  {$table} set  transfered = '{$qty}' where sku = '{$sku}' ";
             $writeAdapter->query($query);
         }
     }
 }
 if($type=='purchased'){
     
     $ioReceive = new Varien_Io_File();
     $csReceive = Mage::getBaseDir('var') . DS . "csv" . DS . "purchased.csv";
     $ioReceive->streamOpen($csReceive, 'r');
     
     $skuIndex = 0;
     $qtyIndexSold = 1;
     $qtyIndexInvoiced = 2;
     $qtyIndexRefunded = 3;
     $qtyIndexCanceled = 4;
     
     while ($csReceiveData = $ioReceive->streamReadCsv()) {
         if (count($csReceiveData) < 2) {
             continue;
         }
         $sku = trim($csReceiveData[$skuIndex]);
         $sku=str_replace("+AHw-","|",$sku);
         // $sku=iconv("UTF-8", "CP1252", $sku);
         
         Mage::log($sku,Zend_log::DEBUG,'ajay.log',true);
         
         $qtySold = trim($csReceiveData[$qtyIndexSold]);
         $qtyInvoiced = trim($csReceiveData[$qtyIndexInvoiced]);
         $qtyRefunded = trim($csReceiveData[$qtyIndexRefunded]);
         $qtyCanceled = trim($csReceiveData[$qtyIndexCanceled]);
         
         $query="SELECT * FROM {$table} where sku = '{$sku}'";
         
         $result=$writeAdapter->fetchAll($query);
         if(count($result)<1){
             $query = "INSERT INTO {$table} (sku, uploaded, received, transfered, sold, invoiced,refunded,canceled,current_stock,expected_stock) VALUES ('{$sku}', '0', '0', '0', '{$qtySold}', '{$qtyInvoiced}', '{$qtyRefunded}', '{$qtyCanceled}', '0', '0')";
             $writeAdapter->query($query);
         }else{
             $query = "UPDATE  {$table} set  sold = '{$qtySold}',invoiced = '{$qtyInvoiced}',refunded = '{$qtyRefunded}',canceled = '{$qtyCanceled}' where sku = '{$sku}' ";
             $writeAdapter->query($query);
         }
     }
 }
 if($type=='stock'){
     $ioReceive = new Varien_Io_File();
     $csReceive = Mage::getBaseDir('var') . DS . "csv" . DS . "stock.csv";
     $ioReceive->streamOpen($csReceive, 'r');
     
     $skuIndexReceive = 0;
     $qtyIndexReceive = 1;
     
     while ($csReceiveData = $ioReceive->streamReadCsv()) {
         if (count($csReceiveData) < 2) {
             continue;
         }
         $sku = trim($csReceiveData[$skuIndexReceive]);
         $sku=str_replace("+AHw-","|",$sku);
         // $sku=iconv("UTF-8", "CP1252", $sku);
         
         Mage::log($sku,Zend_log::DEBUG,'ajay.log',true);
         
         $qty = trim($csReceiveData[$qtyIndexReceive]);
         $query="SELECT * FROM {$table} where sku = '{$sku}'";
         
         $result=$writeAdapter->fetchAll($query);
         if(count($result)<1){
             $query = "INSERT INTO {$table} (sku, uploaded, received, transfered, sold, invoiced,refunded,canceled,current_stock,expected_stock) VALUES ('{$sku}', '0', '0', '0', '0', '0', '0', '0', '{$qty}', '0')";
             $writeAdapter->query($query);
         }else{
             $query = "UPDATE  {$table} set  current_stock = '{$qty}' where sku = '{$sku}' ";
             $writeAdapter->query($query);
         }
     }
 }
 if($type=='calculate'){
         echo "<pre>";
         $query="SELECT * FROM {$table}";
         $result=$writeAdapter->fetchAll($query);
         foreach ($result as $singleRow){
             Mage::log($singleRow['sku'],Zend_log::DEBUG,'ajay.log',true);
             $expected=($singleRow['uploaded']+$singleRow['received'])-($singleRow['transfered']+$singleRow['sold']);
             $query = "UPDATE  {$table} set  expected_stock = '{$expected}' where sku = '{$singleRow['sku']}' ";
             $writeAdapter->query($query);
         }
         
 }
die("Operation end...");
        
        