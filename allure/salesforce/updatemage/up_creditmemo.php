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

$update_creditmemo_log = "update_creditmemo_salesforce_to_magento.log";

$folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "magento" . DS . "creditmemo";

$filepath       = $folderPath . DS . $fName;
$io             = new Varien_Io_File();
$io->streamOpen($filepath, 'r');

$salesforceIdIdx                = 0;
$creditmemoIncrementIdIdx       = 1;

$coreResource = Mage::getSingleton('core/resource');
$write = $coreResource->getConnection('core_write');
while($csvData = $io->streamReadCsv()){
    try{
        $incrementId        = trim($csvData[$creditmemoIncrementIdIdx]);
        $salesforceId       = trim($csvData[$salesforceIdIdx]);
        if($incrementId){
            $ids = Mage::getModel('sales/order_creditmemo')->getCollection()
            ->addAttributeToFilter('increment_id', $incrementId)
            ->getAllIds();
            
            if (!empty($ids)) {
                reset($ids);
                $creditmemo = Mage::getModel('sales/order_creditmemo')->load(current($ids));
                if(!$creditmemo->getId()){
                    continue;
                }
                $sql_order = "UPDATE sales_flat_creditmemo SET salesforce_creditmemo_id='".$salesforceId."' WHERE entity_id ='".$creditmemo->getId()."'";
                $write->query($sql_order);
                Mage::log("creditmemo_id:".$incrementId." salesforce_id:".$salesforceId." updated.",Zend_Log::DEBUG,$update_creditmemo_log,true);
            }
        }
    }catch (Exception $e){
        Mage::log("creditmemo_id:".$incrementId." exception:".$e->getMessage(),Zend_Log::DEBUG,$update_creditmemo_log,true); 
    }
}
Mage::log("Finish...",Zend_Log::DEBUG,$update_creditmemo_log,true);
die("Finish...");

