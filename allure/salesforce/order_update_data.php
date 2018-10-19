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
$orderHistory = "order_history.log";

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

$store = $_GET['store'];

//.csv file header data
$header = array(
    "ID"                        => "ID",
    "Increment_Id__c"           => "Increment_Id__c",
    "Delivery_Method__c"        => "Delivery_Method__c",
    "Payment_Method__c"         => "Payment_Method__c",
    "Card_Type__c"              => "Card_Type__c",
    "Card_Number__c"            => "Card_Number__c",
    "Transaction_Id__c"         => "Transaction_Id__c"
);

try{
    
    
    //get collection of order according to page number, page size & asending order
    
    $filepath = $_GET["file"];
    if(empty($filepath)){
        die("empty file path");
    }
    
    $file = Mage::getBaseDir("var") . DS. $filepath;
    
    $ioR = new Varien_Io_File();
    $ioR->streamOpen($file, 'r');
    
    $orderIdIdx = 0;
    $orderIdsArr = array();
    $ioR->streamReadCsv();
    while($csvData = $ioR->streamReadCsv()){
        $orderIdsArr[$csvData[$orderIdIdx]] = $csvData[$orderIdIdx];
    }
    
    $orderIds = implode(",", $orderIdsArr);
    
    $collection = Mage::getResourceModel("sales/order_collection")
    ->addAttributeToSelect("*")
    ->setOrder('entity_id', 'asc');
    
    $collection->getSelect()->where("entity_id in(".$orderIds.")");
    
    //open or create .csv file
    $io           = new Varien_Io_File();
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "order";
    $filename     = "ORDER_UPDATE".$store."_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    $io->streamOpen($filepath , "w+");
    $io->streamLock(true);
    
    //add header data into .csv file
    $io->streamWriteCsv($header);
    
    foreach ($collection as $order){
        try{
            
            //$_order = Mage::getModel("sales/order")->load($order->getId());
            
            $DeliveryMethod = trim($order->getData("order_type"));
            if($DeliveryMethod == "Multiple - Main"){
                $DeliveryType = "Main";
            }else if ($DeliveryMethod == "Multiple - Backorder"){
                $DeliveryType = "Backorder";
            }else{
                $DeliveryType = "Single";
            }
            
            $payment = $order->getPayment();
            
            $paymentMethod  = $payment->getMethodInstance()->getTitle();
            
            $card_code = $payment->getData('cc_type');
            $cardType = "";
            $aType = Mage::getSingleton('payment/config')->getCcTypes();
            if (isset($aType[$card_code])) {
                $cardType = $aType[$card_code];
            }
            
            
            $last4Digits  = ($payment->getCcLast4())?"XXXX-".$payment->getCcLast4():"";
            
            $transId  = $payment->getLastTransId();
            
            $row = array(
                "ID"                        => $order->getSalesforceOrderId(),
                "Increment_Id__c"           => $order->getIncrementId(),
                "Delivery_Method__c"        => $DeliveryType,
                "Payment_Method__c"         => $paymentMethod,
                "Card_Type__c"              => $cardType,
                "Card_Number__c"            => $last4Digits,
                "Transaction_Id__c"         => $transId
            );
            $io->streamWriteCsv($row);
            $row = null;
            Mage::log("order saved:".$order->getId(),Zend_Log::DEBUG,$orderHistory,true);
        }catch (Exception $ee){
            Mage::log("Sub Exception:".$ee->getMessage(),Zend_Log::DEBUG,$orderHistory,true);
            Mage::log("Occured for Order Id:".$order->getId(),Zend_Log::DEBUG,$orderHistory,true);
        }
    }
    $io->close();
}catch (Exception $e){
    Mage::log("Main Exception:".$e->getMessage(),Zend_Log::DEBUG,$orderHistory,true);
}


function encodeValue($str){
    //iconv('UTF-8', 'ISO-8859-1//TRSANSLIT', $str);
    return @iconv('UTF-8', 'ISO-8859-1//TRSANSLIT', $str);
}

Mage::log("Finish...",Zend_Log::DEBUG,$orderHistory,true);
die("Finish...");

