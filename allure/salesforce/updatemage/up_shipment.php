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

$update_shipment_log = "update_shipment_salesforce_to_magento.log";

$folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "magento" . DS . "shipment";

$filepath       = $folderPath . DS . $fName;
$io             = new Varien_Io_File();
$io->streamOpen($filepath, 'r');

$salesforceIdIdx             = 0;
$shipmentIncrementIdIdx      = 1;

$coreResource = Mage::getSingleton('core/resource');
$write = $coreResource->getConnection('core_write');
$io->streamReadCsv();
while($csvData = $io->streamReadCsv()){
    try{
        $incrementId        = trim($csvData[$shipmentIncrementIdIdx]);
        $salesforceId       = trim($csvData[$salesforceIdIdx]);
        if($incrementId){
            $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($incrementId);
            if(!$shipment->getId()){
                continue;
            }
            $sql_order = "UPDATE sales_flat_shipment SET salesforce_shipment_id='".$salesforceId."' WHERE entity_id ='".$shipment->getId()."'";
            $write->query($sql_order);
            Mage::log("shipment_id:".$incrementId." salesforce_id:".$salesforceId." updated.",Zend_Log::DEBUG,$update_shipment_log,true);
        }
    }catch (Exception $e){
        Mage::log("shipment_id:".$incrementId." exception:".$e->getMessage(),Zend_Log::DEBUG,$update_shipment_log,true);
    }
}

Mage::log("Finish...",Zend_Log::DEBUG,$update_shipment_log,true);
die("Finish...");


