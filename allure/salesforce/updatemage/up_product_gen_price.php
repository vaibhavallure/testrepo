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

$folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "magento" . DS . "product_price_gen";

$filepath       = $folderPath . DS . $fName;
$io             = new Varien_Io_File();
$io->streamOpen($filepath, 'r');

$salesforceIdIdx  = 0;
$product2IdIdx    = 1;

$salesforceDataArr = array();
$io->streamReadCsv();
while($csvData = $io->streamReadCsv()){
    try{
        $product2Id      = trim($csvData[$product2IdIdx]);
        $salesforceId    = trim($csvData[$salesforceIdIdx]);
        if($product2Id){
            $collection = Mage::getModel('catalog/product')->getCollection()
               ->addAttributeToFilter( array(
                   array('attribute'=> 'salesforce_product_id','eq' => $product2Id)));
            
            
            $product = $collection->getFirstItem();
            if($product->getId()){
                Mage::getResourceSingleton('catalog/product_action')
                ->updateAttributes(array($product->getId()),array('salesforce_standard_pricebk' => $salesforceId),1);
                Mage::log("product_id:".$product->getId()." salesforce_price_id:".$salesforceId." updated.",Zend_Log::DEBUG,$update_general_price_log,true);
            }else{
                Mage::log("product_id:".$product->getId()." salesforce_id:".$salesforceId." not updated.",Zend_Log::DEBUG,$update_general_price_log,true);
            } 
            $product = null;
            $collection = null;
       }
    }catch (Exception $e){
        Mage::log("exception:".$e->getMessage(),Zend_Log::DEBUG,$update_general_price_log,true);
    }
}
Mage::log("Finish...",Zend_Log::DEBUG,$update_general_price_log,true);
die("Finish...");

