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
    "Credit_Memo_Id__c"     => "Credit_Memo_Id__c",
    "Order_Id__c"           => "Order_Id__c",
    "Name"                  => "Name",
    "Stauts__c"             => "Stauts__c",
    "Store__c"              => "Store__c",
    "Adjustment__c"         => "Adjustment__c",
    "Created_At__c"         => "Created_At__c",
    "Discount_Amount__c"    => "Discount_Amount__c",
    "Grand_Total__c"        => "Grand_Total__c",
    "Order_Date__c"         => "Order_Date__c",
    "Shipping_Amount__c"    => "Shipping_Amount__c",
    "Subtotal__c"           => "Subtotal__c",
    "Tax_Amount__c"         => "Tax_Amount__c",
    "Order__c"              => "Order__c"
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
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "creditmemo";
    $filename     = "CREDITMEMO_".$store."_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    $io->streamOpen($filepath , "w+");
    $io->streamLock(true); */
    
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "creditmemo";
    $filename     = "CREDITMEMO_".$store."_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    
    $io = new Varien_Io_File();
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    
    $csv = new Varien_File_Csv();
    
    //add header data into .csv file
    //$io->streamWriteCsv($header);
    
    $row = array($header);
    
    $ostores = Mage::helper("allure_virtualstore")->getVirtualStores();
    $oldStoreArr = array();
    foreach ($ostores as $storeO){
        $oldStoreArr[$storeO->getId()] = $storeO->getName();
    }
    $oldStoreArr[0] = "Admin";
    
    foreach ($collection as $creditMemo){
        try{
            $order                  = $creditMemo->getOrder();
            $salesforceOrderId      = $order->getSalesforceOrderId();
            $incrementId            = $creditMemo->getIncrementId();
            $orderIncrementId       = $order->getIncrementId();
            $baseAdjustment         = $creditMemo->getBaseAdjustment();
            $createdAt              = $creditMemo->getCreatedAt();
            $status                 = $creditMemo->getState();
            $discountAmount         = $creditMemo->getBaseDiscountAmount();
            $grandTotal             = $creditMemo->getBaseGrandTotal();
            $orderDate              = $order->getCreatedAt();
            $shippingAmount         = $creditMemo->getBaseShippingAmount();
            $storeId                = $creditMemo->getStoreId();
            $subtotal               = $creditMemo->getBaseSubtotal();
            $taxAmount              = $creditMemo->getBaseTaxAmount();
            
            $row[] = array(
                "Credit_Memo_Id__c"     => $incrementId,
                "Order_Id__c"           => $orderIncrementId,
                "Name"                  => "Credit Memo for Order #".$orderIncrementId,
                "Stauts__c"             => $status,
                "Store__c"              => $oldStoreArr[$storeId],
                "Adjustment__c"         => $baseAdjustment,
                "Created_At__c"         => date("Y-m-d",strtotime($createdAt)),
                "Discount_Amount__c"    => $discountAmount,
                "Grand_Total__c"        => $grandTotal,
                "Order_Date__c"         => date("Y-m-d",strtotime($orderDate)),
                "Shipping_Amount__c"    => $shippingAmount,
                "Subtotal__c"           => $subtotal,
                "Tax_Amount__c"         => $taxAmount,
                "Order__c"              => $salesforceOrderId
            );
            //add row data into .csv file
            //$io->streamWriteCsv($row);
            //$row = null;
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

