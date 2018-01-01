<?php
require_once('../../app/Mage.php'); 
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);

$start = $_GET['start'];
$end   = $_GET['end'];

if(empty($start)){
    die("Mention start");
}

if(empty($end)){
    die("Mention end");
}

$customers  = Mage::getModel('customer/customer')
    ->getCollection()
    ->addAttributeToSelect('*');

$customers->getSelect()
    ->join(array('teamwork' => 'allure_teamwork_customer'),
            'teamwork.customer_id = e.entity_id AND teamwork.is_error=1'.
            ' AND(teamwork.customer_id>='.$start.' AND teamwork.customer_id<='.$end.')',
            array('teamwork.id')
      );


$_url         = "https://api.teamworksvs.com/externalapi3/customers/register";
//$_accessToken = "bWFyaWF0dGVzdCA1NzMyNTY4NTQ4NzY5NzkyIDYzM3paNTZ1Z0w4V3puOU1VUTlNcDUzblZYVGNzZlN3";

$teamwoek_log_file = "teamwork_mag_error_customer_3.log";

$helper = Mage::helper("allure_teamwork");
$status = $helper->getTeamworkStatus();
if($status == 0){
    die("Teamwork status is Inactive");
}

$_accessToken = $helper->getTeamworkAccessToken();
if(empty($_accessToken)){
    die("Please Specify access token");
}

$model  = Mage::getModel("allure_teamwork/teamwork");

$guid1 = "";
$guid2 = "";

foreach ($customers as $customer){
   try{
        $data    = $customer->getData();
        $customer_id = $customer->getId();
        $request = array();
        $request['firstName'] = $data['firstname'];
        $request['lastName']  = $data['lastname'];
        $email = strtolower($data['email']);
        if(!empty($data['email'])){
            $request['email1']    = (object) array("email"=> $email);
        }
        $request['customText1'] = $data['website_id'];
        $request['customFlag1'] = ($data['group_id'] == 2 )?true:false;
        //$request['isEmployee']  = true;
        
        $billingAddr  = $customer->getDefaultBillingAddress();
        $shippingAddr = $customer->getDefaultShippingAddress();
        
        //$billingAddrData = $billingAddr->getData();
        
        $addressArr =  array();
        
        if($billingAddr){
            $billingAddrData = $billingAddr->getData();
            $guid1 = getGuid4();
            $addressArr[] = (object) array(
                        "addressID" => $guid1,
                        "firstName" =>  $billingAddrData['firstname'],
                        "lastName"  =>  $billingAddrData['lastname'],
                        "address1"  =>  ($billingAddrData['street'])?$billingAddrData['street']:null,
                        "city"    =>  ($billingAddrData['city'])?$billingAddrData['city']:null,
                        "region"    =>  ($billingAddrData['state'])?$billingAddrData['state']:null,
                        "countryCode"   =>  ($billingAddrData['country_id'])?$billingAddrData['country_id']:null,
                        "postalCode"    =>  ($billingAddrData['postcode'])?$billingAddrData['postcode']:null,
                        "phone" =>  ($billingAddrData['telephone'])?$billingAddrData['telephone']:null
                );
            $request['defaultBillingAddressID'] = $guid1;
        }
        
        if(!empty($billingAddrData['telephone'])){
            if($billingAddrData['telephone'] !="000-000-0000")
                $request['phone1'] = (object) array("number"=>$billingAddrData['telephone']);
        }
        
        if($shippingAddr){
            $shippingAddrData = $shippingAddr->getData();
            $guid2 = getGuid4();
            $addressArr[] =  (object) array(
                    "addressID" => $guid2,
                    "firstName" =>  $shippingAddrData['firstname'],
                    "lastName"  =>  $shippingAddrData['lastname'],
                    "address1"  =>  ($shippingAddrData['street'])?$shippingAddrData['street']:null,
                    "city"    =>  ($shippingAddrData['city'])?$shippingAddrData['city']:null,
                    "region"    =>  ($shippingAddrData['state'])?$shippingAddrData['state']:null,
                    "countryCode"   =>  ($shippingAddrData['country_id'])?$shippingAddrData['country_id']:null,
                    "postalCode"    =>  ($shippingAddrData['postcode'])?$shippingAddrData['postcode']:null,
                    "phone" =>  ($shippingAddrData['telephone'])?$shippingAddrData['telephone']:null
            );
            $request['defaultShippingAddressID'] = $guid2;
        }
        
        $request['addresses'] = $addressArr;
        
        
        //print_r(json_encode($request));
        //die;
        
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
        
        $model = Mage::getModel("allure_teamwork/teamwork")->load($customer_id,'customer_id');
        if(!$model->getId()){
            $model = Mage::getModel("allure_teamwork/teamwork");
        }
        
        $model->setCustomerId($customer_id)
            ->setEmail($email)
            ->setAutoGenBillId($guid1)
            ->setAutoGenShipId($guid2);
        
        if(!$responseObj->errorCode){
            $teamworkCustomerId = $responseObj->customer->customerID;
            $customerObj = Mage::getModel("customer/customer")->load($customer_id);
            $customerObj->setTeamworkCustomerId($teamworkCustomerId);
            $customerObj->save();
            
            $model->setResponse($response)
                ->setIsError(0)
                ->setTeamworkCustomerId($teamworkCustomerId);
            
            //echo $teamworkCustomerId."<br>";
                Mage::log("id-:".$customer->getId()." email-:".$email." == teamwork_id-:".$teamworkCustomerId,Zend_log::DEBUG,$teamwoek_log_file,true);
        }
        else {
            $model->setIsError(1)
            ->setResponse($response);
            //echo "<br>Error".json_encode($responseObj);
            Mage::log("id-:".$customer->getId()." email-:".$email." == error-:".$response,Zend_log::DEBUG,$teamwoek_log_file,true);
        }
        
        $model->save();
        $model = null;
    }catch (Exception $e){
        Mage::log("id-:".$customer->getId(). " email-:".$email." == Exception-:".$e->getMessage(),Zend_log::DEBUG,$teamwoek_log_file,true);
        //die;
    }
}

Mage::log("Finish...",Zend_log::DEBUG,$teamwoek_log_file,true);

//get guid
function getGuid4(){
    $charid = strtolower(md5(uniqid(rand(), true)));
    $hyphen = chr(45);                  // "-"
    $lbrace = "";//$trim ? "" : chr(123);    // "{"
    $rbrace = "";//$trim ? "" : chr(125);    // "}"
    $guidv4 = $lbrace.
    substr($charid,  0,  8).$hyphen.
    substr($charid,  8,  4).$hyphen.
    substr($charid, 12,  4).$hyphen.
    substr($charid, 16,  4).$hyphen.
    substr($charid, 20, 12).
    $rbrace;
    return strtoupper($guidv4);
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
