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
$productHistory = "product_history.log";

/* $product = Mage::getModel("catalog/product")->load(7099);
echo "<pre>";
print_r($product->getData());
die; */

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
    "IsActive"              => "IsActive",
    "Diamond_Color__c"      => "Diamond_Color__c",
    "DisplayUrl"            => "DisplayUrl",
    "ExternalId"            => "ExternalId",
    "Gemstone__c"           => "Gemstone__c",
    "Jewelry_Care__c"       => "Jewelry_Care__c",
    "Metal_Color__c"        => "Metal_Color__c",
    "ProductCode"           => "ProductCode",
    "Description"           => "Description",
    "Family"                => "Family",
    "Name"                  => "Name",
    "StockKeepingUnit"      => "StockKeepingUnit",
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
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "product";
    $filename     = "PRODUCT_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    $io->streamOpen($filepath , "w+");
    $io->streamLock(true);
    
    //add header data into .csv file
    $io->streamWriteCsv($header);
    
    foreach ($collection as $_product){
        try{
            //$_product = Mage::getModel("catalog/product")->load($_product->getId());
            //prepare .csv row data using array
            
            $row = array(
                "IsActive"                  => ($_product->getStatus())?true:false,
                "Diamond_Color__c"          => "",
                "DisplayUrl"                => $_product->getUrlKey(),
                "ExternalId"                => $_product->getId(),
                "Gemstone__c"               => $gemstoneArr[$_product->getGemstone()],
                "Jewelry_Care__c"           => $_product->getJewelryCare(),
                "Metal_Color__c"            => $metalColorArr[$_product->getMetal()],
                "ProductCode"               => $_product->getId(),
                "Description"               => $_product->getDescription(),
                "Family"                    => $_product->getTypeId(),
                "Name"                      => $_product->getName(),
                "StockKeepingUnit"          => $_product->getSku(),
                "Return_Policy__c"          => $_product->getReturnPolicy(),
                "Tax_Class_Id__c"           => $_product->getTaxClassId(),
                "Vendor_Item_No__c"         => $_product->getVendorItemNo()
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
