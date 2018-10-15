<?php
require_once ('../../app/Mage.php');
umask(0);
Mage::app();

Mage::app()->setCurrentStore(0);

//set default page size
$PAGE_SIZE   = 10;
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
    "StockKeepingUnit"      => "StockKeepingUnit",
    "ProductCode"           => "ProductCode",
    "IsActive"              => "IsActive",
    "Diamond_Color__c"      => "Diamond_Color__c",
    "DisplayUrl"            => "DisplayUrl",
    "ExternalId"            => "ExternalId",
    "Gemstone__c"           => "Gemstone__c",
    "Jewelry_Care__c"       => "Jewelry_Care__c",
    "Metal_Color__c"        => "Metal_Color__c",
    "Description"           => "Description",
    "Family"                => "Family",
    "Name"                  => "Name",
    "Return_Policy__c"      => "Return_Policy__c",
    "Tax_Class_Id__c"       => "Tax_Class_Id__c",
    "Vendor_Item_No__c"     => "Vendor_Item_No__c"
);

//get array of metal color of product
$metalColorArr = getOptionArray("metal");
//get array of gemstones of product
$gemstoneArr   = getOptionArray("gemstone");

try{
    //get collection of product according to page number, page size & asending order
    $collection = Mage::getModel("allure_salesforce/deletedproduct")->getCollection()
    ->setPageSize($PAGE_SIZE)
    ->setCurPage($PAGE_NUMBER)
    ->setOrder('product_id', 'asc');
    //$collection->getSelect()->group('sku');
    
    //echo $collection->getSelect()->__toString();
    //die;
    
    Mage::log("collection size = ".$collection->getSize(),Zend_Log::DEBUG,$productHistory,true);
    
    //open or create .csv file
    /* $io           = new Varien_Io_File();
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "old_product";
    $filename     = "PRODUCT_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    $io->streamOpen($filepath , "w+");
    $io->streamLock(true); */
    
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "old_product";
    $filename     = "PRODUCT_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    
    $io = new Varien_Io_File();
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    
    $csv = new Varien_File_Csv();
    //$csv->saveData($filepath,$header);
    
    //add header data into .csv file
   // $io->streamWriteCsv($header);
    $row = array($header);
    foreach ($collection as $item){
        try{
            Mage::log("product sku:".$item->getSku()." deleted.",Zend_Log::DEBUG,$productHistory,true);
            
            $skuArr = explode("|", $item->getSku());
            $metal = "";
            if(count($skuArr) > 1){
                $metal = $skuArr[1];
                if(is_numeric($metal)){
                    $metal = "";
                }
            }
            
            
            //prepare .csv row data using array
            $row[] = array(
                "StockKeepingUnit"          => $item->getSku(),
                "ProductCode"               => $item->getProductId(),
                "IsActive"                  => "false",
                "Diamond_Color__c"          => "",
                "DisplayUrl"                => "",
                "ExternalId"                => $item->getProductId(),
                "Gemstone__c"               => "",
                "Jewelry_Care__c"           => "",
                "Metal_Color__c"            => $metal,
                "Description"               => "",
                "Family"                    => ($item->getProductType()=="configurable")?"simple":$item->getProductType(),
                "Name"                      => $item->getName(),
                "Return_Policy__c"          => "",
                "Tax_Class_Id__c"           => "",
                "Vendor_Item_No__c"         => ""
            );
            //add row data into .csv file
            //$io->streamWriteCsv($row);
            //$row = null;
        }catch (Exception $ee){
            Mage::log("Sub Exception:".$ee->getMessage(),Zend_Log::DEBUG,$productHistory,true);
            Mage::log("Occured for Product Id:".$_product->getId(),Zend_Log::DEBUG,$productHistory,true);
        }
    }
    $csv->saveData($filepath,$row);
    //$io->close();
}catch (Exception $e){
    Mage::log("Main Exception:".$e->getMessage(),Zend_Log::DEBUG,$productHistory,true);
}

Mage::log("Finish...",Zend_Log::DEBUG,$productHistory,true);
die("Finish...");


/**
 * get product attribute code value "label" using attribute_code & attribute_value
 */
function getOptionLabel($attributeCode,$attributeValue){
    $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$attributeCode);
    $options = $attribute->getSource()->getAllOptions();
    foreach ($options as $option){
        if($option["value"] == $attributeValue){
            return $option["label"];
        }
    }
    return null;
}

/**
 * get array of value-label using attribute code
 */
function getOptionArray($attributeCode){
    $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$attributeCode);
    $options = $attribute->getSource()->getAllOptions();
    $optionArray = array();
    foreach ($options as $option){
        $optionArray[$option["value"]] = $option["label"];
    }
    return $optionArray;
}
