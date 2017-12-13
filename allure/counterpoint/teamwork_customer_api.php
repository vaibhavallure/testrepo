<?php
require_once('../../app/Mage.php'); 
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);

$customer=Mage::getModel("customer/customer")->load(3329);
var_dump($customer->getData());
die;

$customers  = Mage::getModel('customer/customer')
    ->getCollection()
    ->addAttributeToSelect('*')
    //->addAttributeToFilter('entity_id', array('eq' => 3288))
    ->addAttributeToFilter('entity_id',
        array(
            'gteq' => 3311
        )
    )
    ->addAttributeToFilter('entity_id', 
        array(
            'lteq' => 3330
        )
    )
    ->load();

$_url         = "https://api.teamworksvs.com/externalapi2/customers/register";
$_accessToken = "bWFyaWF0dGVzdCA1NzMyNTY4NTQ4NzY5NzkyIDYzM3paNTZ1Z0w4V3puOU1VUTlNcDUzblZYVGNzZlN3";


foreach ($customers as $customer){
    $data    = $customer->getData();
    $customer_id = $customer->getId();
    $request = array();
    $request['firstName'] = $data['firstname'];
    $request['lastName']  = $data['lastname'];
    $request['email1']    = $data['email'];
    $billingAddr  = $customer->getDefaultBillingAddress();
    $shippingAddr = $customer->getDefaultShippingAddress();
    if($billingAddr){
        $billingAddrData = $billingAddr->getData();
        $request['address1'] =$billingAddrData['street'];
        $request['state'] = $billingAddrData['state'];
        $request['city'] = $billingAddrData['city'];
        $request['countryCode'] = $billingAddrData['country_id'];
        $request['postalCode'] = $billingAddrData['postcode'];
        $request['phone1'] = $billingAddrData['telephone'];
    }
    
    if($shippingAddr){
        $shippingAddrData = $shippingAddr->getData();
        $request['addresses'] = array(
            array(
                "firstName" =>  $shippingAddrData['firstname'],
                "lastName"  =>  $shippingAddrData['lastname'],
                "address1"  =>  $shippingAddrData['street'],
                "city"    =>  $shippingAddrData['city'],
                "region"    =>  $shippingAddrData['state'],
                "countryCode"   =>  $shippingAddrData['country_id'],
                "postalCode"    =>  $shippingAddrData['country_id'],
                "phone" =>  $shippingAddrData['telephone']
            )
        );
    }
    
    $sendRequest = curl_init($_url);
    curl_setopt($sendRequest, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($sendRequest, CURLOPT_HEADER, false);
    curl_setopt($sendRequest, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($sendRequest, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($sendRequest, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($sendRequest, CURLOPT_FOLLOWLOCATION, 0);
    
    curl_setopt($sendRequest, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "Access-Token: {$_accessToken}"
    ));
    
    $json_arguments = json_encode($request);
    curl_setopt($sendRequest, CURLOPT_POSTFIELDS, $json_arguments);
    $response = curl_exec($sendRequest); 
    $responseObj = json_decode($response);
    if(!$responseObj->errorCode){
        $teamworkCustomerId = $responseObj->customerID;
        $customerObj = Mage::getModel("customer/customer")->load($customer_id);
        $customerObj->setTeamworkCustomerId($teamworkCustomerId);
        $customerObj->save();
        echo $teamworkCustomerId."<br>";
    }
    else {
        echo "<br>Error";
    }
}

die;

//teamwork api url

//$json_arguments = '{"customerID":"77BDE782-E50A-428A-88DE-2A6759247EF2"}';

/* $json_arguments = '{"email1" : "test5@allureinc.co", "phone1" : "+919657293987","organization":"Person","postalCode":"411001","address1":"Kothrud" ,
"city":"Pune","state":"Maharashtra","country":"India","birthDay":22,"birthMonth":10,"birthYear":1991,"firstName":"Sagar","lastName":"Kadam",
"suffix":"Mr.","gender":1}'; */

/* $json_arguments = '{"addresses":[{"city":"Pune","fax":null,"suffix":null,
"countryCode":"IN","title":"Mr","middleName":null,"phoneDigits":"919657293982",
"address2":null,"VATRegistrationNumber":null,"phone":"+919657293982",
"firstName":"Abdul","lastName":"Kulkarni","postalCode":"411001",
"organization":"Test","address1":"Kothrud","region":"Maharastra",
"type":"Home","email":"test955@allureinc.co"}
],
"lastTransactionTime":null,"lastTransactionTimeSecond":null,
"birthMonth":10,"LRPConversionDisabled":null,"email1":"test9@allureinc.co",
"city":"Pune","LRPConversionDisabledSecond":null,"birthYear":1991,
"email2":"test95@allureinc.co",
"LLRPBalance":"0.00","state":"Maharashtra","tokenBalance":null,
"phone3":"","postalCode":"411001","acceptMarketing2":false,
"LRPBalance":"0.00","phone2":"","address1":"Kothrud","address2":"",
"phone1":"+919657293990","phone4":"","SCBalance":"0.00",
"isTaxExempt":false,"LRPBalanceSecond":"0.00","firstName":"Abdul",
"lastName":"Kulkarni","defaultGiftcardID":null,"acceptMarketing1":false,
"LLRPBalanceSecond":"0.00","birthDay":22,"title":"Mr","gender":1,
"SCLastTransactionTime":null,"organization":"Person","country":"India",
"isWholesaleCustomer":true,"isEmployee":true,"isTradingPartner":true,
"organization":"Test","homePage":"example.com"
}'; */

// convert requestArguments to json
/* curl_setopt($sendRequest, CURLOPT_POSTFIELDS, $json_arguments);

$response = curl_exec($sendRequest); */
echo "<pre>";
print_r($response);
