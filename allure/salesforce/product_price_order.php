<?php
require_once ('../../app/Mage.php');
umask(0);
Mage::app();

Mage::app()->setCurrentStore(0);

//set default page size
$PAGE_SIZE   = 2000;
//set default page number
$PAGE_NUMBER = 1;
//log file name
$productHistory = "product_history.log";

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
    "Product2Id"      => "Product2Id",
    "Pricebook2Id"    => "Pricebook2Id",
    "UnitPrice"       => "UnitPrice",
    "IsActive"        => "IsActive"
);

try{
    //get collection of product according to page number, page size & asending order
    
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
    
    $collection = Mage::getResourceModel("sales/order_collection")
    ->addAttributeToSelect("*")
    //->setPageSize($PAGE_SIZE)
    //->setCurPage($PAGE_NUMBER)
    ->setOrder('entity_id', 'asc');
    
    $collection->getSelect()->where("customer_id in(".$custIds.")");
    
    //open or create .csv file
    /* $io           = new Varien_Io_File();
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "product_price_gen";
    $filename     = "PRODUCT_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    $io->streamOpen($filepath , "w+");
    $io->streamLock(true); */
    
    //add header data into .csv file
    //$io->streamWriteCsv($header);
    
    
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "product_price_gen";
    $filename     = "PRODUCT_GEN_".$store."_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    
    $folderPath1   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "product_price_whl";
    $filename1     = "PRODUCT_WHL_".$store."_".$PAGE_NUMBER.".csv";
    $filepath1     = $folderPath1 . DS . $filename1;
    
    $io = new Varien_Io_File();
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    $io->open(array("path" => $folderPath1));
    
    $csv1 = new Varien_File_Csv();
    $csv2 = new Varien_File_Csv();
    
    
    $productArr = array();
    foreach ($collection as $order){
        $items = $order->getAllVisibleItems();
        foreach ($items as $item){
            $productId = Mage::getModel("catalog/product")->getIdBySku($item->getSku());
            if($productId){
                $productArr[$productId] = $productId;
            }
        }
    }
    $row = array($header);
    $row1 = array($header);
    foreach ($productArr as $prodId){
        try{
            $_product = Mage::getModel("catalog/product")->load($prodId);
            
            $wholesalePrice = 0;
            foreach ($_product->getData('group_price') as $gPrice){
                if($gPrice["cust_group"] == 2){ //wholesaler group : 2
                    $wholesalePrice = $gPrice["price"];
                }
            }
            
            //prepare .csv row data using array
            
            /* if($_product->getData("salesforce_product_id")){
                continue;
            } */
            
            $row[] = array(
                "Product2Id"      => $_product->getData("salesforce_product_id"),
                "Pricebook2Id"    => Mage::helper('allure_salesforce')->getGeneralPricebook(),
                "UnitPrice"       => $_product->getPrice(),
                "IsActive"        => ($_product->getStatus())?"true":"false"
            );
            
            //prepare .csv row data using array
            $row1[] = array(
                "Product2Id"      => $_product->getData("salesforce_product_id"),
                "Pricebook2Id"    => Mage::helper('allure_salesforce')->getWholesalePricebook(),
                "UnitPrice"       => $wholesalePrice,
                "IsActive"        => ($_product->getStatus())?"true":"false"
            );
            //add row data into .csv file
            //$io->streamWriteCsv($row);
            //$row = null;
            $_product = null;
        }catch (Exception $ee){
            Mage::log("Sub Exception:".$ee->getMessage(),Zend_Log::DEBUG,$productHistory,true);
            Mage::log("Occured for Product Id:".$_product->getId(),Zend_Log::DEBUG,$productHistory,true);
        }
    }
    $csv1->saveData($filepath,$row);
    $csv2->saveData($filepath1,$row1);
    //$io->close();
}catch (Exception $e){
    Mage::log("Main Exception:".$e->getMessage(),Zend_Log::DEBUG,$productHistory,true);
}

Mage::log("Finish...",Zend_Log::DEBUG,$productHistory,true);
die("Finish...");

