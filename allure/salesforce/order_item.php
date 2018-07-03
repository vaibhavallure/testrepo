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

//.csv file header data
$header = array(
    "OrderId"               => "OrderId",
    "PricebookEntryId"      => "PricebookEntryId",
    "UnitPrice"             => "UnitPrice",
    "Quantity"              => "Quantity"
);

try{
    //get collection of order according to page number, page size & asending order
    $collection = Mage::getResourceModel("sales/order_collection")
    ->addAttributeToSelect("*")
    ->setPageSize($PAGE_SIZE)
    ->setCurPage($PAGE_NUMBER)
    ->setOrder('entity_id', 'asc');
    
    Mage::log("collection size = ".$collection->getSize(),Zend_Log::DEBUG,$orderHistory,true);
    
    //open or create .csv file
    $io           = new Varien_Io_File();
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "order_item";
    $filename     = "ORDER_ITEM_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    $io->streamOpen($filepath , "w+");
    $io->streamLock(true);
    
    //add header data into .csv file
    $io->streamWriteCsv($header);
    
    foreach ($collection as $order){
        try{
            $items = $order->getAllVisibleItems();
            foreach ($items as $item){
                $productId = Mage::getModel("catalog/product")->getIdBySku($item->getSku());
                $product = Mage::getModel("catalog/product")->load($productId);
                $salesforceProductId = "";
                if($product){
                    $salesforcePricebkEntryId = $product->getSalesforceStandardPricebk();
                    if($customerGroup == 2){
                        $salesforcePricebkEntryId = $product->getSalesforceWholesalePricebk();
                    }
                }
                $row = array(
                    "OrderId"               => $order->getSalesforceOrderId(),
                    "PricebookEntryId"      => $salesforceProductId,
                    "UnitPrice"             => $item->getBasePrice(),
                    "Quantity"              => $item->getQtyOrdered()
                );
                //add row data into .csv file
                $io->streamWriteCsv($row);
                $row = null;
            }
        }catch (Exception $ee){
            Mage::log("Sub Exception:".$ee->getMessage(),Zend_Log::DEBUG,$orderHistory,true);
            Mage::log("Occured for Order Id:".$_product->getId(),Zend_Log::DEBUG,$orderHistory,true);
        }
    }
    $io->close();
}catch (Exception $e){
    Mage::log("Main Exception:".$e->getMessage(),Zend_Log::DEBUG,$orderHistory,true);
}

Mage::log("Finish...",Zend_Log::DEBUG,$orderHistory,true);
die("Finish...");

