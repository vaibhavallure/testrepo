<?php
require_once ('../../app/Mage.php');
umask(0);
Mage::app();

Mage::app()->setCurrentStore(0);

//set default page size
$PAGE_SIZE   = 1000;
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

//.csv file header data
$header = array(
    "Product2Id"      => "Product2Id",
    "Pricebook2Id"    => "Pricebook2Id",
    "UnitPrice"       => "UnitPrice",
    "IsActive"        => "IsActive"
);

try{
    //get collection of product according to page number, page size & asending order
    $collection = Mage::getResourceModel("catalog/product_collection")
    ->addAttributeToSelect("*")
    ->addAttributeToSelect("metal")
    ->addAttributeToSelect("return_policy")
    ->addAttributeToSelect("jewelry_care")
    ->addAttributeToSelect("description")
    ->setPageSize($PAGE_SIZE)
    ->setCurPage($PAGE_NUMBER)
    ->setOrder('entity_id', 'asc');
    
    Mage::log("collection size = ".$collection->getSize(),Zend_Log::DEBUG,$productHistory,true);
    
    //open or create .csv file
    $io           = new Varien_Io_File();
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "product_price_whl";
    $filename     = "PRODUCT_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    $io->streamOpen($filepath , "w+");
    $io->streamLock(true);
    
    //add header data into .csv file
    $io->streamWriteCsv($header);
    
    foreach ($collection as $product){
        try{
            $_product = Mage::getModel("catalog/product")->load($product->getId());
            $wholesalePrice = 0;
            foreach ($_product->getData('group_price') as $gPrice){
                if($gPrice["cust_group"] == 2){ //wholesaler group : 2
                    $wholesalePrice = $gPrice["price"];
                }
            }
            //prepare .csv row data using array
            $row = array(
                "Product2Id"      => $_product->getData("salesforce_product_id"),
                "Pricebook2Id"    => Mage::helper('allure_salesforce')->getWholesalePricebook(),
                "UnitPrice"       => $wholesalePrice,
                "IsActive"        => ($_product->getStatus())?true:false
            );
            //add row data into .csv file
            $io->streamWriteCsv($row);
            $row = null;
        }catch (Exception $ee){
            Mage::log("Sub Exception:".$ee->getMessage(),Zend_Log::DEBUG,$productHistory,true);
            Mage::log("Occured for Product Id:".$_product->getId(),Zend_Log::DEBUG,$productHistory,true);
        }
    }
    $io->close();
}catch (Exception $e){
    Mage::log("Main Exception:".$e->getMessage(),Zend_Log::DEBUG,$productHistory,true);
}

Mage::log("Finish...",Zend_Log::DEBUG,$productHistory,true);
die("Finish...");

