<?php
require_once ('../../app/Mage.php');
umask(0);
Mage::app();

Mage::app()->setCurrentStore(0);

//set default page size
$PAGE_SIZE   = 2000;
//set default page number
$PAGE_NUMBER = 1;
//log file name
$accountHistory = "account_history.log";

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

//.csv file header data
$header = array(
    "Customer_ID__c"            => "Customer_ID__c",
    "Name"                      => "Name",
    "AccountNumber"             => "AccountNumber",
    "Site"                      => "Site",
    "AccountSource"             => "AccountSource",
    "Birth_Date__c"             => "Birth_Date__c",
    "Company__c"                => "Company__c",
    "Counterpoint_No__c"        => "Counterpoint_No__c",
    "Created_In__c"             => "Created_In__c",
    "Customer_Note__c"          => "Customer_Note__c",
    "Default_Billing__c"        => "Default_Billing__c",
    "Default_Shipping__c"       => "Default_Shipping__c",
    "Description"               => "Description",
    "Email__c"                  => "Email__c",
    "Fax"                       => "Fax",
    "Gender__c"                 => "Gender__c",
    "Group__c"                  => "Group__c",
    "Phone"                     => "Phone",
    "Store__c"                  => "Store__c",
    "BillingStreet"             => "BillingStreet",
    "BillingCity"               => "BillingCity",
    "BillingState"              => "BillingState",
    "BillingPostalCode"         => "BillingPostalCode",
    "BillingCountry"            => "BillingCountry",
    "ShippingStreet"            => "ShippingStreet",
    "ShippingCity"              => "ShippingCity",
    "ShippingState"             => "ShippingState",
    "ShippingPostalCode"        => "ShippingPostalCode",
    "ShippingCountry"           => "ShippingCountry"
);

try{
    //get collection of customer according to page number, page size & asending order
    $collection = Mage::getModel("customer/customer")->getCollection()
    ->addAttributeToSelect("*")
    ->setPageSize($PAGE_SIZE)
    ->setCurPage($PAGE_NUMBER)
    ->setOrder('entity_id', 'asc');
    
    Mage::log("collection size = ".$collection->getSize(),Zend_Log::DEBUG,$accountHistory,true);
    
    //open or create .csv file
    $io           = new Varien_Io_File();
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "account";
    $filename     = "ACCOUNT_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    $io->streamOpen($filepath , "w+");
    $io->streamLock(true);
    
    //add header data into .csv file
    $io->streamWriteCsv($header);
    
    foreach ($collection as $customer){
        try{
            //prepare .csv row data using array
            $prefix = $customer->getPrefix();
            $fName = $customer->getFirstname();
            $mName = $customer->getMiddlename();
            $lName = $customer->getLastname();
            $fullName = "";
            if($prefix){
                $fullName .= $prefix." ";
            }
            $fullName .= $fName . " ";
            if($mName){
                $fullName .= $mName;
            }
            $fullName .= $lName;
            
            
            $defaultBillingAddr     = $customer->getDefaultBillingAddress();
            $state       = "";
            $countryName = "";
            if($defaultBillingAddr){
                if($defaultBillingAddr['region_id']){
                    $region = Mage::getModel('directory/region')
                    ->load($defaultBillingAddr['region_id']);
                    $state = $region->getName();
                }else{
                    $state = $defaultBillingAddr['region'];
                }
                
                $country = Mage::getModel('directory/country')
                ->loadByCode($defaultBillingAddr['country_id']);
                $countryName = $country->getName();
            }
            
            $stateShip       = "";
            $countryNameShip = "";
            $defaultShippingAddr    = $customer->getDefaultShippingAddress();
            if($defaultShippingAddr){
                if($defaultBillingAddr['region_id']){
                    $region = Mage::getModel('directory/region')
                    ->load($defaultShippingAddr['region_id']);
                    $stateShip = $region->getName();
                }else{
                    $stateShip = $defaultShippingAddr['region'];
                }
                
                $country = Mage::getModel('directory/country')
                ->loadByCode($defaultShippingAddr['country_id']);
                $countryNameShip = $country->getName();
            }
            
            $row = array(
                "Customer_ID__c"      => $customer->getId(),
                "Name"                => $fullName,
                "AccountNumber"       => "",
                "Site"                => "",
                "AccountSource"       => "",
                "Birth_Date_c"        => ($customer->getDob()) ? date("Y-m-d",strtotime($customer->getDob())) : "",//"YYYY-MM-DD",
                "Company__c"          => $customer->getCompany(),
                "Counterpoint_No__c"  => $customer->getCounterpointCustNo(),
                "Created_In__c"       => "",
                "Customer_Note__c"    => $customer->getCustomerNote(),
                "Default_Billing__c"  => $customer->getDefaultBilling(),
                "Default_Shipping__c" => $customer->getDefaultShipping(),
                "Description"         => "",
                "Email__c"            => $customer->getEmail(),
                "Fax"                 => "",
                "Gender__c"           => ($customer->getGender()) ? $customer->getGender() : 4,
                "Group__c"            => $customer->getGroupId(),
                "Phone"               => ($defaultBillingAddr) ? $defaultBillingAddr->getTelephone() : "",
                "Store__c"            => $customer->getStoreId(),
                "BillingStreet"       => ($defaultBillingAddr) ? implode(", ", $defaultBillingAddr->getStreet()) : "",
                "BillingCity"         => ($defaultBillingAddr) ? $defaultBillingAddr->getCity() : "",
                "BillingState"        => ($defaultBillingAddr) ? $state : "",
                "BillingPostalCode"   => ($defaultBillingAddr) ? $defaultBillingAddr->getPostcode() : "",
                "BillingCountry"      => ($defaultBillingAddr) ? $countryName : "",
                "ShippingStreet"      => ($defaultShippingAddr) ? implode(", ",$defaultShippingAddr->getStreet()) : "",
                "ShippingCity"        => ($defaultShippingAddr) ? $defaultShippingAddr->getCity() : "",
                "ShippingState"       => ($defaultShippingAddr) ? $stateShip : "",
                "ShippingPostalCode"  => ($defaultShippingAddr) ? $defaultShippingAddr->getPostcode() : "",
                "ShippingCountry"     => ($defaultShippingAddr) ? $countryNameShip : ""
            );
            
            //add row data into .csv file
            $io->streamWriteCsv($row);
            $row = null;
        }catch (Exception $ee){
            Mage::log("Sub Exception:".$ee->getMessage(),Zend_Log::DEBUG,$accountHistory,true);
            Mage::log("Occured for Customer Id:".$customer->getId(),Zend_Log::DEBUG,$accountHistory,true);
        }
    }
    $io->close();
}catch (Exception $e){
    Mage::log("Main Exception:".$e->getMessage(),Zend_Log::DEBUG,$accountHistory,true);
}

Mage::log("Finish...",Zend_Log::DEBUG,$accountHistory,true);
die("Finish...");

