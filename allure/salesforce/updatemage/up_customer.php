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

$update_cust_log = "update_salesforce_cust_in_magento.log";

$folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "magento" . DS . "customer";

$filepath       = $folderPath . DS . $fName;
$io             = new Varien_Io_File();
$io->streamOpen($filepath, 'r');

$salesforceIdIdx    = 0;
$customerIdIdx      = 1;

$salesforceDataArr = array();
$helper = Mage::helper("allure_salesforce/salesforceClient");
$salesforce_customer_field = $helper::S_CUSTOMERID;

$io->streamReadCsv();
while($csvData = $io->streamReadCsv()){
    try{
        $customerId     = trim($csvData[$customerIdIdx]);
        $salesforceId   = trim($csvData[$salesforceIdIdx]);
        if($customerId){
            $customer = Mage::getModel("customer/customer")->load($customerId);
            if(!$customer->getId()){
                continue;
            }
            $customer->setData($salesforce_customer_field, $salesforceId);
            $customer->getResource()->saveAttribute($customer, $salesforce_customer_field);
            Mage::log("customer_id:".$customerId." salesforce_id:".$salesforceId." updated." ,Zend_Log::DEBUG,$update_cust_log,true);
        }
    }catch (Exception $e){
        Mage::log("customer_id:".$customerId." exception:".$e->getMessage(),Zend_Log::DEBUG,$update_cust_log,true);
    }
}

Mage::log("Finish...",Zend_Log::DEBUG,$update_cust_log,true);
die("Finish...");

