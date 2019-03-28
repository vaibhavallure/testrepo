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
$size       = $_GET['size'];

if(empty($pageNumber)){
    die("<p class='salesforce-error'>Please specify page number.</p>");
}

if(is_numeric($pageNumber)){
    $PAGE_NUMBER = (int) $pageNumber;
}else{
    die("<p class='salesforce-error'>Please specify page number in only number format. 
        (eg: 1 or 2 or 3 etc...)</p>");
}

if(empty(!$size)){
    $PAGE_SIZE = $size;
}

//.csv file header data
$header = array(
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
    "StockKeepingUnit"      => "StockKeepingUnit",
    "Return_Policy__c"      => "Return_Policy__c",
    "Tax_Class_Id__c"       => "Tax_Class_Id__c",
    "Vendor_Item_No__c"     => "Vendor_Item_No__c",
    "Location__c"           => "Location__c",
    "Amount__c"             => "Amount__c",
    "FR_SIZE__c"            => "FR_SIZE__c",
    "SIDE_EAR__c"           => "SIDE_EAR__c",
    "DIRECTION__c"          => "DIRECTION__c",
    "NECK_LENGT__c"         => "NECK_LENGT__c",
    "NOSE_BEND__c"          => "NOSE_BEND__c",
    "C_LENGTH__c"           => "C_LENGTH__c",
    "SIZE__c"               => "SIZE__c",
    "GAUGE__c"              => "GAUGE__c",
    "POST_OPTIO__c"         => "POST_OPTIO__c",
    "RISE__c"               => "RISE__c",
    "S_Length__c"           => "S_Length__c",
    "PLACEMENT__c"          => "PLACEMENT__c",
    "Material__c"           => "Material__c"
);


$header1 = array(
    "StockKeepingUnit"          => "StockKeepingUnit",
    "ProductCode"               => "ProductCode",
    "IsActive"                  => "false",
    "ExternalId"                => "IsActive",
    "Metal_Color__c"            => "Metal_Color__c",
    "Family"                    => "Family",
    "Name"                      => "Name"
);

//get array of metal color of product
$metalColorArr = getOptionArray("metal");
//get array of gemstones of product
$gemstoneArr   = getOptionArray("gemstone");

$amountArr      = getOptionArray("amount");      //amount - select
$frSizeArr      = getOptionArray("fr_size");      //fr_size - select
$sideEarArr     = getOptionArray("side_ear");     //side_ear - select
$directionArr   = getOptionArray("direction"); //direction - select
$neckLengthArr  = getOptionArray("neck_lengt"); //neck_lengt - select
$noseBendArr    = getOptionArray("nose_bend");    //nose_bend - select
$cLengthArr     = getOptionArray("c_length");      //c_length - select
$sizeArr        = getOptionArray("size");            //size - select
$gaugeArr       = getOptionArray("gauge");           //gauge - select
$postOptionArr  = getOptionArray("post_optio"); //post_optio - select
$riseArr        = getOptionArray("rise");            //rise - select
$sLengthArr     = getOptionArray("s_length");    //s_length - select
$placementArr   = getOptionArray("placement"); //placement - select
$materialArr    = getOptionArray("material"); //material - multiselect

$store = $_GET['store'];

try{
    
    $attrSets = Mage::getResourceModel('eav/entity_attribute_set_collection')
    ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
    ->load()
    ->toOptionHash();
    
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
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "product";
    $filename     = "PRODUCT_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    $io->streamOpen($filepath , "w+");
    $io->streamLock(true); */
    
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "product";
    $filename     = "PRODUCT_".$store."_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    
    $folderPath1   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "old_product";
    $filename1    = "PRODUCT_".$store."_".$PAGE_NUMBER.".csv";
    $filepath1     = $folderPath1 . DS . $filename1;
    
    //add header data into .csv file
    //$io->streamWriteCsv($header);
    
    $io = new Varien_Io_File();
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    
    $io->open(array("path" => $folderPath1));
    
    $csv = new Varien_File_Csv();
    $csv1 = new Varien_File_Csv();
    
    $productArr = array();
    $row1 = array($header1);
    foreach ($collection as $order){
        $items = $order->getAllVisibleItems();
        foreach ($items as $item){
            $productId = Mage::getModel("catalog/product")->getIdBySku($item->getSku());
            if($productId){
                $productArr[$productId] = $productId;
            }else{
                if($item->getProductType()!="configurable"){
                    $skuArr = explode("|", $item->getSku());
                    $metal = "";
                    if(count($skuArr) > 1){
                        $metal = $skuArr[1];
                        if(is_numeric($metal)){
                            $metal = "";
                        }
                    }
                    
                    
                    //prepare .csv row data using array
                    $row1[$item->getSku()] = array(
                        "StockKeepingUnit"          => $item->getSku(),
                        "ProductCode"               => $item->getProductId(),
                        "IsActive"                  => "false",
                        "ExternalId"                => $item->getProductId(),
                        "Metal_Color__c"            => $metal,
                        "Family"                    => ($item->getProductType()=="configurable")?"simple":$item->getProductType(),
                        "Name"                      => $item->getName()
                    );
                }
            }
        }
    }
    
    $csv1->saveData($filepath1,$row1);
    
    $row = array($header);
    foreach ($productArr as $prodId){
        try{
            //$_product = Mage::getModel("catalog/product")->load($_product->getId());
            //prepare .csv row data using array
            $_product = Mage::getModel("catalog/product")->load($prodId);
            $salesforceId = $_product->getSalesforceProductId();
            if($salesforceId){
                continue;
            } 
            
            $material = $_product->getMaterial();
            if($material){
                $tMaterial = array();
                foreach (explode(",", $material) as $mat){
                    $tMaterial[] = $materialArr[$mat];
                }
                $material = implode(",", $tMaterial);
            }
            
            $row[] = array(
                "ProductCode"               => $_product->getId(),
                "IsActive"                  => ($_product->getStatus())?"true":"false",
                "Diamond_Color__c"          => "",
                "DisplayUrl"                => $_product->getUrlKey(),
                "ExternalId"                => $_product->getId(),
                "Gemstone__c"               => $gemstoneArr[$_product->getGemstone()],
                "Jewelry_Care__c"           => $_product->getJewelryCare(),
                "Metal_Color__c"            => $metalColorArr[$_product->getMetal()],
                "Description"               => $_product->getDescription(),
                "Family"                    => $_product->getTypeId(),
                "Name"                      => $_product->getName(),
                "StockKeepingUnit"          => $_product->getSku(),
                "Return_Policy__c"          => $_product->getReturnPolicy(),
                "Tax_Class_Id__c"           => $_product->getTaxClassId(),
                "Vendor_Item_No__c"         => $_product->getVendorItemNo(),
                "Location__c"               => $attrSets[$_product->getAttributeSetId()],
                "Amount__c"                 => $amountArr[$_product->getAmount()],
                "FR_SIZE__c"                => $frSizeArr[$_product->getFrSize()],
                "SIDE_EAR__c"               => $sideEarArr[$_product->getSideEar()],
                "DIRECTION__c"              => $directionArr[$_product->getDirection()],
                "NECK_LENGT__c"             => $neckLengthArr[$_product->getNeckLengt()],
                "NOSE_BEND__c"              => $noseBendArr[$_product->getNoseBend()],
                "C_LENGTH__c"               => $cLengthArr[$_product->getCLength()],
                "SIZE__c"                   => $sizeArr[$_product->getSize()],
                "GAUGE__c"                  => $gaugeArr[$_product->getGauge()],
                "POST_OPTIO__c"             => $postOptionArr[$_product->getPostOptio()],
                "RISE__c"                   => $riseArr[$_product->getRise()],
                "S_Length__c"               => $sLengthArr[$_product->getSLength()],
                "PLACEMENT__c"              => $placementArr[$_product->getPlacement()],
                "Material__c"               => $material
            );
            //add row data into .csv file
           // $io->streamWriteCsv($row);
           //  $row = null;
           $_product = null;
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
