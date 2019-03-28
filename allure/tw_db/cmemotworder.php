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
        
        
        $credmCnt = 0;
        
        foreach ($csvData as $data){
            
            $tData = unserialize($data["order"]);
            
            $credmCnt++;
            
            foreach ($tData as $receiptId => $oData){
                try{
                    
                    $orderObj = Mage::getModel('sales/order')->load($receiptId,'teamwork_receipt_id');
                    if(!$orderObj->getId()){
                        Mage::log($credmCnt." - Receipt Id:".$receiptId." Order not created.",Zend_log::DEBUG,$teamworkLog,true);
                        continue;
                    }
                    
                    $orderId = $orderObj->getId();
                    
                    
                    if (!$orderObj->canCreditmemo()) {
                        Mage::log($credmCnt." - Order Id:".$orderId." Cannot create credit memo for the order.",Zend_log::DEBUG,$teamworkLog,true);
                        continue;
                    }
                    
                    $savedData = array();
                    $qtys = array();
                    $backToStock = array();
                    /* foreach ($savedData as $orderItemId =>$itemData) {
                        if (isset($itemData['qty'])) {
                            $qtys[$orderItemId] = $itemData['qty'];
                        }
                        if (isset($itemData['back_to_stock'])) {
                            $backToStock[$orderItemId] = true;
                        }
                    }
                    $data['qtys'] = $qtys; */
                    
                    $data = array(
                        /* 'qtys' => array(
                            $orderItem->getId() => 1
                        ) */
                    );
                    
                    $ordered_items = $orderObj->getAllItems();
                    
                    $tempArr = array();
                    foreach($ordered_items as $item){     //item detail
                        $savedQtys[$item->getItemId()] = $item->getQtyOrdered();
                        $otherSysQty = $item->getOtherSysQty();
                        if($otherSysQty < 0){
                            $tempArr[$item->getId()] = $item->getQtyOrdered();
                        }
                    }
                    
                    $data["qtys"] = $tempArr;
                    
                    if(count($tempArr) <= 0){
                        Mage::log($credmCnt." - Order Id:".$orderId." Order is not applicable to creditmemo.",Zend_log::DEBUG,$teamworkLog,true);
                        continue;
                    }
                    
                    
                    $invoice = null;
                    $invoiceCollection = $orderObj->getInvoiceCollection();
                    foreach($invoiceCollection as $invoice1){
                        $invoice = $invoice1;
                    }
                    
                    //Mage::log("invoice:".$invoice->getId(),Zend_log::DEBUG,$teamworkLog,true);
                    $service = Mage::getModel('sales/service_order', $orderObj);
                    if (0 && $invoice) {
                        $creditmemo = $service->prepareInvoiceCreditmemo($invoice, $data)->save();
                        $creditmemo->refund();
                    } else {
                        $creditmemo = $service->prepareCreditmemo($data);//->save();
                        
                        foreach ($creditmemo->getAllItems() as $item) {
                            //Mage::log($item->getData(),Zend_log::DEBUG,$teamworkLog,true);
                            $item->register();
                        }
                        
                        $creditmemo->save();
                        
                        $creditmemo->refund();
                        //$creditmemo->setState(2)->save();
                    }
                    
                    /**
                     * Process back to stock flags
                     */
                    foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                        $orderItem = $creditmemoItem->getOrderItem();
                        $parentId = $orderItem->getParentItemId();
                        if (isset($backToStock[$orderItem->getId()])) {
                            $creditmemoItem->setBackToStock(true);
                        } elseif ($orderItem->getParentItem() && isset($backToStock[$parentId]) && $backToStock[$parentId]) {
                            $creditmemoItem->setBackToStock(true);
                        } elseif (empty($savedData)) {
                            $creditmemoItem->setBackToStock(Mage::helper('cataloginventory')->isAutoReturnEnabled());
                        } else {
                            $creditmemoItem->setBackToStock(false);
                        }
                    }
                    
                    
                    
                    Mage::getModel('core/resource_transaction')
                    ->addObject($creditmemo)
                    ->addObject($orderObj)
                    ->save();
                    
                    Mage::log($credmCnt." - Credit memo:".$creditmemo->getId(),Zend_log::DEBUG,$teamworkLog,true);
                        
                    
                 }catch (Exception $e){
                    Mage::log("Exception:".$e->getMessage(),Zend_log::DEBUG,$teamworkLog,true);
                 }
            }
        }
    }
}

Mage::log("Finish...",Zend_log::DEBUG,$teamworkLog,true);
die("Finish...");


