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

//.csv file header data
$header = array(
    "Magento_Tracker_Id__c" => "Magento_Tracker_Id__c",
    "Shipment__c"           => "Shipment__c",
    "Name"                  => "Name",
    "Tracking_Number__c"    => "Tracking_Number__c",
    "Carrier__c"            => "Carrier__c"
);

try{
    //get collection of order according to page number, page size & asending order
    $collection = Mage::getResourceModel("sales/order_shipment_collection")
    ->addAttributeToSelect("*")
    ->setPageSize($PAGE_SIZE)
    ->setCurPage($PAGE_NUMBER)
    ->setOrder('entity_id', 'asc');
    
    Mage::log("collection size = ".$collection->getSize(),Zend_Log::DEBUG,$shipmentHistory,true);
    
    //open or create .csv file
   /*  $io           = new Varien_Io_File();
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "shipment_track";
    $filename     = "SHIPMENT_TRACK_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    $io->streamOpen($filepath , "w+");
    $io->streamLock(true); */
    
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "shipment_track";
    $filename     = "SHIPMENT_TRACK_".$store."_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    
    $io = new Varien_Io_File();
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    
    $csv = new Varien_File_Csv();
    //add header data into .csv file
    //$io->streamWriteCsv($header);
    
    $row = array($header);
    foreach ($collection as $shipment){
        try{
            $tracksNumCollection = $shipment->getAllTracks();
            foreach ($tracksNumCollection as $track){
                $row[] = array(
                    "Magento_Tracker_Id__c" => $track->getData("entity_id"),
                    "Shipment__c"           => $shipment->getSalesforceShipmentId(),
                    "Name"                  => $track->getData("title"),
                    "Tracking_Number__c"    => $track->getData("track_number"),
                    "Carrier__c"            => $track->getData("carrier_code")
                );
                //add row data into .csv file
                //$io->streamWriteCsv($row);
                //$row = null;
            }
        }catch (Exception $ee){
            Mage::log("Sub Exception:".$ee->getMessage(),Zend_Log::DEBUG,$shipmentHistory,true);
            Mage::log("Occured for Shipment Id:".$shipment->getId(),Zend_Log::DEBUG,$shipmentHistory,true);
        }
    }
    $csv->saveData($filepath,$row);
   // $io->close();
}catch (Exception $e){
    Mage::log("Main Exception:".$e->getMessage(),Zend_Log::DEBUG,$shipmentHistory,true);
}

Mage::log("Finish...",Zend_Log::DEBUG,$shipmentHistory,true);
die("Finish...");

