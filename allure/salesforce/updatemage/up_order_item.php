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

$update_order_item_log = "update_order_item_salesforce_to_magento.log";

$folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "magento" . DS . "order_item";

$filepath       = $folderPath . DS . $fName;
$io             = new Varien_Io_File();
$io->streamOpen($filepath, 'r');

$salesforceIdIdx        = 0;
$salesforceOrderIdIdx   = 1;
$pricebookEntryIdIdx    = 2;
$itemIdIdx = 3;
$skuIdx = 4;

$coreResource = Mage::getSingleton('core/resource');
$write = $coreResource->getConnection('core_write');
$io->streamReadCsv();
while($csvData = $io->streamReadCsv()){
    try{
        $salesforceOrderId        = trim($csvData[$salesforceOrderIdIdx]);
        $salesforceItemId         = trim($csvData[$salesforceIdIdx]);
        $pricebookEntryId         = trim($csvData[$pricebookEntryIdIdx]);
        $itemId = trim($csvData[$itemIdIdx]);
        $sku = trim($csvData[$skuIdx]);
        
        if($salesforceOrderId){
            /* $product = Mage::getModel('catalog/product')
            ->loadByAttribute("salesforce_product_id",$pricebookEntryId); */
            /* $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter( array(
                array('attribute'=> 'salesforce_product_id','eq' => $pricebookEntryId)));
             */
            
            /* $product = $collection->getFirstItem();
            if(!$product->getId()){
                continue;
            } */
            //$sku = $product->getSku();
            $orderIds = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('salesforce_order_id', $salesforceOrderId)
            ->getAllIds();
            $orderId = current($orderIds);
            
            if ($orderId) {
                $sql_order = "UPDATE sales_flat_order_item SET salesforce_item_id='".$salesforceItemId.
                "' WHERE order_id ='".$orderId."' AND sku ='".$sku. "'";
                $write->query($sql_order);
                Mage::log("salesforce_order_item:".$salesforceItemId,Zend_Log::DEBUG,$update_order_item_log,true);
            }else{
                Mage::log("salesforce_order_item:".$salesforceItemId." not updated.",Zend_Log::DEBUG,$update_order_item_log,true);
            }
        }
    }catch (Exception $e){
        Mage::log("exception".$e->getMessage(),Zend_Log::DEBUG,$update_order_item_log,true);
    }
}
Mage::log("Finish...",Zend_Log::DEBUG,$update_order_item_log,true);
die("Finish...");

