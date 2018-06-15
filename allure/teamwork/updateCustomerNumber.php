<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$emailIndex = 0;
$csv = Mage::getBaseDir('var').DS."teamwork".DS.'customer4.csv';
$io = new Varien_Io_File();
$io->streamOpen($csv, 'r');
$counter=0;
$logFile='resync_sucess.log';
$helper = Mage::helper("allure_teamwork");
$_url   = $helper->getTeamworkUrl() . $helper::UPADTE_CUSTOMER_URLPATH;
$_accessToken = $helper->getTeamworkAccessToken();

while($csvData = $io->streamReadCsv()){

    $email =trim($csvData[$emailIndex]);
    
    $customer = Mage::getModel("customer/customer");
    $customer->setWebsiteId(Mage::app()->getWebsite('admin')->getId());
    $customer->loadByEmail($email);
    
    if($customer->getId()){
        
            if(empty($customer->getCounterpointCustNo())){
                Mage::log("Email-:".$customer->getEmail(),Zend_Log::DEBUG,'resync_cpidmissing.log',true);
                continue;
            }
            if(empty($customer->getTeamworkCustomerId())){
                Mage::log("Email-:".$customer->getEmail(),Zend_Log::DEBUG,'resync_twidmissing.log',true);
                continue;
            }
            $request = array();
            $request['customerID']  = $customer->getTeamworkCustomerId();
            //  $request['primaryEmail']= array($customer->getEmail());
            $request['customText4'] = $customer->getCounterpointCustNo();
            //  $page=$customer->getEntityId();
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
            $total=$total++;
            $json_arguments = json_encode($request);
            curl_setopt($sendRequest, CURLOPT_POSTFIELDS, $json_arguments);
            $response = curl_exec($sendRequest);
            curl_close($sendRequest);
            $responseObj = json_decode($response);
            Mage::log("Email-:".$customer->getEmail(),Zend_Log::DEBUG,$logFile,true);
            Mage::log("ID-:".$customer->getId(),Zend_Log::DEBUG,$logFile,true);
            Mage::log("Response -:".$response,Zend_Log::DEBUG,$logFile,true);
            
            
    }else{
        Mage::log("Email not found:".$email,Zend_log::DEBUG,'resync_emailmissing.log',true);
    }
}
die("finished");