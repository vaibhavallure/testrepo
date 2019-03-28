<?php
require_once ('../../app/Mage.php');
umask(0);
Mage::app();

Mage::app()->setCurrentStore(0);

//set default page size
$PAGE_SIZE   = 500;
//set default page number
$PAGE_NUMBER = 1;
//log file name
$orderHistory = "order_history.log";

echo "<style>
.salesforce-error{
    color: #f90d0d;
    text-align: center;
    margin-top: 10px;
}
</style>";

$pageNumber = $_GET["page"];
if(empty($pageNumber)){
    die("<p class='salesforce-error'>Please specify page number.</p>");
}

if(is_numeric($pageNumber)){
    $PAGE_NUMBER = (int) $pageNumber;
}else{
    die("<p class='salesforce-error'>Please specify page number in only number format.
        (eg: 1 or 2 or 3 etc...)</p>");
}

$size       = $_GET['size'];
if(empty(!$size)){
    $PAGE_SIZE = $size;
}

$store = $_GET['store'];

//.csv file header data
$header = array(
    "Order_Id__c"               => "Order_Id__c",
    "Increment_Id__c"           => "Increment_Id__c",
    "accountId"                 => "accountId",
    "Customer_Group__c"         => "Customer_Group__c",
    "Customer_Email__c"         => "Customer_Email__c",
    "Store__c"                  => "Store__c",
    "Old_Store__c"              => "Old_Store__c",
    "EffectiveDate"             => "EffectiveDate",
    "Status"                    => "Status",
    "Quantity__c"               => "Quantity__c",
    "Item_s_count__c"           => "Item_s_count__c",
    "Shipping_Method__c"        => "Shipping_Method__c",
    "Shipping_Amount__c"        => "Shipping_Amount__c",
    "Sub_Total__c"              => "Sub_Total__c",
    "Discount__c"               => "Discount__c",
    "Discount_Base__c"          => "Discount_Base__c",
    "Grant_Total__c"            => "Grant_Total__c",
    "Grand_Total_Base__c"       => "Grand_Total_Base__c",
    "Tax_Amount__c"             => "Tax_Amount__c",
    "Total_Paid__c"             => "Total_Paid__c",
    "Total_Due__c"              => "Total_Due__c",
    "Payment_Method__c"         => "Payment_Method__c",
    "Total_Refunded_Amount__c"  => "Total_Refunded_Amount__c",
    "BillingCity"               => "BillingCity",
    "BillingCountry"            => "BillingCountry",
    "BillingPostalCode"         => "BillingPostalCode",
    "BillingState"              => "BillingState",
    "BillingStreet"             => "BillingStreet",
    "ShippingCity"              => "ShippingCity",
    "ShippingCountry"           => "ShippingCountry",
    "ShippingPostalCode"        => "ShippingPostalCode",
    "ShippingState"             => "ShippingState",
    "ShippingStreet"            => "ShippingStreet",
    "Counterpoint_Order_ID__c"  => "Counterpoint_Order_ID__c",
    "Customer_Note__c"          => "Customer_Note__c",
    "Signature__c"              => "Signature__c",
    "Pricebook2Id"              => "Pricebook2Id"
);

try{
    
    $ostores = Mage::helper("allure_virtualstore")->getVirtualStores();
    $oldStoreArr = array();
    foreach ($ostores as $storeO){
        $oldStoreArr[$storeO->getId()] = $storeO->getName();
    }
    $oldStoreArr[0] = "Admin";
    
    //get collection of order according to page number, page size & asending order
    
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
    
    /* echo "<pre>";
    print_r($customerArr);
    die; */
    
    
    
    $custIds = implode(",", $customerArr);
    
    $collection = Mage::getResourceModel("sales/order_collection")
    ->addAttributeToSelect("*")
    //->setPageSize($PAGE_SIZE)
    //->setCurPage($PAGE_NUMBER)
    ->setOrder('entity_id', 'asc');
    
    $collection->getSelect()->where("customer_id in(".$custIds.")");
    
   // echo $collection->getSelect()->__toString();die;
    /* if($store){
        $collection->addFieldToFilter("old_store_id",$store);
    }
    
    Mage::log("collection size = ".$collection->getSize(),Zend_Log::DEBUG,$orderHistory,true);
     */
    
    
    //open or create .csv file
    $io           = new Varien_Io_File();
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "order";
    $filename     = "ORDER_".$store."_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    $io->streamOpen($filepath , "w+");
    $io->streamLock(true);
    
    //add header data into .csv file
    $io->streamWriteCsv($header);
    
    foreach ($collection as $order){
        try{
            
            $orderId = $order->getId();
            $status = $order->getStatus();
            $customerId = $order->getCustomerId();
            
            $saleforceCustomerId = Mage::helper('allure_salesforce')->getGuestAccount();
            if($customerId){
                $customer = Mage::getModel("customer/customer")->load($customerId);
                if($customer->getId())
                    $saleforceCustomerId = $customer->getSalesforceCustomerId();
            }

            $customerEmail = $order->getCustomerEmail();
            $customerGroup = $order->getCustomerGroupId();
            
            $totalQty = $order->getTotalQtyOrdered();
            
            $totalItemCount = $order->getTotalItemCount();
            
            $incrementId = $order->getIncrementId();
            $shipingMethod = $order->getShippingMethod();
            $createdAt = $order->getCreatedAt();
            $counterpointOrderId = $order->getCounterpointOrderId();
            $shippingDescription = $order->getShippingDescription();
            
            $subtotal = $order->getSubtotal();
            $baseSubtotal = $order->getBaseSubtotal();
            $grandTotal = $order->getGrandTotal();
            $baseGrandTotal = $order->getBaseGrandTotal();
            $discountAmount = $order->getDiscountAmount();
            $baseDiscountAmount = $order->getBaseDiscountAmount();
            $shippingAmount = $order->getShippingAmount();
            $baseShippingAmount = $order->getBaseShippingAmount();
            
            $taxAmount = $order->getTaxAmount();
            $baseTaxAmount = $order->getBaseTaxAmount();
            
            $totalPaid = $order->getTotalPaid();
            $baseTotalPaid = $order->getBaseTotalPaid();
            $totalRefunded = $order->getTotalRefunded();
            $baseTotalRefunded = $order->getBaseTotalRefunded();
            $totalInvoiced = $order->getTotalInvoiced();
            $baseTotalInvoiced = $order->getBaseTotalInvoiced();
            
            $baseTotalDue = $order->getBaseTotalDue();
            
            $billingAddr = $order->getBillingAddress();
            $shippingAddr = $order->getShippingAddress();
            
            $customerNote = Mage::helper('giftmessage/message')->getEscapedGiftMessage($order);
            
            $paymentMethod  = $order->getPayment()->getMethodInstance()->getTitle();
            
            $state       = "";
            $countryName = "";
            if($billingAddr){
                if($billingAddr['region_id']){
                    $region = Mage::getModel('directory/region')
                    ->load($billingAddr['region_id']);
                    $state = $region->getName();
                }else{
                    $state = $billingAddr['region'];
                }
                
                if($billingAddr['country_id']){
                    $country = Mage::getModel('directory/country')
                    ->loadByCode($billingAddr['country_id']);
                    $countryName = $country->getName();
                }
            }
            
            $stateShip       = "";
            $countryNameShip = "";
            if($shippingAddr){
                if($shippingAddr['region_id']){
                    $region = Mage::getModel('directory/region')
                    ->load($shippingAddr['region_id']);
                    $stateShip = $region->getName();
                }else{
                    $stateShip = $shippingAddr['region'];
                }
                
                if($shippingAddr['country_id']){
                    $country = Mage::getModel('directory/country')
                    ->loadByCode($shippingAddr['country_id']);
                    $countryNameShip = $country->getName();
                }
            } 
            
            $pricebookId = Mage::helper('allure_salesforce')->getGeneralPricebook(); //$helper::RETAILER_PRICEBOOK_ID;
            if($customerGroup == 2){
                $pricebookId = Mage::helper('allure_salesforce')->getWholesalePricebook(); //$helper::WHOLESELLER_PRICEBOOK_ID;
            }
            
            $row = array(
                "Order_Id__c"               => $order->getId(),
                "Increment_Id__c"           => $incrementId,
                "accountId"                 => $saleforceCustomerId,
                "Customer_Group__c"         => $customerGroup,
                "Customer_Email__c"         => $customerEmail,
                "Store__c"                  => $oldStoreArr[$order->getStoreId()],
                "Old_Store__c"              => $oldStoreArr[$order->getOldStoreId()],
                "EffectiveDate"             => date("Y-m-d",strtotime($createdAt)),
                "Status"                    => $status,
                "Quantity__c"               => $totalQty,
                "Item_s_count__c"           => $totalItemCount,
                "Shipping_Method__c"        => $shippingDescription,
                "Shipping_Amount__c"        => $baseShippingAmount,
                "Sub_Total__c"              => $baseSubtotal,
                "Discount__c"               => $discountAmount,
                "Discount_Base__c"          => $baseDiscountAmount,
                "Grant_Total__c"            => $grandTotal,
                "Grand_Total_Base__c"       => $baseGrandTotal,
                "Tax_Amount__c"             => $baseTaxAmount,
                "Total_Paid__c"             => $baseTotalPaid,
                "Total_Due__c"              => $baseTotalDue,
                "Payment_Method__c"         => $paymentMethod,
                "Total_Refunded_Amount__c"  => $baseTotalRefunded,
                "BillingCity"               => ($billingAddr) ? encodeValue($billingAddr["city"]) : "",
                "BillingCountry"            => encodeValue($countryName),
                "BillingPostalCode"         => ($billingAddr) ? encodeValue($billingAddr["postcode"]) : "",
                "BillingState"              => encodeValue($state),
                "BillingStreet"             => ($billingAddr) ? encodeValue($billingAddr["street"]) : "",
                "ShippingCity"              => ($shippingAddr) ? encodeValue($shippingAddr["city"]) : "",
                "ShippingCountry"           => encodeValue($countryNameShip),
                "ShippingPostalCode"        => ($shippingAddr) ? encodeValue($shippingAddr["postcode"]) : "",
                "ShippingState"             => encodeValue($stateShip),
                "ShippingStreet"            => ($shippingAddr) ? encodeValue($shippingAddr["street"]) : "",
                "Counterpoint_Order_ID__c"  => $counterpointOrderId,
                "Customer_Note__c"          => ($customerNote) ? $customerNote : "",
                "Signature__c"              => ($order->getNoSignatureDelivery()) ? "Yes" : "No",
                "Pricebook2Id"              => $pricebookId
            );
            
            //add row data into .csv file
            $io->streamWriteCsv($row);
            $row = null;
        }catch (Exception $ee){
            Mage::log("Sub Exception:".$ee->getMessage(),Zend_Log::DEBUG,$orderHistory,true);
            Mage::log("Occured for Order Id:".$order->getId(),Zend_Log::DEBUG,$orderHistory,true);
        }
    }
    $io->close();
}catch (Exception $e){
    Mage::log("Main Exception:".$e->getMessage(),Zend_Log::DEBUG,$orderHistory,true);
}


function encodeValue($str){
    //iconv('UTF-8', 'ISO-8859-1//TRSANSLIT', $str);
    return @iconv('UTF-8', 'ISO-8859-1//TRSANSLIT', $str);
}

Mage::log("Finish...",Zend_Log::DEBUG,$orderHistory,true);
die("Finish...");

