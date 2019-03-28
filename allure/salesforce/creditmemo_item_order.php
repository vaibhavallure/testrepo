<?php
require_once ('../../app/Mage.php');
umask(0);
Mage::app();

Mage::app()->setCurrentStore(0);

//set default page size
$PAGE_SIZE   = 500;
//set default page number
$PAGE_NUMBER = 1;
//log file name
$creditmemoHistory = "creditmemo_history.log";

echo "<style>
.salesforce-error{
    color: #f90d0d;
    text-align: center;
    margin-top: 10px;
}
</style>";

$pageNumber = $_GET["page"];
if(empty($pageNumber)){
    die("<p class='salesforce-error'>Please specify page number.</p>");
}

if(is_numeric($pageNumber)){
    $PAGE_NUMBER = (int) $pageNumber;
}else{
    die("<p class='salesforce-error'>Please specify page number in only number format.
        (eg: 1 or 2 or 3 etc...)</p>");
}

$size       = $_GET['size'];
if(empty(!$size)){
    $PAGE_SIZE = $size;
}

//.csv file header data
$header = array(
    "ID"                => "ID",
    "Credit_Memo__c"    => "Credit_Memo__c"
);

try{
    $filepath = $_GET["file"];
    if(empty($filepath)){
        die("empty file path");
    }
    
    $file = Mage::getBaseDir("var") . DS. $filepath;
    
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
    //->setPageSize($PAGE_SIZE)
    //->setCurPage($PAGE_NUMBER)
    ->setOrder('entity_id', 'asc');
    
    $collectionT->getSelect()->where("customer_id in(".$custIds.")");
    
    //echo "<pre>";
    $ordArr = array();
    foreach ($collectionT as $ord){
        $ordArr[] = $ord->getId();
    }
    
    //get collection of order according to page number, page size & asending order
    $collection = Mage::getResourceModel("sales/order_creditmemo_collection")
    ->addAttributeToSelect("*")
    ->addFieldToFilter("order_id",array("in"=>$ordArr))
    //->setPageSize($PAGE_SIZE)
    //->setCurPage($PAGE_NUMBER)
    ->setOrder('entity_id', 'asc');
    
    Mage::log("collection size = ".$collection->getSize(),Zend_Log::DEBUG,$creditmemoHistory,true);
    
    //open or create .csv file
    /* $io           = new Varien_Io_File();
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "creditmemo_item";
    $filename     = "CREDITMEMO_ITEM_".$store."_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    $io->streamOpen($filepath , "w+");
    $io->streamLock(true); */
    
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "creditmemo_item";
    $filename     = "CREDITMEMO_".$store."_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    
    $io = new Varien_Io_File();
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    
    $csv = new Varien_File_Csv();
    
    //add header data into .csv file
    //$io->streamWriteCsv($header);
    
    $row = array($header);
    
    //add header data into .csv file
    //$io->streamWriteCsv($header);
    
    foreach ($collection as $creditMemo){
        try{
            $items      = $creditMemo->getAllItems();
            foreach ($items as $item){
                $orderItemId = $item->getOrderItemId();
                $orderItem = Mage::getModel("sales/order_item")->load($orderItemId);
                if(!$orderItem->getId()){
                    continue;
                }
                $salesforceItemId = $orderItem->getSalesforceItemId();
                $row[] = array(
                    "ID"                => $salesforceItemId,
                    "Credit_Memo__c"    => $creditMemo->getSalesforceCreditmemoId()
                );
                //add row data into .csv file
                //$io->streamWriteCsv($row);
                //$row = null;
            }
        }catch (Exception $ee){
            Mage::log("Sub Exception:".$ee->getMessage(),Zend_Log::DEBUG,$creditmemoHistory,true);
            Mage::log("Occured for Shipment Id:".$creditMemo->getId(),Zend_Log::DEBUG,$creditmemoHistory,true);
        }
    }
    $csv->saveData($filepath,$row);
    //$io->close();
}catch (Exception $e){
    Mage::log("Main Exception:".$e->getMessage(),Zend_Log::DEBUG,$creditmemoHistory,true);
}

Mage::log("Finish...",Zend_Log::DEBUG,$creditmemoHistory,true);
die("Finish...");

