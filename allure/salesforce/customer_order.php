<?php
require_once ('../../app/Mage.php');
umask(0);
Mage::app();

Mage::app()->setCurrentStore(0);

//set default page size
$PAGE_SIZE   = 1000;
//set default page number
$PAGE_NUMBER = 1;
//log file name
$accountHistory = "account_history.log";

$ostores = Mage::helper("allure_virtualstore")->getVirtualStores();
$oldStoreArr = array();
foreach ($ostores as $storeO){
    $oldStoreArr[$storeO->getId()] = $storeO->getName();
}
//var_dump($oldStoreArr);die;
echo "<style>
.salesforce-error{
    color: #f90d0d;
    text-align: center;
    margin-top: 10px;
}
</style>";

$pageNumber = $_GET["page"];
$store      = $_GET["store"];
$size       = $_GET['size'];

if(empty($pageNumber)){
    die("<p class='salesforce-error'>Please specify page number.</p>");
}

/* if(empty($store)){
    die("<p class='salesforce-error'>Please specify store.</p>");
}
 */
if(empty(!$size)){
    $PAGE_SIZE = $size;
}

if(is_numeric($pageNumber)){
    $PAGE_NUMBER = (int) $pageNumber;
}else{
    die("<p class='salesforce-error'>Please specify page number in only number format. 
        (eg: 1 or 2 or 3 etc...)</p>");
}

//.csv file header data
$header[] = array(
    "Customer_ID__c"            => "Customer_ID__c",
    "Name"                      => "Name",
    //"AccountNumber"             => "AccountNumber",
    //"Site"                      => "Site",
    //"AccountSource"             => "AccountSource",
    "Birth_Date__c"             => "Birth_Date__c",
    "Company__c"                => "Company__c",
    "Counterpoint_No__c"        => "Counterpoint_No__c",
    "Created_In__c"             => "Created_In__c",
    "Customer_Note__c"          => "Customer_Note__c",
    "Default_Billing__c"        => "Default_Billing__c",
    "Default_Shipping__c"       => "Default_Shipping__c",
    //"Description"               => "Description",
    "Email__c"                  => "Email__c",
    //"Fax"                       => "Fax",
    "Gender__c"                 => "Gender__c",
    "Group__c"                  => "Group__c",
    "Phone"                     => "Phone",
    "Store__c"                  => "Store__c",
    "Teamwork_Customer_ID__c"   => "Teamwork_Customer_ID__c",
    "TW_UC_GUID__c"             => "TW_UC_GUID__c",
    "Old_Store__c"              => "Old_Store__c",
    "BillingStreet"             => "BillingStreet",
    "BillingCity"               => "BillingCity",
    "BillingState"              => "BillingState",
    "BillingPostalCode"         => "BillingPostalCode",
    "BillingCountry"            => "BillingCountry",
    "ShippingStreet"            => "ShippingStreet",
    "ShippingCity"              => "ShippingCity",
    "ShippingState"             => "ShippingState",
    "ShippingPostalCode"        => "ShippingPostalCode",
    "ShippingCountry"           => "ShippingCountry",
);


try{
    
    //echo $collection->getSelect()->__toString();die;
    
    //Mage::log("collection size = ".$collection->getSize(),Zend_Log::DEBUG,$accountHistory,true);
    
    //open or create .csv file
    
    $folderPath   = Mage::getBaseDir("var") . DS . "salesforce" . DS . "account";
    $filename     = "ACCOUNT_STORE_".$store."_".$PAGE_NUMBER.".csv";
    $filepath     = $folderPath . DS . $filename;
    
    
    $io = new Varien_Io_File();
    $io->setAllowCreateFolders(true);
    $io->open(array("path" => $folderPath));
    //$io->streamOpen($filepath , "w+");
    //$io->streamLock(true);
    
    $csv = new Varien_File_Csv();
    
    //add header data into .csv file
    $csv->saveData($filepath,$header);
    
    
    
    
    /* $store = $_GET['store'];
    if($store){
        $collection->addFieldToFilter("old_store_id",$store);
    } */
    
    $customerArr = array();
    for ($storeId = 1 ; $storeId < 14; $storeId++){
        $collection = Mage::getResourceModel("sales/order_collection")
        ->addAttributeToSelect("*")
        ->addFieldToFilter("old_store_id",$storeId)
        ->setPageSize($PAGE_SIZE)
        ->setCurPage($PAGE_NUMBER)
        ->setOrder('entity_id', 'asc');
        
        $collection->getSelect()->where("customer_id is not null");
        
        foreach ($collection as $order){
            $custId = $order->getCustomerId();
            if($custId){
                $customerArr[$custId] = $custId;
            }
        }
        
        $collection = null;
    }
    
   /*  echo "<pre>";
    print_r(count($customerArr));
    die; */
    
    foreach ($customerArr as $customerId){
        try{
            $customer = Mage::getModel("customer/customer")->load($customerId);
            $salesforceId = $customer->getSalesforceCustomerId();
            if($salesforceId){
                continue;
            }
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
                //$state = utf8_encode($state);//htmlspecialchars($state, ENT_NOQUOTES, "UTF-8");
                //$state = iconv('UTF-8', 'ISO-8859-1//TRSANSLIT', $state);
                
                if($defaultBillingAddr['country_id']){
                    $country = Mage::getModel('directory/country')
                    ->loadByCode($defaultBillingAddr['country_id']);
                    $countryName = $country->getName();
                }
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
                
                //$stateShip = utf8_encode($stateShip);
                //$stateShip = iconv('UTF-8', 'ISO-8859-1//TRSANSLIT', $stateShip);
                
                if($defaultShippingAddr['country_id']){
                    $country = Mage::getModel('directory/country')
                    ->loadByCode($defaultShippingAddr['country_id']);
                    $countryNameShip = $country->getName();
                }
            }
            
            $header[] = array(
                "Customer_ID__c"      => $customer->getId(),
                "Name"                => encodeValue($fullName),
                //"AccountNumber"       => "",
                //"Site"                => "",
                //"AccountSource"       => "",
                "Birth_Date_c"        => ($customer->getDob()) ? date("Y-m-d",strtotime($customer->getDob())) : null,//"YYYY-MM-DD",
                "Company__c"          => encodeValue($customer->getCompany()),
                "Counterpoint_No__c"  => $customer->getCounterpointCustNo(),
                "Created_In__c"       => $customer->getCreatedIn(),
                "Customer_Note__c"    => encodeValue($customer->getCustomerNote()),
                "Default_Billing__c"  => $customer->getDefaultBilling(),
                "Default_Shipping__c" => $customer->getDefaultShipping(),
                //"Description"         => "",
                "Email__c"            => encodeValue($customer->getEmail()),
                //"Fax"                 => "",
                "Gender__c"           => ($customer->getGender()) ? $customer->getGender() : 4,
                "Group__c"            => $customer->getGroupId(),
                "Phone"               => ($defaultBillingAddr) ? $defaultBillingAddr->getTelephone() : null,
                "Store__c"            => $oldStoreArr[$customer->getStoreId()],//$customer->getStoreId(),
                "Teamwork_Customer_ID__c"   => $customer->getTeamworkCustomerId(),
                "TW_UC_GUID__c"             => $customer->getTwUcGuid(),
                "Old_Store__c"          => encodeValue($oldStoreArr[$customer->getOldStoreId()]),
                "BillingStreet"       => ($defaultBillingAddr) ? encodeValue(implode(", ", $defaultBillingAddr->getStreet())) : null,
                "BillingCity"         => ($defaultBillingAddr) ? encodeValue($defaultBillingAddr->getCity()) : null,
                "BillingState"        => ($defaultBillingAddr) ? encodeValue($state) : null,
                "BillingPostalCode"   => ($defaultBillingAddr) ? encodeValue($defaultBillingAddr->getPostcode()) : null,
                "BillingCountry"      => ($defaultBillingAddr) ? encodeValue($countryName) : null,
                "ShippingStreet"      => ($defaultShippingAddr) ? encodeValue(implode(", ",$defaultShippingAddr->getStreet())) :null,
                "ShippingCity"        => ($defaultShippingAddr) ? encodeValue($defaultShippingAddr->getCity()) : null,
                "ShippingState"       => ($defaultShippingAddr) ? encodeValue($stateShip) : null,
                "ShippingPostalCode"  => ($defaultShippingAddr) ? encodeValue($defaultShippingAddr->getPostcode()) : null,
                "ShippingCountry"     => ($defaultShippingAddr) ? encodeValue($countryNameShip) : null
            );
            $customer = null;
        }catch (Exception $ee){
            Mage::log("Sub Exception:".$ee->getMessage(),Zend_Log::DEBUG,$accountHistory,true);
            Mage::log("Occured for Customer Id:".$customer->getId(),Zend_Log::DEBUG,$accountHistory,true);
        }
    }
    //add row data into .csv file
    $csv->saveData($filepath,$header);
    //$io->close();
}catch (Exception $e){
    Mage::log("Main Exception:".$e->getMessage(),Zend_Log::DEBUG,$accountHistory,true);
}

function encodeValue($str){
    //iconv('UTF-8', 'ISO-8859-1//TRSANSLIT', $str);
    return @iconv('UTF-8', 'ISO-8859-1//TRSANSLIT', $str); 
}


Mage::log("Finish...",Zend_Log::DEBUG,$accountHistory,true);
die("Finish...");

