<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$file = $_GET["file"];

if(empty($file)){
    die("Specify file path.");
}

$alphabets = range('A','Z');
$numbers = range('0','9');
$additional_characters = array('#','@','$');
$final_array = array_merge($alphabets,$numbers,$additional_characters);


$teamworkLog = "customer_in_teamwork.log";

$folderPath   = Mage::getBaseDir("var") . DS .$file;

$csvData = array();
if(($handle = fopen($folderPath, "r")) != false){
    $max_line_length = defined("MAX_LINE_LENGTH") ? MAX_LINE_LENGTH : 10000;
    $header = fgetcsv($handle, $max_line_length);
    foreach ($header as $c => $_cols){
        $header[$c] = strtolower(str_replace(" ", "_", $_cols));
    }
    
    $header_column_count = count($header);
    
    while (($row = fgetcsv($handle,$max_line_length)) != false){
        $row_column_count = count($row);
        if($row_column_count == $header_column_count){
            $entry = array_combine($header, $row);
            $csvData[] = $entry;
        }
    }
    fclose($handle);
    
    if(count($csvData)){
        $websiteId = 1;
        foreach ($csvData as $data){
            $email = $data["email"];
            
            if(empty($email)){
                Mage::log("Not found. TWID:".$teamworkId." Name:".$firstName." ".$lastName,Zend_log::DEBUG,$teamworkLog,true);
                continue;
            }
            
            $customer = Mage::getModel('customer/customer')
            ->setWebsiteId($websiteId)
            ->loadByEmail($email);
            
            
            if($customer->getId()){
                try{
                    /* $isTmCustomer = $customer->getIsTeamworkCustomer();
                    if(!$isTmCustomer){
                        continue;
                    } */
                    
                    $acceptMarketing = trim($data["accept_marketing"]);
                    $acceptTransactional = trim($data["accept_transactional"]);
                    
                    $customer->setTwAcceptMarketing($acceptMarketing)
                    ->setTwAcceptTransactional($acceptTransactional)->save();
                    
                    $createdAtArr = explode(".", trim($data["created_at"]));
                    
                   // $createdAt1 = trim($createdAtArr[0]);
                    //$customer->setCreatedAt($createdAt1)->save();
                    Mage::log("Email:".$email." Customer Id :".$customer->getId()." accept Trans : Marketing = ".$acceptTransactional." : ".$acceptMarketing,Zend_log::DEBUG,$teamworkLog,true);
                    
                }catch (Exception $e1){
                    Mage::log("Email:".$email." Customer Id :".$customer->getId()." Exception".$e1->getMessage(),Zend_log::DEBUG,$teamworkLog,true);
                }
            }
            continue;
            
            if($customer->getId()){
                Mage::log("Already exists. TWID:".$teamworkId." Email:".$email." Customer ID:".$customer->getId(),Zend_log::DEBUG,$teamworkLog,true);
                continue;
            }
            
            $teamworkId = $data["teamwork_customer_id"];
            $firstName  = $data["firstname"];
            $lastName = $data["lastname"];
            $createdAt = $data["created_at"];
            $streets = $data["street"];
            
            $streetArr = explode(",", $streets);
            
            $city = $data["city"];
            $state = $data["state"];
            $country = $data["country"];
            $postalCode = $data["postal_code"];
            $phone = $data["phone"];
            $group = ($data["is_wholesale"])?2:1;
            
            $acceptMarketing = $data["accept_marketing"];
            $acceptTransactional = $data["accept_transactional"];
            
            $password = '';
            $length = 8;  //password length
            while($length--) {
                $keyV = array_rand($final_array);
                $password .= $final_array[$keyV];
            }
            
            try{
                $customer = Mage::getModel("customer/customer");
                $customer->setWebsiteId($websiteId)
                ->setStoreId($storeId)
                ->setGroupId($group)
                ->setFirstname($firstName)
                ->setLastname($lastName)
                ->setEmail($email)
                ->setCreatedAt($createdAt)
                ->setPassword($password)
                ->setPasswordConfirmation($password)
                ->setPasswordCreatedAt(time())
                
                ->setCustomerType(20)  //teamwork db - 20 
                ->setTeamworkCustomerId($teamworkId)
                ->setIsTeamworkCustomer(1)
                ->setTwAcceptMarketing($acceptMarketing)
                ->setTwAcceptTransactional($acceptTransactional)
                ->save();
                
                Mage::log("TWID:".$teamworkId." New customer create. Email:".$customer->getEmail()." Customer ID:".$customer->getId(),Zend_log::DEBUG,$teamworkLog,true);
                
                if($customer->getId()){
                    $_custom_address = array (
                        'firstname'  => $customer->getFirstname(),
                        'lastname'   => $customer->getLastname(),
                        /* 'street'     => array (
                            '0' => $street
                        ), */
                        'city'       => $city,
                        'postcode'   => $postalCode,
                        'country_id' => $country,
                        'region' 	=> 	$state,
                        'telephone'  => $phone,
                        'fax'        => '',
                    );
                    
                    foreach ($streetArr as $street){
                        $_custom_address[] = trim($street);
                    }
                    
                    $address = Mage::getModel("customer/address");
                    $address->setData($_custom_address)
                    ->setCustomerId($customer->getId())
                    ->setIsDefaultBilling('1')
                    ->setIsDefaultShipping('1')
                    ->setSaveInAddressBook('1');
                    $address->save();
                    Mage::log("Address Create for Customer Id:".$customer->getId()." Email".$customer->getEmail(),Zend_log::DEBUG,$teamworkLog,true);
                }
                
            }catch (Exception $e){
                Mage::log("TWID:".$teamworkId." Name:".$firstName." ".$lastName." :: Exception".$e->getMessage(),Zend_log::DEBUG,$teamworkLog,true);
            }
            
        }
    }
}

Mage::log("Finish...",Zend_log::DEBUG,$teamworkLog,true);
die("Finish...");


