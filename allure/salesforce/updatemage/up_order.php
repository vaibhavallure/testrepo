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

$update_order_log = "update_order_salesforce_to_magento.log";

$folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "magento" . DS . "order";

$filepath       = $folderPath . DS . $fName;
$io             = new Varien_Io_File();
$io->streamOpen($filepath, 'r');

$salesforceIdIdx       = 0;
$orderIdIdx            = 1;

$coreResource = Mage::getSingleton('core/resource');
$write = $coreResource->getConnection('core_write');
while($csvData = $io->streamReadCsv()){
    try{
        $orderId        = trim($csvData[$orderIdIdx]);
        $salesforceId       = trim($csvData[$salesforceIdIdx]);
        if($orderId){
            //$order = Mage::getModel("sales/order")->load($orderId);
            if ($orderId) {
                $sql_order = "UPDATE sales_flat_order SET salesforce_order_id='".$salesforceId."' WHERE entity_id ='".$orderId."'";
                $write->query($sql_order);
                Mage::log("order_id:".$orderId." salesforce_id:".$salesforceId." updated.",Zend_Log::DEBUG,$update_order_log,true);
            }else{
                Mage::log("order_id:".$orderId." salesforce_id:".$salesforceId." not updated.",Zend_Log::DEBUG,$update_order_log,true);
            }
        }
    }catch (Exception $e){
        Mage::log("order_id:".$incrementId." exception:".$e->getMessage(),Zend_Log::DEBUG,$update_order_log,true); 
    }
}
Mage::log("Finish...",Zend_Log::DEBUG,$update_order_log,true);
die("Finish...");

