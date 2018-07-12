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

$update_general_price_log = "update_gen_price_salesforce_to_magento.log"; 

$folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "magento" . DS . "old_product_price_gen";

$filepath       = $folderPath . DS . $fName;
$io             = new Varien_Io_File();
$io->streamOpen($filepath, 'r');

$salesforceIdIdx  = 0;
$productSalesforceIdx       = 1;

$salesforceDataArr = array();

$io->streamReadCsv();
while($csvData = $io->streamReadCsv()){
    try{
        $productSalesforceId      = trim($csvData[$productSalesforceIdx]);
        $salesforceId    = trim($csvData[$salesforceIdIdx]);
        if($productSalesforceId){
            $product = Mage::getModel('allure_salesforce/deletedproduct')
            ->load($productSalesforceId,"salesforce_product_id");
            if(!$product->getId()){
                continue;
            }
            $product->setSalesforceStandardPricebk($salesforceId)->save();
            Mage::log("product sku:".$sku." salesforce_price_id:".$salesforceId." updated.",Zend_Log::DEBUG,$update_general_price_log,true);
        }
    }catch (Exception $e){
        Mage::log("exception:".$e->getMessage(),Zend_Log::DEBUG,$update_general_price_log,true);
    }
}
Mage::log("Finish...",Zend_Log::DEBUG,$update_general_price_log,true);
die("Finish...");

