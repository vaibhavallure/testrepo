<?php
require_once('../../app/Mage.php'); 
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);

/* $customer=Mage::getModel("customer/customer")->load(3329);
var_dump($customer->getData());
die; */

$customers  = Mage::getModel('customer/customer')
    ->getCollection()
    ->addAttributeToSelect('*')
    //->addAttributeToFilter('entity_id', array('eq' => 3288))
    ->addAttributeToFilter('entity_id',
        array(
            'gteq' => 4500
        )
    )
    ->addAttributeToFilter('entity_id', 
        array(
            'lteq' => 4600
        )
    )
    ->load();

$_url         = "https://api.teamworksvs.com/externalapi2/customers/register";
$_accessToken = "bWFyaWF0dGVzdCA1NzMyNTY4NTQ4NzY5NzkyIDYzM3paNTZ1Z0w4V3puOU1VUTlNcDUzblZYVGNzZlN3";

$teamwoek_log_file = "teamwork_mag_customer.log";

foreach ($customers as $customer){
    try{
        $data    = $customer->getData();
        $customer_id = $customer->getId();
        $request = array();
        $request['firstName'] = $data['firstname'];
        $request['lastName']  = $data['lastname'];
        if(!empty($data['email'])){
            $request['email1']    = $data['email']; //(object) array("email"=>$data['email']);
        }
        $request['customText1'] = $data['website_id'];
        $request['customFlag1'] = ($data['group_id'] == 2 )?true:false;
        $billingAddr  = $customer->getDefaultBillingAddress();
        $shippingAddr = $customer->getDefaultShippingAddress();
        if($billingAddr){
            $billingAddrData = $billingAddr->getData();
            $request['address1'] = ($billingAddrData['street'])?$billingAddrData['street']:null;
            $request['state'] = ($billingAddrData['state'])?$billingAddrData['state']:null;
            $request['city'] = ($billingAddrData['city'])?$billingAddrData['city']:null;
            $request['countryCode'] = ($billingAddrData['country_id'])?$billingAddrData['country_id']:null;
            $request['postalCode'] = ($billingAddrData['postcode'])?$billingAddrData['postcode']:null;
            $request['phone1'] = ($billingAddrData['telephone'])?$billingAddrData['telephone']:null;
        }
        
        if($shippingAddr){
            $shippingAddrData = $shippingAddr->getData();
            $request['addresses'] = array(
                array(
                    "firstName" =>  $shippingAddrData['firstname'],
                    "lastName"  =>  $shippingAddrData['lastname'],
                    "address1"  =>  ($shippingAddrData['street'])?$shippingAddrData['street']:null,
                    "city"    =>  ($shippingAddrData['city'])?$shippingAddrData['city']:null,
                    "region"    =>  ($shippingAddrData['state'])?$shippingAddrData['state']:null,
                    "countryCode"   =>  ($shippingAddrData['country_id'])?$shippingAddrData['country_id']:null,
                    "postalCode"    =>  ($shippingAddrData['postcode'])?$shippingAddrData['postcode']:null,
                    "phone" =>  ($shippingAddrData['telephone'])?$shippingAddrData['telephone']:null
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
        curl_close($sendRequest);
        $responseObj = json_decode($response);
        //print_r($response);
        if(!$responseObj->errorCode){
            $teamworkCustomerId = $responseObj->customerID;
            $customerObj = Mage::getModel("customer/customer")->load($customer_id);
            $customerObj->setTeamworkCustomerId($teamworkCustomerId);
            $customerObj->save();
            Mage::log("id-:".$customer->getId()." email-:".$data['email']." == teamwork_id-:".$teamworkCustomerId,Zend_log::DEBUG,$teamwoek_log_file,true);
            //echo $teamworkCustomerId."<br>";
        }
        else {
            //echo "<br>Error".json_encode($responseObj);
            Mage::log("id-:".$customer->getId()." email-:".$data['email']." == error-:".$response,Zend_log::DEBUG,$teamwoek_log_file,true);
        }
    }catch (Exception $e){
        Mage::log("id-:".$customer->getId(). " email-:".$data['email']." == Exception-:".$e->getMessage(),Zend_log::DEBUG,$teamwoek_log_file,true);
        //die;
    }
}
Mage::log("Finish...",Zend_log::DEBUG,$teamwoek_log_file,true);
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
