<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$helper = Mage::helper("allure_teamwork");
$_url   = $helper->getTeamworkUrl() . $helper::UPADTE_CUSTOMER_URLPATH;
$_accessToken = $helper->getTeamworkAccessToken();

$csv = Mage::getBaseDir('var').DS."teamwork".DS.'duplicateCPN.csv';
$io = new Varien_Io_File();
$io->streamOpen($csv, 'r');
$logFile='resync_sucess.log';
$idIndex = 0;

while ($csvData = $io->streamReadCsv()) {
    
    $customerNumber =trim($csvData[$idIndex]);
    
    $customerCollection = Mage::getModel('customer/customer')->getCollection();
    $customerCollection->addAttributeToFilter('counterpoint_cust_no', $customerNumber);
    $customerCollection->getSelect()->order('entity_id ASC');
    
 
    if (count($customerCollection) < 2)
        continue;
    
    $cntr = 0;
    
    foreach ($customerCollection as $customer) {
        if ($cntr == 0) {
            $cntr ++;
            continue;
        }
        $customer = Mage::getModel('customer/customer')->load($customer->getId());
        if ($customer->getId()) {
            
            if (empty($customer->getTeamworkCustomerId())) {
                Mage::log("Email-:" . $customer->getEmail(), Zend_Log::DEBUG, 'resync_twidmissing.log', true);
                continue;
            }
            
            $request = array();
            $request['customerID'] = $customer->getTeamworkCustomerId();
            // $request['primaryEmail']= array($customer->getEmail());
            $request['customText4'] = '';
            $request['status'] = 'INACTIVE';
            $request['isInactive'] = true;
            // $page=$customer->getEntityId();
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
            Mage::log("Email-:" . $customer->getEmail(), Zend_Log::DEBUG, $logFile, true);
            Mage::log("ID-:" . $customer->getId(), Zend_Log::DEBUG, $logFile, true);
            Mage::log("Request -:" . $json_arguments, Zend_Log::DEBUG, $logFile, true);
            Mage::log("Response -:" . $response, Zend_Log::DEBUG, $logFile, true);
           
            
           try {
               Mage::log("ID::".$customer->getId()." Email-:" . $customer->getEmail(), Zend_Log::DEBUG, 'resync_twidelcustomer.log', true);
               $customer->setCounterpointCustNo('');
               $customer->save();
               
              // $customer->delete();
           } catch (Exception $e) {
               
           }
        }
    }
    
}
die("Finished");