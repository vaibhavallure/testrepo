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
$shipmentHistory = "shipment_history.log";

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
    "Increment_ID__c"       => "Increment_ID__c",
    "Name"                  => "Name",
    "Customer_Id__c"        => "Customer_Id__c",
    "Order_Id__c"           => "Order_Id__c",
    "Quantity__c"           => "Quantity__c",
    "Shipping_Label__c"     => "Shipping_Label__c",
    "Weight__c"             => "Weight__c",
    //"Carrier__c"            => "Carrier__c",
    //"Track_Number__c"       => "Track_Number__c",
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
    $collection = Mage::getResourceModel("sales/order_shipment_collection")
    ->addAttributeToSelect("*")
    ->addFieldToFilter("order_id",array("in"=>$ordArr))
    //->setPageSize($PAGE_SIZE)
    //->setCurPage($PAGE_NUMBER)
    ->setOrder('entity_id', 'asc');
    
    //echo $collection->getSelect()->__toString();
    
    Mage::log("collection size = ".$collection->getSize(),Zend_Log::DEBUG,$shipmentHistory,true);
    
    //open or create .csv file
    $io           = new Varien_Io_File();
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "shipment";
    $filename     = "SHIPMENT_".$store."_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    $io->streamOpen($filepath , "w+");
    $io->streamLock(true);
    
    //add header data into .csv file
    $io->streamWriteCsv($header);
    
    foreach ($collection as $shipment){
        try{
            $order = $shipment->getOrder();
            $salesforceOrderId  = $order->getSalesforceOrderId();
            $customerId         = $shipment->getCustomerId();
            $incrementId        = $shipment->getIncrementId();
            $orderIncrementId   = $order->getIncrementId();
            $totalQty = $shipment->getTotalQty();
            $shippingLabel = $shipment->getShippingLabel();
            $weight = $order->getWeight();
            /* $tracksNumCollection = $shipment->getAllTracks();
            $trackNumberArr = array();
            $titlesArr = array();
            foreach ($tracksNumCollection as $track){
                $trackNumberArr[]   = $track->getData("track_number");
                $titlesArr[]        = $track->getData("title");
            }
            $carrierTitles = implode(",", $titlesArr);
            $trackNums = implode(",", $trackNumberArr); */
            
            $row = array(
                "Increment_ID__c"       => $incrementId,
                "Name"                  => "Shipment for Order #".$orderIncrementId,
                "Customer_Id__c"        => $customerId,
                "Order_Id__c"           => $orderIncrementId,
                "Quantity__c"           => $totalQty,
                "Shipping_Label__c"     => "",
                "Weight__c"             => $weight,
                //"Carrier__c"            => $carrierTitles,
                //"Track_Number__c"       => $trackNums,
                "Order__c"              => $salesforceOrderId
            );
            //add row data into .csv file
            $io->streamWriteCsv($row);
            $row = null;
        }catch (Exception $ee){
            Mage::log("Sub Exception:".$ee->getMessage(),Zend_Log::DEBUG,$shipmentHistory,true);
            Mage::log("Occured for Shipment Id:".$shipment->getId(),Zend_Log::DEBUG,$shipmentHistory,true);
        }
    }
    $io->close();
}catch (Exception $e){
    Mage::log("Main Exception:".$e->getMessage(),Zend_Log::DEBUG,$shipmentHistory,true);
}

Mage::log("Finish...",Zend_Log::DEBUG,$shipmentHistory,true);
die("Finish...");

