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

$updated_invoice_log = "updated_invoice_salesforce_to_magento.log";

$folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "magento" . DS . "invoice";

$filepath       = $folderPath . DS . $fName;
$io             = new Varien_Io_File();
$io->streamOpen($filepath, 'r');

$salesforceIdIdx            = 0;
$invoiceIncrementIdIdx      = 1;

$coreResource = Mage::getSingleton('core/resource');
$write = $coreResource->getConnection('core_write');
$io->streamReadCsv();
while($csvData = $io->streamReadCsv()){
    try{
        $incrementId        = trim($csvData[$invoiceIncrementIdIdx]);
        $salesforceId       = trim($csvData[$salesforceIdIdx]);
        if($incrementId){
            $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($incrementId);
            if(!$invoice->getId()){
                continue;
            }
            $sql_order = "UPDATE sales_flat_invoice SET salesforce_invoice_id='".$salesforceId."' WHERE entity_id ='".$invoice->getId()."'";
            $write->query($sql_order);
            Mage::log("invoice_id:".$incrementId." salesforce_id:".$salesforceId." updated.",Zend_Log::DEBUG,$updated_invoice_log,true);
        }
    }catch (Exception $e){
       Mage::log("invoice_id:".$incrementId." exception:".$e->getMessage(),Zend_Log::DEBUG,$updated_invoice_log,true); 
    }
}
Mage::log("Finish...",Zend_Log::DEBUG,$updated_invoice_log,true);
die("Finish...");
