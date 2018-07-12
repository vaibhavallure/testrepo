<?php
require_once ('../../../app/Mage.php');
umask(0);
Mage::app();

echo "<style>
.salesforce-error{
    color: #f90d0d;
    text-align: center;
    margin-top: 10px;
}
</style>";

$fName = $_GET["file"];

if(empty($fName)){
    die("<p class='salesforce-error'>Please specify file Name.</p>");
}

$update_product_log = "update_product_salesforce_to_magento.log";

$folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "magento" . DS . "product";

$filepath       = $folderPath . DS . $fName;
$io             = new Varien_Io_File();
$io->streamOpen($filepath, 'r');

$salesforceIdIdx = 0;
$productIdIdx    = 1;

$salesforceDataArr = array();
echo "<pre>";

$cnt = 0;
$io->streamReadCsv();
while($csvData = $io->streamReadCsv()){
    try{
        
        $productId      = trim($csvData[$productIdIdx]);
        $salesforceId   = trim($csvData[$salesforceIdIdx]);
        if($productId){
            Mage::getResourceSingleton('catalog/product_action')
            ->updateAttributes(array($productId),array('salesforce_product_id' => $salesforceId),1);
            Mage::log("product_id:".$productId." salesforce_id:".$salesforceId." updated.",Zend_Log::DEBUG,$update_product_log,true);
        }
    }catch (Exception $e){
        Mage::log("product_id:".$productId." exception:".$e->getMessage(),Zend_Log::DEBUG,$update_product_log,true);
    }
}
Mage::log("Finish...",Zend_Log::DEBUG,$update_product_log,true);
die("Finish...");

