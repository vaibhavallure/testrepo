<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
$products = array() ;
$lower = $_GET['lower'];
$upper= $_GET['upper'];

$orderIds = array(454175, 454176);
$orderObjArray = array();
foreach ($orderIds as $orderId){
    $orderObjArray[$orderId] = Mage::getModel("sales/order")->load($orderId);
}

try{
    $order = $orderObjArray[$orderId];
    $order->queueMultiAddressNewOrderEmail($orderObjArray);
}catch(Exception $e){
    var_dump($e->getMessage());
}
die("Finish...");



die;
$TOKEN = "OUtNUUhIV1V2UjgxR0RwejV0Tmk0VllneEljNTRZWHdLNHkwTERwZXlsaz0=";
$TM_URL = "/services/orders";
$helper = Mage::helper("allure_teamwork");
$urlPath ="http://35.237.115.49:9000";// $helper->getTeamworkSyncDataUrl();
$requestURL = $urlPath . $TM_URL;//."?start=".$start."&end=".$end;
var_dump($requestURL);
$token = $TOKEN; //trim($helper->getTeamworkSyncDataToken());
$sendRequest = curl_init($requestURL);
curl_setopt($sendRequest, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
curl_setopt($sendRequest, CURLOPT_HEADER, false);
curl_setopt($sendRequest, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($sendRequest, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($sendRequest, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($sendRequest, CURLOPT_FOLLOWLOCATION, 0);
curl_setopt($sendRequest, CURLOPT_HTTPHEADER, array(
    "Content-Type: application/json",
    "Authorization: Bearer ".$token
));

$requestArgs = array(
    "start_time" => $start,
    "end_time"   => $end
);
// convert requestArgs to json
if ($requestArgs != null) {
    $json_arguments = json_encode($requestArgs);
    curl_setopt($sendRequest, CURLOPT_POSTFIELDS, $json_arguments);
}
$response  = curl_exec($sendRequest);
//$response1 = json_decode($response,true);
echo "<pre>";
$response1 = unserialize($response);
var_dump(count($response1));

print_r($response1);

Mage::getModel("allure_teamwork/tmobserver")->addDataIntoSystem($response);

die;


//echo round(16.6657,2);

/* $customer  = Mage::getModel("customer/customer")->load(3288);

$cDate = "2018-06-09 23:01:19";
$customer->setCreatedAt($cDate)->save();

echo "<pre>";
print_r($customer->getData()); */
//Mage::getModel("allure_teamwork/tmobserver")->synkTeamwokLiveOrders();

die;

$conn = odbc_connect('TEAMWORKS', 'MariaTasReportingUser','{1EE26209-DB51-4905-AE02-2395D119F500}');
if($conn){
    $sql = "SELECT TABLE_NAME FROM CloudHQ.INFORMATION_SCHEMA.TABLES;";
    $result = odbc_exec($conn,$sql);
    $cnt = 0;
    while(odbc_fetch_row($result)){
        for($i=1;$i<=odbc_num_fields($result);$i++){
            
            $tableName = odbc_result($result,$i);
            $query = "SELECT TOP 100 * FROM $tableName;";
            $result1 = odbc_exec($conn,$query);
            $file = fopen('data/'.$tableName.'.csv', 'w');
            
            $header = array();
            
            $query2 = "SELECT * FROM CloudHQ.INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = $tableName";
            $result2 = odbc_exec($conn,$query2);
            while(odbc_fetch_row($result2)){
                for($k=1;$k <= odbc_num_fields($result2);$k++){
                    $value2 = odbc_result($result2,$k);
                    $row[$k] = $value2;
                    $header[$k] = $value2;
                }
            }
            
            while(odbc_fetch_row($result1)){
                $row = array();
                for($j=1;$j <= odbc_num_fields($result1);$j++){
                    $value = odbc_result($result1,$j);
                    $row[$j] = $value;
                }
                
                if(count($header) == count($row)){
                    $entry = array_combine($header, $row);
                    fputcsv($file, $entry);
                    $row = null;
                }
                
                
            }
            fclose($file);
        }
        
        if($cnt==10){
            break;
        }
        $cnt++;
    }
} else {
    die('Failure');
}

                    




die;
$order = Mage::getModel("sales/order")->load(297324);
$payment = $order->getPayment();
$code = $payment->getData('cc_type');
$aType = Mage::getSingleton('payment/config')->getCcTypes();
if (isset($aType[$code])) {
    $sName = $aType[$code];
}
else {
    $sName = Mage::helper('payment')->__('N/A');
}

$DeliveryMethod = trim($order->getData("order_type"));
if($DeliveryMethod == "Multiple - Main"){
    $DeliveryType = "Main";
}else if ($DeliveryMethod == "Multiple - Backorder"){
    $DeliveryType = "Backorder";
}else{
    $DeliveryType = "Single";
}

var_dump($sName);
echo "<pre>";
print_r($DeliveryType);
$last4Digits  = "XXXX-".$order->getPayment()->getCcLast4();
var_dump($order->getPayment()->getData("last_trans_id"));

die;

$serverName = "10.154.0.8"; //serverName\instanceName

// Since UID and PWD are not specified in the $connectionInfo array,
// The connection will be attempted using Windows Authentication.
$connectionInfo = array( "Database"=>"CloudHQ",
    "UID"=> "MariaTasReportingUser", 
    "PWD" => '{1EE26209-DB51-4905-AE02-2395D119F500}');
$conn = sqlsrv_connect( $serverName, $connectionInfo);

if( $conn ) {
    echo "Connection established.<br />";
}else{
    echo "Connection could not be established.<br />";
    die( print_r( sqlsrv_errors(), true));
}


die;

$product = Mage::getModel('catalog/product')
->loadByAttribute("salesforce_product_id","01t29000001eGADAA2");

print_r($product->getData());
die;

$sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
->load()
->toOptionHash();

echo "<pre>";
print_r($sets);

die;

$optionArray = array();
$attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',"s_length");
$options = $attribute->getSource()->getAllOptions();
foreach ($options as $option){
    $optionArray[$option["value"]] = $option["label"];
}
echo "<pre>";
print_r($optionArray);
die;


$product = Mage::getModel("catalog/product")->load(34698);
//secho $product->getAttributeSetId();

$attributeSetModel = Mage::getModel("eav/entity_attribute_set");
$attributeSetModel->load($product->getAttributeSetId());
$attributeSetName = $attributeSetModel->getAttributeSetName();
echo $attributeSetName;

die;

echo Mage::helper("core")->removeAccents("Gūlsen");//"Gūlsen"."<br/>";
echo iconv('UTF-8', 'ISO-8859-1//TRSANSLIT', "Gūlsen");
die;

$orderItem = Mage::getModel("sales/order_item")->load(342189);
echo "<pre>";
print_r($orderItem->getProductOptions()["options"]);

die;

$productID  = Mage::getModel("catalog/product")->getIdBySku("CFL55PKD_T|ROSE GOLD|6.5MM");
var_dump($productID);
die;
$product    = Mage::getModel("catalog/product")->load($productID);
echo "<pre>";
foreach ($product->getOptions() as $options){
    foreach ($options->getValues() as $option){
        print_r($option->getData());
    }
}

die;


$customer = Mage::getModel("customer/customer")->load(3358);
$address = $customer->getDefaultBillingAddress();

$text = htmlspecialchars($address->getData('region'), ENT_NOQUOTES, "UTF-8");// utf8_encode($address->getData('region'));
var_dump($text);

die;

$ids = Mage::getModel('sales/order')->getCollection()
->addAttributeToFilter('salesforce_order_id', "80129000000H7VUAA0")
->getAllIds();

var_dump(current($ids));

die;

/* $customer = Mage::getModel("customer/customer")->load(121822);
$customer->setData('firstname', "AB");
$customer->setData('lastname', "TEST");
$customer->getResource()->saveAttribute($customer, 'firstname');
$customer->getResource()->saveAttribute($customer, 'lastname'); */

die;

$helper = Mage::helper("allure_salesforce/salesforceClient");
$creditMemo = Mage::getModel("sales/order_creditmemo")->load(17857);
$items = $creditMemo->getAllItems();

$order = $creditMemo->getOrder();
$salesforceOrderId = $order->getSalesforceOrderId();
if($salesforceOrderId){
    //return ;
}
$salesforceCreditmemoId = $creditMemo->getSalesforceCreditmemoId();

$incrementId = $creditMemo->getIncrementId();
$orderIncrementId = $order->getIncrementId();
$baseAdjustment = $creditMemo->getBaseAdjustment();
$createdAt = $creditMemo->getCreatedAt();
$status = $creditMemo->getState();
$discountAmount = $creditMemo->getBaseDiscountAmount();
$grandTotal = $creditMemo->getBaseGrandTotal();
$orderDate = $order->getCreatedAt();
$shippingAmount = $creditMemo->getBaseShippingAmount();
$storeId = $creditMemo->getStoreId();
$subtotal = $creditMemo->getBaseSubtotal();
$taxAmount = $creditMemo->getBaseTaxAmount();


$requestMethod = "GET";
$urlPath = $helper::CREDIT_MEMO_URL;
if(!$salesforceCreditmemoId){
    $requestMethod = "PATCH";
    $urlPath .= "/" .$salesforceCreditmemoId;
}else{
    $requestMethod = "POST";
}

$request = array(
    "Adjustment__c" => $baseAdjustment,
    "Created_At__c" => date("Y-m-d",strtotime($createdAt)),
    "Credit_Memo_Id__c" => $incrementId,
    "Stauts__c" => $status,
    "Discount_Amount__c" => $discountAmount,
    "Grand_Total__c" => $grandTotal,
    "Order_Date__c" => date("Y-m-d",strtotime($orderDate)),
    "Order_Id__c" => $orderIncrementId,
    "Shipping_Amount__c" => $shippingAmount,
    "Store__c" => $storeId,
    "Subtotal__c" => $subtotal,
    "Tax_Amount__c" => $taxAmount
);

$response = $helper->sendRequest($urlPath,$requestMethod,$request);
$responseArr = json_decode($response,true);
if($responseArr["success"]){
    $salesforceId = $responseArr["id"];
    $helper->salesforceLog("Salesforce Id :".$salesforceId);
    $coreResource = Mage::getSingleton('core/resource');
    $write = $coreResource->getConnection('core_write');
    $sql_order = "UPDATE sales_flat_creditmemo SET salesforce_creditmemo_id='".$salesforceId."' WHERE entity_id ='".$creditMemo->getId()."'";
    $write->query($sql_order);
    $helper->salesforceLog("Salesforce Id Added.");
    
    $cRequest = array("allOrNone"=>false);
    $cRequest["records"] = array();
    $requestMethod = "PATCH";
    $urlPath = $helper::UPDATE_COMPOSITE_OBJECT_URL;
    foreach ($items as $item){
        $orderItemId = $item->getOrderItemId();
        $orderItem = Mage::getModel("sales/order_item")->load($orderItemId);
        /* if(!$orderItem){
            continue;
        }
        $salesforceItemId = $orderItem->getSalesforceItemId();
        if(!$salesforceItemId){
            continue;
        } */
        $tempArr = array(
            "attributes" => array("type" => "OrderItem"),
            "id" => "80229000000zm78AAA",
            "Credit_Memo__c" => $salesforceId
        );
        array_push($cRequest["records"],$tempArr);
    }
    
    $response = $helper->sendRequest($urlPath,$requestMethod,$cRequest);
    $helper->salesforceLog("Salesforce creditmemo updated.");
}

die;



$shipment = Mage::getModel("sales/order_shipment")->load(28);

$salesforceShipmentId = $shipment->getSalesforceShipmentId();

$order = $shipment->getOrder();

$salesforceOrderId = $order->getSalesforceOrderId();
$customerId = $shipment->getCustomerId();
$incrementId = $shipment->getIncrementId();
$orderIncrementId = $order->getIncrementId();

$totalQty = $shipment->getTotalQty();
$shippingLabel = $shipment->getShippingLabel();

$weight = $order->getWeight();

$tracksNumCollection = $shipment->getAllTracks();
$trackNumberArr = array();
$titlesArr = array();
foreach ($trackNumberArr as $track){
    $trackNumberArr[] = $track->getData("track_number");
    $titlesArr[] = $track->getData("title");
}
$carrierTitles = implode(",", $titlesArr);
$trackNums = implode(",", $trackNumberArr);

/* if(!$salesforceOrderId){
    return;
} */


$requestMethod = "GET";
$urlPath = $helper::SHIPMENT_URL;
if($salesforceShipmentId){
    $requestMethod = "PATCH";
    $urlPath .= "/" .$salesforceShipmentId;
}else{
    $requestMethod = "POST";
}

$request = array(
    "Customer_Id__c" => $customerId,
    "Increment_ID__c" => $incrementId,
    "Order__c" => "80129000000H7R1AAK",
    "Order_Id__c" => $orderIncrementId,
    "Quantity__c" => $totalQty,
    "Shipping_Label__c" => "",
    "Weight__c" => $weight,
    "Carrier__c" => $carrierTitles,
    "Track_Number__c" => $trackNums
);

$response = $helper->sendRequest($helper::SHIPMENT_URL,"POST",$request);
var_dump($response);

$responseArr = json_decode($response,true);
if($responseArr["success"]){
    $salesforceId = $responseArr["id"];
    $helper->salesforceLog("Salesforce Id :".$salesforceId);
    $coreResource = Mage::getSingleton('core/resource');
    $write = $coreResource->getConnection('core_write');
    $sql_order = "UPDATE sales_flat_shipment SET salesforce_shipment_id='".$salesforceId."' WHERE entity_id ='".$shipment->getId()."'";
    $write->query($sql_order);
    $helper->salesforceLog("Salesforce Id Added.");
}

die;

$invoice = Mage::getModel("sales/order_invoice")->load(325621);
$order = $invoice->getOrder();

$salesforceOrderId = $order->getSalesforceOrderId();
if($salesforceOrderId){
    $baseGrandTotal = $invoice->getBaseGrandTotal();
    $basTaxAmount = $invoice->getBaseTaxAmount();
    $baseShippingAmount = $invoice->getBaseShippingAmount();
    $baseSubtotal = $invoice->getBaseSubtotal();
    $baseDiscountAmount = $invoice->getBaseDiscountAmount();
    $discountDescrption = $invoice->getDiscountDescription();
    $createdAt = $invoice->getCreatedAt();
    $invoiceId = $invoice->getIncrementId();
    
    $orderDate = $order->getCreatedAt();
    $orderId = $order->getIncrementId();
    
    $status = $invoice->getState();
    $storeId = $invoice->getStoreId();
    
    $totalQty = $invoice->getTotalQty();
    
    $request = array(
        "Discount_Amount__c" => $baseDiscountAmount,
        "Discount_Descrition__c" => "for advertisment",
        "Grand_Total__c" => $baseGrandTotal,
        "Invoice_Date__c" => date("Y-m-d",strtotime($createdAt)),
        "Invoice_Id__c" => $invoiceId,
        "Order_Date__c" => date("Y-m-d",strtotime($orderDate)),
        "Order_Id__c" => $orderId,
        "Shipping_Amount__c" => $baseShippingAmount,
        "Status__c" => $status,
        "Subtotal__c" => $baseSubtotal,
        "Tax_Amount__c" => $basTaxAmount,
        "Total_Quantity__c" => $totalQty,
        "Store__c" => $storeId,
        "Order__c" => "80129000000H7R1AAK"
    );
    
    $response = $helper->sendRequest($helper::INVOICE_URL,"POST",$request);
    echo "<pre>";
    //print_r(json_decode($response,true));
    
    $responseArr = json_decode($response,true);
    if($responseArr["success"]){
        $salesforceId = $responseArr["id"];
        $helper->salesforceLog("Salesforce Id :".$salesforceId);
        $coreResource = Mage::getSingleton('core/resource');
        $write = $coreResource->getConnection('core_write');
        $sql_order = "UPDATE sales_flat_invoice SET salseforce_invoice_id='".$salesforceId."' WHERE entity_id ='".$invoice->getId()."'";
        $write->query($sql_order);
        $helper->salesforceLog("Salesforce Id Added.");
    }

}
//var_dump($request);
die;


$order = Mage::getModel("sales/order")->load(297349);

$helper = Mage::helper("allure_salesforce/salesforceClient");

$orderId = $order->getId();
$status = $order->getStatus();
$customerId = $order->getCustomerId();
$customerEmail = $order->getCustomerEmail();
$customerGroup = $order->getCustomerGroupId();

$pricebookId = $helper::RETAILER_PRICEBOOK_ID;
if($customerGroup == 2){
    $pricebookId = $helper::WHOLESELLER_PRICEBOOK_ID;
}

$totalQty = $order->getTotalQtyOrdered();

$totalItemCount = $order->getTotalItemCount();

$incrementId = $order->getIncrementId();
$shipingMethod = $order->getShippingMethod();
$createdAt = $order->getCreatedAt();
$counterpointOrderId = $order->getCounterpointOrderId();
$shippingDescription = $order->getShippingdescription();

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
    
    $country = Mage::getModel('directory/country')
    ->loadByCode($billingAddr['country_id']);
    $countryName = $country->getName();
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
    
    $country = Mage::getModel('directory/country')
    ->loadByCode($shippingAddr['country_id']);
    $countryNameShip = $country->getName();
}

$orderItem = array();
$orderItem["records"] = array();
$items = $order->getAllVisibleItems();
foreach ($items as $item){
    $itemArray = array(
        "attributes" => array("type" => "OrderItem"),
        "PricebookEntryId"=>"01u290000037WAR",
        "quantity" => $item->getQtyOrdered(),
        "UnitPrice" => $item->getBasePrice()
    );
    array_push($orderItem["records"],$itemArray);
}

//var_dump($orderItem);die;

$request = array();
$request["order"] = array(
    array(
        "attributes" => array("type" => "order"),
        "EffectiveDate" => date("Y-m-d",strtotime($createdAt)),
        "Status" => $status,
        "accountId" => "0012900000Ls44hAAB",
        "Pricebook2Id" => "01s290000001ivyAAA",//$pricebookId,
        "BillingCity" => ($billingAddr)?$billingAddr["city"]:"",
        "BillingCountry" => $countryName,
        "BillingPostalCode" => ($billingAddr)?$billingAddr["postcode"]:"",
        "BillingState" => $state,
        "BillingStreet" => ($billingAddr)?$billingAddr["street"]:"",
        "ShippingCity" => ($shippingAddr)?$shippingAddr["city"]:"",
        "ShippingCountry" => $countryNameShip,
        "ShippingPostalCode" => ($shippingAddr)?$shippingAddr["postcode"]:"",
        "ShippingState" => $stateShip,
        "ShippingStreet" => ($shippingAddr)?$shippingAddr["street"]:"",
        
        "Shipping_Method__c" => $shippingDescription,
        "Quantity__c"   => $totalQty,
        "Item_s_count__c" => $totalItemCount,
        
        "Shipping_Amount__c" => $baseShippingAmount,
        
        "Total_Refunded_Amount__c" => $baseTotalRefunded,
        "Tax_Amount__c" => $baseTaxAmount,
        
        "Sub_Total__c" => $baseSubtotal,
        "Discount__c" => $discountAmount,
        "Discount_Base__c" => $baseDiscountAmount,
        "Grant_Total__c" => $grandTotal,
        "Grand_Total_Base__c" => $baseGrandTotal,
        
        "Total_Paid__c" => $baseTotalPaid,
        "Total_Due__c" => $baseTotalDue,
        
        "Payment_Method__c" => $paymentMethod,
        "Store__c" => $order->getStoreId(),
        "Order_Id__c" => $incrementId,
        "Customer_Group__c" => $customerGroup,
        "Customer_Email__c" => $customerEmail,
        "Counterpoint_Order_ID__c" => $counterpointOrderId,
        "Customer_Note__c" => ($customerNote)?$customerNote:"",
        
        "OrderItems" => $orderItem
    )
);

echo "<pre>";
//print_r(json_encode($request));die;

$response = $helper->sendRequest($helper::ORDER_PLACE_URL,"POST",$request);

$responseArr = json_decode($response,true);
if($responseArr["done"]){
    $recordes = $responseArr["records"][0];
    $salesforceId = $recordes["Id"];
    $helper->salesforceLog("Salesforce Id :".$salesforceId);
    $coreResource = Mage::getSingleton('core/resource');
    $write = $coreResource->getConnection('core_write');
    $sql_order = "UPDATE sales_flat_order SET salesforce_order_id='".$salesforceId."' WHERE entity_id ='".$orderId."'";
    $write->query($sql_order);
    $helper->salesforceLog("Salesforce Id Added.");
}
print_r(json_decode($response,true));

die;

$product = Mage::getModel("catalog/product")->load(34698);
$price = $product->getPrice();
$wPrice = 0;
foreach ($product->getData('group_price') as $gPrice){
    $wPrice = $gPrice["price"];
}

$sRequest = array();
$sRequest["records"] = array(
    array(
        "attributes"    => array(
            "type"          => "PricebookEntry",
            "referenceId"   => "general"
        ),
        "Pricebook2Id"  => "1000",
        "Product2Id"    =>  "12345",
        "UnitPrice"     => $price
    )
);

if($wPrice){
    $stemp = array(
        "attributes"    => array(
            "type"          => "PricebookEntry",
            "referenceId"   => "general"
        ),
        "Pricebook2Id"  => "1001",
        "Product2Id"    =>  "12345",
        "UnitPrice"     => $wPrice
    );
    
    array_push($sRequest["records"],$stemp);
}

var_dump(json_encode($sRequest));

die;



$customer = Mage::getModel("customer/customer")->load(52577); 
if($customer->getDefaultShippingAddress()){
var_dump($customer->getDefaultShippingAddress()->getData());
}

if($customer->getDefaultBillingAddress()){
    var_dump($customer->getDefaultBillingAddress()->getData());
}
die;

Mage::getModel("allure_teamwork/observer")->syncTeamworkCustomer();

die;

if(empty($lower) || empty($upper)){
    die('Please add Upper and Lower limit');
}


$app = Mage::app('default');

Mage::getSingleton('core/session', array('name' => 'adminhtml'));

Mage::app()->setCurrentStore(0);


$resource     = Mage::getSingleton('core/resource');
$writeAdapter   = $resource->getConnection('core_write');
$table        = $resource->getTableName('catalog/product_super_attribute');

$collection = Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('entity_id',
    array(
        'gteq' => $lower
    ));

$collection->addAttributeToFilter('entity_id', array(
    'lteq' => $upper
));
$collection->addAttributeToFilter('type_id', array('eq' => 'configurable'));




foreach ($collection as $prod){
    $productId  = $prod->getId();
    $_product = Mage::getModel('catalog/product')->load($productId);
    $mainSku = $_product->getSku();
    $mainName = $_product->getName();
    
    $productAttributeOptions = $_product->getTypeInstance(true)->getConfigurableAttributesAsArray($_product);
    echo "<pre>";
    //print_r($productAttributeOptions);
    
    $childProducts = Mage::getModel('catalog/product_type_configurable')
    ->getUsedProducts(null,$_product);
    
    $atributeCode = 'metal_color';
    $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$atributeCode);
    $options = $attribute->getSource()->getAllOptions();
    
    $colorArr = array();
    foreach ($options as $option){
        $val = explode(" ", $option['label']);
        $str = $option['label'];
        if(count($val)>2)
            $str = $val[0]." ".$val[count($val)-1];
            
            $colorArr[$option['value']]=$str;
    }
    
    
    $multipleOptions=array();
    foreach ($productAttributeOptions as $prodOptions){
        if($prodOptions['attribute_code']!='metal_color'){
            $optionsArray=array();
            
            //foreach ($_product->getOptions() as $option) {
            /* if ($option->getTitle() === 'Post Length'){
             echo 'Product '. $_product->getName() . ' has a Post Length option!<br>';
             }else{ */
            foreach ($prodOptions['values'] as $optionsValues){
                $value=array(
                    'title' => $optionsValues['label'],
                    'price' => 00.00,
                    'price_type' => 'fixed',
                    'sku' => '',
                    'sort_order' => 0,
                );
                array_push($optionsArray,$value);
            }
            
            $customOptions=array(
                'title' => $prodOptions['label'],
                'type' => 'drop_down',
                'is_required' => 1,
                'sort_order' => 0,
                'values' => $optionsArray
            );
            array_push($multipleOptions,$customOptions);
            $query        = "delete from {$table} WHERE product_super_attribute_id =".$prodOptions['id'];
            $writeAdapter->query($query);
            //}
            //}
        }
        
    }
    
    $product = Mage::getModel('catalog/product')->load($productId);
    $product->setProductOptions($multipleOptions);
    $product->setCanSaveCustomOptions(true);
    $product->save();
    
    echo $productId ." - Option Added successfully<br>";
    
    
    
    $websitesCollection = Mage::getModel("core/website")->getCollection()
    ->addFieldToFilter('stock_id',array('neq'=>0));
    $websiteArr = array();
    foreach ($websitesCollection as $website){
        $websiteArr[$website->getId()] = $website->getStockId();
    }
    
    $mainArr = array();
    foreach ($options as $prodOptions){
        $count = 0;
        $value = $prodOptions['value'];
        $inventory = array();
        $productId = 0;
        foreach ($childProducts as $child){
            if($value==$child->getMetalColor()){
                $count+=1;
                $websiteIds = $child->getWebsiteIds();
                foreach ($websiteIds as $websiteId){
                    $stock = Mage::getModel('cataloginventory/stock_item')
                    ->loadByProductAndStock($child,$websiteArr[$websiteId]);
                    $inventory[$websiteId] += $stock->getQty();
                }
                
                if($count==1){
                    $productId = $child->getId();
                }
            }
        }
        if($productId!=0)
            $mainArr[$productId] = $inventory;
            
    }
    
    //var_dump($mainArr);
    
    foreach ($childProducts as $child){
        $productId = $child->getId();
        $product = Mage::getModel('catalog/product')->load($productId);
        $sku = "";
        $name = "";
        if(array_key_exists($productId, $mainArr)){
            $sku = $mainSku."|".$colorArr[$child->getMetalColor()];
            $name = $mainName."-".$colorArr[$child->getMetalColor()];
            $product->setName($name);
            $product->setSku($sku)->save();
            foreach ($mainArr[$productId] as $stockId=>$qty){
                $stock = Mage::getModel('cataloginventory/stock_item')
                ->loadByProductAndStock($child,$stockId);
                $stock->setQty($qty)->save();
            }
        }else{
            $product->delete();
        }
    }
    
    echo "Operation Successfull";
}
die;


die;



Mage::getModel('appointments/cron')->autoProcess();
die;

Mage::getModel('allure_instacatalog/cron')->syncFeeds();
die;
$app = Mage::app('default');

Mage::getSingleton('core/session', array('name' => 'adminhtml'));

Mage::app()->setCurrentStore(0);

//var_dump(Mage::app()->getStore()->isAdmin());

$findify_cron = Mage::getModel('findify/cron_daily');
ini_set('memory_limit', '-1');
$findify_cron->runFeed(1);

die;

if($ch==1){
    Mage::getModel('productshare/observer')->shareAvailableProductsToStoreRun();
    
}else {
    if (isset($_GET['products']) && !empty($_GET['products']))
        $products = explode(',', $_GET['products']);
        $website = $_GET['website'];
        $storeId = $_GET['store'];
        Mage::getModel('productshare/observer')->shareAvailableProductsToStoreAdditional($products,$storeId,$website);
        
}

/* foreach ($collection as $item){
 if($item)
 print_r( $item);
 else
 echo "bye";
 } */
 die;
 function getWebsiteIds(){
     $websiteIds = array();
     foreach (Mage::app()->getWebsites() as $website) {
         $websiteIds[] = $website->getId();
     }
     return $websiteIds;
 }
 
 $collection = Mage::getModel('catalog/product')->getCollection();
 //$websiteIds = getWebsiteIds();
 $websiteId = 2;
 $storeId = 2;
 foreach($collection as $_product):
 try{
     $product = Mage::getModel('catalog/product')->load($_product->getId());
     $websiteIds = $product->getWebsiteIds();
     $storeIds = $product->getStoreIds();
     if (!in_array($websiteId, $websiteIds)) {
         array_push($websiteIds,$websiteId);
         array_unique($websiteIds);
         if(!in_array($storeId, $storeIds)){
             array_push($storeIds,$storeId);
             array_unique($storeIds);
             $product->setStoreIds($storeIds);
         }
         $product->setWebsiteIds($websiteIds)->save();
     }
 }catch(Exception $e){
     Mage::log($e->getMessage());
     Mage::log($e->getMessage(),Zend_log::DEBUG,'multistore_copy_product.log',true);
 }
 endforeach;
 