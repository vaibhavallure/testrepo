<?php
require_once ('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);

$log_file = "invoice_pdf_salesforce.log";

$orderIds = array(297370,297368,297372);

try{
    
    $CURRENT_PAGE = $_GET["page"];
    $PAGE_SIZE = $_GET["size"];
    if(empty($CURRENT_PAGE) || empty($PAGE_SIZE)){
        die("currentPage OR pageSize missing");
    } 
    
    if(is_numeric($CURRENT_PAGE)){
    $CURRENT_PAGE = (int) $CURRENT_PAGE;
    }else{
        die("<p class='salesforce-error'>Please specify Current page in only number format.
            (eg: 1 or 2 or 3 etc...)</p>");
    }
    if(is_numeric($PAGE_SIZE)){
    $PAGE_SIZE = (int) $PAGE_SIZE;
    }else{
        die("<p class='salesforce-error'>Please specify Page size in only number format.
            (eg: 1 or 2 or 3 etc...)</p>");
    }
    /* $file = Mage::getBaseDir("var") . DS. $tFile;
    
    $ioR = new Varien_Io_File();
    $ioR->streamOpen($file, 'r');
    
    $customerIdIdx = 0;
    $customerArr = array();
    $ioR->streamReadCsv();
    while($csvData = $ioR->streamReadCsv()){
        $customerArr[$csvData[$customerIdIdx]] = $csvData[$customerIdIdx];
    }
    
    $custIds = implode(",", $customerArr);
    
    $collectionT = Mage::getResourceModel("sales/order_collection")
    ->addAttributeToSelect("*")
    ->setOrder('entity_id', 'asc');
    
    $collectionT->getSelect()->where("customer_id in(".$custIds.")");
    
    $ordArr = array();
    foreach ($collectionT as $ord){
        $ordArr[] = $ord->getId();
    } */
    
    $header = array(
        "Title"           => "Title",
        "Description"     => "Description",
        "VersionData"     => "VersionData",
        "PathOnClient"    => "PathOnClient"
    );
    
    $io           = new Varien_Io_File();
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "invoice_pdf" ;
    $filename    = "INVOICE_PDF.csv";
    
    $TfolderPath = $folderPath . DS . "PDF";
    
    $filepath     = $folderPath . DS . $filename;
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $TfolderPath));
    $io->streamOpen($filepath , "w+");
    $io->streamLock(true);
    
    $io->streamWriteCsv($header);
    
    $orderCollection = Mage::getModel("sales/order")->getCollection()
    //->addFieldToFilter("entity_id",array("in" => $orderIds))
    ->setCurPage($CURRENT_PAGE)
    ->setPageSize($PAGE_SIZE)
    ->setOrder('entity_id', 'asc');

    foreach ($orderCollection as $order){
        try{
            if(!$order->hasInvoices()){
                continue;
            }
            
            $filename1 = $order->getIncrementId().".pdf";
            
            $filepath1     = $TfolderPath . DS . $filename1;
            
            $invoices = $order->getInvoiceCollection();
            
            if(Mage::helper("core")->isModuleEnabled("Allure_Pdf")){
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getCompressPdf($invoices,true);
            }else {
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
            }
            
            file_put_contents($filepath1,$pdf->render());
            
            $row = array(
                "Title"           => $filename1,
                "Description"     => $order->getIncrementId(),
                "VersionData"     => "FOLDERDATA" . DS . $filename1,
                "PathOnClient"    => "FOLDERDATA" . DS . $filename1
            );
            
            $io->streamWriteCsv($row);
            $row = null;
        }catch (Exception $e){
            Mage::log($e->getMessage(),Zend_Log::DEBUG,$log_file,true);
        }
    }
    $io->close();
}catch (Exception $e){
    Mage::log($e->getMessage(),Zend_Log::DEBUG,$log_file,true);
}
Mage::log("Finish...",Zend_Log::DEBUG,$log_file,true);
die("Finish...");