<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');


$logFile = "create_tw_customer.log";
$logFileError = "create_tw_customer_error.log";
$emailIndex = 0;
$csv = Mage::getBaseDir('var').DS."teamwork".DS.'unsynced.csv';
$io = new Varien_Io_File();
$io->streamOpen($csv, 'r');

$model  = Mage::getModel("allure_teamwork/teamwork");
//Mariatest2
//$_accessToken = "bWFyaWF0dGVzdDIgNTYyOTQ5OTUzNDIxMzEyMCB1ZnlQM3VIM05nN1g1WTJYODdaWk5PSk91SjF1dXEzUw==";
//Mariatash live
$_accessToken= "bWFyaWF0YXNoIDU2NTkzMTM1ODY1NjkyMTYgakUyMEJhWU5RVjhyQVVDRUpWV2tXQ3JhaUFJNjYzQ3A=";
$_url         = "https://api.teamworksvs.com/externalapi3/customers/register";
$guid1 = "";
$guid2 = "";
$count=0;
$countError=0;
while($csvData = $io->streamReadCsv()){
    $email = trim($csvData[$emailIndex]);
    $customer = Mage::getModel('customer/customer')
    ->loadByEmail($email);
    if($customer->getId()){
        Mage::log("found::".$email,Zend_log::DEBUG,$logFile,true);
        try {
            $data           = $customer->getData();
            if(!$customer->getIsDuplicate()){
                
                $customer_id    = $customer->getId();
                $request        = array();
                $request['firstName'] = $data['firstname'];
                $request['lastName']  = $data['lastname'];
                $email = strtolower($data['email']);
                
                $request['magentoID'] = $customer_id;
                
                $cpCustNo = $customer->getCounterpointCustNo();
                if(!empty($cpCustNo)){
                    $request['customText4'] = $cpCustNo;
                }
                
                $customerNote = $customer->getCustNote();
                if(!empty($customerNote)){
                    $request['largeMemo'] = $customerNote;
                }
                
                if(!empty($data['email'])){
                    $request['email1']    = (object) array(
                        "email"=> $email,
                        "acceptMarketing"=>true
                    );
                }
                
                $taxVat = $customer->getTaxvat();
                if(!empty($taxVat)){
                    $request['VATRegistrationNumber'] = $taxVat;
                }
                
                $request['customText1'] = $data['website_id'];
                $request['customFlag1'] = ($data['group_id'] == 2 )?true:false;
                
                $billingAddr  = $customer->getDefaultBillingAddress();
                $shippingAddr = $customer->getDefaultShippingAddress();
                
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
                    if($billingAddrData['telephone'] !="000-000-0000"){
                        $request['phone2'] = (object) array("number"=>$billingAddrData['telephone']);
                    }
                }
                
                if($shippingAddr){
                    $shippingAddrData = $shippingAddr->getData();
                    $guid2 =getGuid4();
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
                
                $model = Mage::getModel("allure_teamwork/teamwork")->load($customer_id,'customer_id');
                if(!$model->getId()){
                    $model = Mage::getModel("allure_teamwork/teamwork");
                }
                
                $custType = $customer->getCustomerType();
                $model->setCustomerId($customer_id)
                ->setEmail($email)
                ->setAutoGenBillId($guid1)
                ->setAutoGenShipId($guid2)
                ->setCounterpointCustNo($cpCustNo)
                ->setCustomerNote($customerNote)
                ->setIsCounterpointCust($custType);
                
                if(!$responseObj->errorCode){
                    $teamworkCustomerId = $responseObj->customer->customerID;
                    $customerObj = Mage::getModel("customer/customer")->load($customer_id);
                    $customerObj->setTeamworkCustomerId($teamworkCustomerId);
                    $customerObj->save();
                    
                    $model->setResponse($response)
                    ->setTeamworkCustomerId($teamworkCustomerId);
                    
                    Mage::log($count."id-:".$customer->getId()." email-:".$email." == teamwork_id-:".$teamworkCustomerId,Zend_log::DEBUG,$logFile,true);
                }
                else {
                    $countError++;
                    $model->setIsError(1)
                    ->setResponse($response);
                    Mage::log($countError."id-:".$customer->getId()." email-:".$email." == error-:".$response,Zend_log::DEBUG,$logFileError,true);
                }
                
                $model->save();
                $model = null;
            }
        } catch (Exception $e) {
            Mage::log($countError."Exception Occured::".$email."   Message::".$e->getMessage(),Zend_log::DEBUG,$logFileError,true);
        }
       
    }else{
        Mage::log($count."::  Customer not found::".$email,Zend_log::DEBUG,$logFileError,true);
    }
    $count++;
   
}
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
echo "Done";