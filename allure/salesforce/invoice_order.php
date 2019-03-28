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
$invoiceHistory = "invoice_history.log";

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
    "Invoice_Id__c"             => "Invoice_Id__c",
    "Order_Id__c"               => "Order_Id__c",
    "Name"                      => "Name",
    "Store__c"                  => "Store__c",
    "Invoice_Date__c"           => "Invoice_Date__c",
    "Order_Date__c"             => "Order_Date__c",
    "Shipping_Amount__c"        => "Shipping_Amount__c",
    "Status__c"                 => "Status__c",
    "Subtotal__c"               => "Subtotal__c",
    "Grand_Total__c"            => "Grand_Total__c",
    "Tax_Amount__c"             => "Tax_Amount__c",
    "Total_Quantity__c"         => "Total_Quantity__c",
    "Discount_Amount__c"        => "Discount_Amount__c",
    "Discount_Descrition__c"    => "Discount_Descrition__c",
    "Order__c"                  => "Order__c"
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
    $collection = Mage::getResourceModel("sales/order_invoice_collection")
    ->addAttributeToSelect("*")
    ->addFieldToFilter("order_id",array("in"=>$ordArr))
    //->setPageSize($PAGE_SIZE)
    //->setCurPage($PAGE_NUMBER)
    ->setOrder('entity_id', 'asc');
    
    Mage::log("collection size = ".$collection->getSize(),Zend_Log::DEBUG,$invoiceHistory,true);
    
    //open or create .csv file
    $io           = new Varien_Io_File();
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "invoice";
    $filename     = "INVOICE_".$store."_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    $io->streamOpen($filepath , "w+");
    $io->streamLock(true);
    
    //add header data into .csv file
    $io->streamWriteCsv($header);
    
    $ostores = Mage::helper("allure_virtualstore")->getVirtualStores();
    $oldStoreArr = array();
    foreach ($ostores as $storeO){
        $oldStoreArr[$storeO->getId()] = $storeO->getName();
    }
    $oldStoreArr[0] = "Admin";
    
    foreach ($collection as $invoice){
        try{
            $order = $invoice->getOrder();
            //$order = Mage::getModel("sales/order")->load($order->getId());
                
            $baseGrandTotal = $invoice->getBaseGrandTotal();
            $basTaxAmount = $invoice->getBaseTaxAmount();
            $baseShippingAmount = $invoice->getBaseShippingAmount();
            $baseSubtotal = $invoice->getBaseSubtotal();
            $baseDiscountAmount = $invoice->getBaseDiscountAmount();
            $discountDescrption = $invoice->getDiscountDescription();
            $createdAt = $invoice->getCreatedAt();
            $invoiceIncrementId = $invoice->getIncrementId();
                
            $orderDate = $order->getCreatedAt();
            $orderIncrementId = $order->getIncrementId();
                
            $status = $invoice->getState();
            $storeId = $invoice->getStoreId();
                
            $totalQty = $invoice->getTotalQty();
            
            $salesforceOrderId = $order->getSalesforceOrderId();
                
            $row = array(
                "Invoice_Id__c"             => $invoiceIncrementId,
                "Order_Id__c"               => $orderIncrementId,
                "Name"                      => "Invoice for Order #".$orderIncrementId,
                "Store__c"                  => $oldStoreArr[$storeId],
                "Invoice_Date__c"           => date("Y-m-d",strtotime($createdAt)),
                "Order_Date__c"             => date("Y-m-d",strtotime($orderDate)),
                "Shipping_Amount__c"        => $baseShippingAmount,
                "Status__c"                 => $status,
                "Subtotal__c"               => $baseSubtotal,
                "Grand_Total__c"            => $baseGrandTotal,
                "Tax_Amount__c"             => $basTaxAmount,
                "Total_Quantity__c"         => $totalQty,
                "Discount_Amount__c"        => $baseDiscountAmount,
                "Discount_Descrition__c"    => "",
                "Order__c"                  => $salesforceOrderId
            );
            
            //add row data into .csv file
            $io->streamWriteCsv($row);
            $row = null;
        }catch (Exception $ee){
            Mage::log("Sub Exception:".$ee->getMessage(),Zend_Log::DEBUG,$invoiceHistory,true);
            Mage::log("Occured for Invoice Id:".$invoice->getId(),Zend_Log::DEBUG,$invoiceHistory,true);
        }
    }
    $io->close();
}catch (Exception $e){
    Mage::log("Main Exception:".$e->getMessage(),Zend_Log::DEBUG,$invoiceHistory,true);
}

Mage::log("Finish...",Zend_Log::DEBUG,$invoiceHistory,true);
die("Finish...");

