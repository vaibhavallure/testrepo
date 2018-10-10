<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$file = $_GET["file"];

if(empty($file)){
    die("Specify file path.");
}


$teamworkLog = "customer_in_teamwork.log";

$folderPath   = Mage::getBaseDir("var") . DS .$file;

$csvData = array();
if(($handle = fopen($folderPath, "r")) != false){
    $max_line_length = defined("MAX_LINE_LENGTH") ? MAX_LINE_LENGTH : 10000;
    $header = fgetcsv($handle, $max_line_length);
    foreach ($header as $c => $_cols){
        $header[$c] = strtolower(str_replace(" ", "_", $_cols));
    }
    
    $header_column_count = count($header);
    
    while (($row = fgetcsv($handle,$max_line_length)) != false){
        $row_column_count = count($row);
        if($row_column_count == $header_column_count){
            $entry = array_combine($header, $row);
            $csvData[] = $entry;
        }
    }
    fclose($handle);
    
    if(count($csvData)){
        $websiteId = 1;
        $existCnt = 0;
        $nonExistCnt = 0;
        
        $shipCnt = 0;
        
        foreach ($csvData as $data){
            
            $shipCnt++;
            
            $tData = unserialize($data["order"]);
            
            foreach ($tData as $receiptId => $oData){
                try{
                    
                    $orderObj = Mage::getModel('sales/order')->load($receiptId,'teamwork_receipt_id');
                    if(!$orderObj->getId()){
                        Mage::log($shipCnt." - Receipt Id:".$receiptId." Order not created.",Zend_log::DEBUG,$teamworkLog,true);
                        continue;
                    }
                    
                    $orderId = $orderObj->getId();
                    
                    if (!$orderObj->canShip()) {
                        Mage::log($shipCnt." - Order Id:".$orderId." Shipment can't create for this order.",Zend_log::DEBUG,$teamworkLog,true);
                        continue;
                    }
                    
                    $isRefundItem = false;
                    $qtys = array();
                    $cntI = 0;
                    $refCnt = 0;
                    foreach ($orderObj->getAllItems() as $item) {
                        $cntI++;
                        $otherSysQty = $item->getOtherSysQty();
                        if($otherSysQty < 0){
                            $isRefundItem = true;
                            $refCnt++;
                            //break;
                        }
                        
                        $qtys[$item->getId()] = $item->getQtyOrdered();
                    }
                    
                    if($cntI == $refCnt){
                        Mage::log($shipCnt." - Order Id:".$orderId." Shipment can't create for this order.Refunded Item present.",Zend_log::DEBUG,$teamworkLog,true);
                        continue;
                    }
                    
                    $shipment = Mage::getModel('sales/service_order', $orderObj)
                    ->prepareShipment($qtys);
                    
                    // Register Shipment
                    $shipment->register();
                    
                    $shipment->getOrder()->setIsInProcess(true);
                    $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($orderObj)
                    ->save();
                    
                    $shipment->setEmailSent(true);
                    
                    $orderObj->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
                    $orderObj->setData('status', Mage_Sales_Model_Order::STATE_COMPLETE);
                    
                    $orderObj->save();
                    
                    Mage::log($shipCnt." - Shipment created. Shipment Id:".$shipment->getId()." Order Id:".$orderId,Zend_log::DEBUG,$teamworkLog,true);
                    
                 }catch (Exception $e){
                    Mage::log("Exception".$e->getMessage(),Zend_log::DEBUG,$teamworkLog,true);
                 }
            }
        }
    }
}

Mage::log("Finish...",Zend_log::DEBUG,$teamworkLog,true);
die("Finish...");


