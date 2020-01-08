<?php
/**
 * Created by PhpStorm.
 * User: Indrajeet
 * Date: 11/7/19
 * Time: 6:08 PM
 */
require_once('../../app/Mage.php');
umask(0);
Mage::app();
echo "<pre>";

$from = $_GET['from'];
$to = $_GET['to'];
if(empty($from) || empty($to)) {
    echo 'Enter date';
}else {
    $twClient = Mage::helper('allure_teamwork/teamworkClient');
    $twClient->syncTmOrders($from,$to);
    die;
}

die;















die;
$helper = Mage::helper("allure_salesforce/salesforceClient");
$emailFormatterHelper = Mage::helper('allure_salesforce/emailFormatter');
$customerCollection = Mage::getModel('customer/customer')->getCollection()
->addFieldToFilter('entity_id',array('in' => array(9813,12530,44458,45736,46145,54754,58225,64832,66016,66640,67538,68926,70424,74332,78241,80063,80955,81128,81410,81777,81858,81958,82105,83417,84081,86727,87031,87139,87830,88037,88079,88815,89128,89129,89130,89131,89132,89170,89190,89347,89386,89408,89430,89465,89490,89680,89697,90006,90161,90173,90468,90698,91112,91705,91738,92005,92050,92101,92177,92847,92897,92929,93008,93069,93104,93113,93122,93131,93460,93734,94002,94057,94256,94361,94399,94430,94647,94648,94819,94894,95087,95192,95239,95247,95249,95260,95580,95612,95683,95709,95869,96100,111462,127477,129689,130057,130436,130764,132200,133466,136960,139049,140028,142628,143460,143934,143944,144129,144141,144246,144282,144299,144531,144547,144574,144624,144625,144764,144805,144875,144887,144890,144906,145091)));
//->addFieldToFilter('entity_id',array('in' => array(146757)));

$correctedCountEmail = 0;
$failedCountEmail = 0;
$correctedCountName= 0;
$failedEmailArray = array();
foreach ($customerCollection as $customerOb) {
    $customer = Mage::getModel('customer/customer')->load($customerOb->getId());
    if($customer){
        $prefix = $customer->getPrefix();
        $fName = $customer->getFirstname();
        $mName = $customer->getMiddlename();
        $lName = $customer->getLastname();
        $fullName = "";

        $sql = "";
        if ($prefix) {
            $fullName .= $prefix . " ";
        }
        $fullName .= $fName . " ";
        if ($mName) {
            $fullName .= $mName;
        }

        $fullName .= $lName;
        $email = $customer->getEmail();
        $formattedEmail = $emailFormatterHelper->startMailFormating($email,$fullName,null);
        if (strlen($lName) < 1) {
            $log = "Change customer name from {$fullName}} to = ";
            $fullName = explode("@", $formattedEmail, 2)[0];
            $log = $log. $fullName;
            echo $log;
            echo "</br>";
            $customer->setData('lastname',$fullName);
            $correctedCountName++;
        }


        if ($formattedEmail != $email) {
            try{
                $customer->setData('email',$formattedEmail);
                $customer->save();
                $log = "";
                $log = "Change custome email from {$email} to {$formattedEmail}";
                echo $log;
                echo "</br>";
                $correctedCountEmail++;
            }catch (Exception $e) {
                //$helper->salesforceLog("Error for Customer - {$customer->getId()} with message {$e->getMessage()}");
                $failedCountEmail++;
                array_push($failedEmailArray,$customer->getId());
            }
        }
    }
}
//$helper->salesforceLog("Corrected email Count - {$correctedCountEmail}");
echo "Corrected email Count - {$correctedCountEmail}";echo "</br>";
//$helper->salesforceLog("Failed email Count - {$failedCountEmail}");
echo "Failed email Count - {$failedCountEmail}";echo "</br>";

//$helper->salesforceLog("Corrected name Count - {$correctedCountName}");
echo "Corrected name Count - {$correctedCountName}";echo "</br>";

//$helper->salesforceLog(print_r($failedEmailArray));
print_r($failedEmailArray);
die;
//$sql = "";
//$fullName="latthe";
//$formattedEmail = 'indrajeet@allureinc.co';
//$email = 'indrajeet1@allureinc.co';
//$id = 146757;
//$sql = $sql. "REPLACE INTO `customer_entity_varchar`(entity_type_id,attribute_id,entity_id,value) VALUES(1,7,{$id},'{$fullName}');";
//
//if($formattedEmail != $email) {
//    //$sql = $sql. "REPLACE INTO `customer_entity`(entity_id,attribute_set_id,entity_type_id,website_id,email,) VALUES(1,9,{$id},'{$formattedEmail}');";
//    $sql = $sql. "UPDATE `customer_entity` SET email = '{$formattedEmail}' WHERE entity_id = {$id};";
//}
//
//echo $sql;
$helper = Mage::helper("allure_salesforce/emailFormatter");
$customer = Mage::getModel('customer/customer')->load(146757);
if ($customer) {
    $customerId = $customer->getId();
    $customer = Mage::getModel('customer/customer')->load($customerId);
    $emailFormatterHelper = Mage::helper('allure_salesforce/emailFormatter');
    //$this->salesforceLog("Customer {$$customerId}");

    $salesforceId = $customer->getSalesforceCustomerId();
    $salesforceContactId = $customer->getSalesforceContactId();

//    if ($create && (!empty($salesforceId) && !empty($salesforceContactId))) {
//        $this->salesforceLog("Tried to create Customer and Contact - " . $customerId . ". But Customer and Contact already Present in SF -" . $salesforceId);
//        return;
//    }
//
//    if (!$create && (empty($salesforceId) || empty($salesforceContactId))) {
//        $this->salesforceLog("Tried to update Customer or Contact - " . $customerId . ". But Customer or Contact not Present in SF -" . $salesforceId);
//        return;
//    }

    $prefix = $customer->getPrefix();
    $fName = $customer->getFirstname();
    $mName = $customer->getMiddlename();
    $lName = $customer->getLastname();
    $fullName = "";

    $sql = "";
    if ($prefix) {
        $fullName .= $prefix . " ";
    }
    $fullName .= $fName . " ";
    if ($mName) {
        $fullName .= $mName;
    }
    $fullName .= $lName;
    $email = $customer->getEmail();
    $formattedEmail = $emailFormatterHelper->startMailFormating($email, $fullName, null);
    //echo $customerId."-".strlen($fName).":".strlen($lName)."</br>";
    //if (strlen($fName) < 1 || strlen($lName) < 1) {
        $fullName = explode("@", $formattedEmail, 2)[0];
        //$this->salesforceLog("Tried to get data of Customer or Contact - " . $$customerId . ". But Customer or Contact Doesn't have any name-" . $salesforceId . "Changed name too = " . $fullName);
        $sql = $sql . "REPLACE INTO `customer_entity_varchar`(entity_type_id,attribute_id,entity_id,value) VALUES(1,7,{$customerId},'{$fullName}');";
    //}

    if ($formattedEmail != $email) {
        //$sql = $sql. "REPLACE INTO `customer_entity_varchar`(entity_type_id,attribute_id,entity_id,value) VALUES(1,9,{$customerId},'{$formattedEmail}');";
        $sql = $sql . "UPDATE `customer_entity` SET email = '{$formattedEmail}' WHERE entity_id = {$customerId};";
    }
    var_dump($sql);
}
die;

//$helper = Mage::helper("allure_salesforce/salesforceClient");






$email = $helper->startMailFormating("ALICENAM@GM,AIL.COM","asd",null);
$email = $helper->startMailFormating($customer->getEmail(),$fullName,null);
var_dump($email);die;


die;
$model = Mage::getModel('allure_salesforce/observer_update');
$arr = array(48572,18087,18688,48691,48693,18982,18981,34111,34112,18244,18239,18234,18229,18224,48722,18715,20097,48460,48458,48463,48459,48462,48461,48471,43357,12189,12188,12187,12183,4954,4955,4956,4957,4958,4959,4960,4961,4962,4963,4964,4965,4966,4967,4968,31923,32002,22737,40302,40303,40304,34278,34309,48905,48904,48902,48903);
$sRequest = array();
foreach($arr as $ar) {
    $product = Mage::getModel('catalog/product')->load($ar);
    $salesforceProductId = $product->getSalesforceProductId();
    $wholesalePrice = 0;
    foreach ($product->getData('group_price') as $gPrice) {
        if ($gPrice["cust_group"] == 2) { //wholesaler group : 2
            $wholesalePrice = $gPrice["price"];
        }
    }

    $sTemp = array(
        "attributes" => array(
            "type" => "PricebookEntry",
            "referenceId" => "productW-" . $product->getId()
        ),
        "Pricebook2Id" => Mage::helper('allure_salesforce')->getWholesalePricebook(),//$this::WHOLESELLER_PRICEBOOK_ID,
        "Product2Id" => $salesforceProductId,
        "UnitPrice" => $wholesalePrice
    );
    array_push($sRequest, $sTemp);
}
//$model->sendCompositeRequest(array("products" => $sRequest),null);
$requestData = array("pb" => $sRequest);
$objectMappings = array(
    "pb" => "PriceBookEntry"
);
foreach ($requestData as $modelName => $reqArr) {
    if (!empty($reqArr)) {
        $chunkedReqArray = array_chunk($reqArr, 200);
        foreach ($chunkedReqArray as $reqArray) {
            $request["records"] = $reqArray;

            if (empty($lastRunTime)) {
                $urlPath = "/services/data/v42.0/composite/tree/" . $objectMappings[$modelName];
                $requestMethod = "POST";
            }
            print_r(json_encode($request,true));die;
            $response = $helper->sendRequest($urlPath, $requestMethod, $request);
            $responseArr = json_decode($response, true);

            if (!$responseArr["hasErrors"]) {
                $helper->salesforceLog("bulk operation was succesfull");
                $helper->addSalesforcelogRecord("BULK operation ", $requestMethod, "BULKOP-" . $lastRunTime, $response);
                if (empty($lastRunTime))
                    $helper->bulkProcessResponse($responseArr, $modelName);
            }
        }
    }
}
print_r($sRequest);die;

//$order = Mage::getModel('sales/order')->load(437297);
//var_dump($order->getCouponCode());
//
//var_dump($order->getCouponRuleName());
//
//
//die;

$product = Mage::getModel('catalog/product')->load(49132);
$stoneWeightClassification = $product->getData('stone_weight_classification');
$barcode = $product->getData('barcode');
var_dump($barcode);die;
var_dump($product->getData('weight'));die;

//$sd = Mage::getModel('eav/attribute_option_value')->load(1129);

$_collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
    ->setAttributeFilter(238)
    ->setStoreFilter(0)
    ->load();

$s = $_collection->toOptionArray();
foreach($s as $t) {
    if($t['value'] == $product->getData('custom_stock_status')) {
        var_dump($t['label']);
    }
}

die;




$_customer = Mage::getModel('customer/customer')->load(189547);

//$subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($_customer);
//var_dump($subscriber->isSubscribed());die;
//$_customer->setConfirmation(1);
//$_customer->save();die;
//var_dump($_customer->getCreatedAt());die;

$collection = Mage::getResourceModel('sales/sale_collection')
    ->setCustomerFilter($_customer)
    ->setOrderStateFilter(Mage_Sales_Model_Order::STATE_CANCELED, true)
    ->load()
;
var_dump($collection->getTotals()->getBaseLifetime());
var_dump($collection->getTotals()->getAvgsale());
die;
if (!$_customer->getConfirmation()) {
    var_dump(Mage::helper('customer')->__('Confirmed'));echo "adada";
}

if ($_customer->isConfirmationRequired()) {
    var_dump(Mage::helper('customer')->__('Not confirmed, cannot login'));echo "bsda";
}

var_dump(Mage::helper('customer')->__('Not confirmed, can login'));echo "ccsada";

die;

$config = "allure_salesforce/general/bulk_cron_time";
var_dump(Mage::getStoreConfig($config));die;

//$requestData = array(
//    "orders" => array(454144,454145),
//    "order_items" => array(737038,737039),
//    "customers" => array(186464),
//    "contact" => array(186464),
//    "invoice" => array(499066,499067),
//    "credit_memo" => array(22535,22536),
//    "shipment" => array(329824,329823),
//);

$requestData = array(
    "orders" => array(454158),
);

$lastRunTime = new DateTime("1 hour ago");  //static right now only for test purpose
//print_r($lastRunTime);die;
$model = Mage::getModel("allure_salesforce/observer_update");
$model->getRequestData(null,$requestData);
//$model->getRequestData($lastRunTime,null);
die;

//$model = Mage::getModel("allure_salesforce/observer_update");
//$data = $model->getRequestData();
//echo "<pre>";
//print_r($data,true);
//die;