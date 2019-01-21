<?php
/**
 * @author aws02
 */
class Allure_Salesforce_Model_Observer_Customer{

    /**
     * return Allure_Salesforce_Helper_SalesforceClient
     */
    private function getHelper(){
        return Mage::helper("allure_salesforce/salesforceClient");
    }

    /**
     * process salesforce customer response data and maintaine logs data
     */
    private function processCustomer($object , $objectType , $fieldName , $requestMethod , $response){
        $responseArr = json_decode($response,true);
        $helper = $this->getHelper();
        $isFailure = false;
        if($responseArr["success"]){
            try{
//                $helper->salesforceLog('Set data on '.$fieldName. '  for objectType'.$objectType.' resMethod'.$requestMethod);
                $object->setData($fieldName, $responseArr["id"]);
                $object->getResource()->saveAttribute($object, $fieldName);
            }catch (Exception $e){
                $isFailure = true;
                $helper->salesforceLog("Exception in processCustomer of object ".$objectType);
                $helper->salesforceLog("Message :".$e->getMessage());
            }
        }else{
            if(!($responseArr == ""))
                $isFailure = true;
        }

        if($isFailure){
            $helper->addSalesforcelogRecord($objectType,$requestMethod,$object->getId(),$response);
        }else{
            $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $object->getId());
        }
    }


    public function addCustomerToSalesforce($customer){
        $helper  = $this->getHelper();
        $ostores = Mage::helper("allure_virtualstore")->getVirtualStores();
        $oldStoreArr = array();

        foreach ($ostores as $storeO){
            $oldStoreArr[$storeO->getId()] = $storeO->getName();
        }

        $oldStoreArr[0] = "Admin";

        if ($customer) {

            $objectType     = $helper::ACCOUNT_OBJECT;
            $objectTypeC    = $helper::CONTACT_OBJECT;   //created for Contact
            $sFieldName     = $helper::S_CUSTOMERID;
            $sCFieldName    = $helper::S_CONTACTID;      //created for Contact

            $salesforceId   = $customer->getSalesforceCustomerId();
            $salesforceContactId   = $customer->getSalesforceContactId();
            $requestMethod  = "GET";
            $requestMethodContact  = "GET";
            $urlPath        = $helper::ACCOUNT_URL;
            $contactUrlPath = $helper::CONTACT_URL;     //created for Contact

            if ($salesforceId) {
                $urlPath       .=  "/" .$salesforceId;
                $requestMethod  = "PATCH";
            } else{
                $requestMethod  = "POST";
            }

            if ($salesforceContactId) {
                $contactUrlPath.=  "/" .$salesforceContactId;
                $requestMethodContact = "PATCH";
            } else{
                $requestMethodContact = "POST";
            }

            $prefix = $customer->getPrefix();
            $fName = $customer->getFirstname();
            $mName = $customer->getMiddlename();
            $lName = $customer->getLastname();
            $fullName = "";

            if ($prefix){
                $fullName .= $prefix." ";
            }

            $fullName .= $fName . " ";

            if ($mName) {
                $fullName .= $mName;
            }

            $fullName .= $lName;


            $defaultBillingAddr     = $customer->getDefaultBillingAddress();
            $state       = "";
            $countryName = "";

            if ($defaultBillingAddr) {
                if (!empty($defaultBillingAddr['region_id'])) {
                    $region = Mage::getModel('directory/region')
                        ->load($defaultBillingAddr['region_id']);
                    $state = $region->getName();
                } else {
                    $state = $defaultBillingAddr['region'];
                }

                $bcountryNm = $defaultBillingAddr['country_id'];

                if (!empty($bcountryNm)) {
                    if (strlen($bcountryNm) > 3) {
                        $countryName = $bcountryNm;
                    } else {
                        $country = Mage::getModel('directory/country')
                            ->loadByCode($defaultBillingAddr['country_id']);
                        if($country->getId()){
                            $countryName = $country->getName();
                        }
                    }
                }
            }

            $stateShip       = "";
            $countryNameShip = "";
            $defaultShippingAddr    = $customer->getDefaultShippingAddress();

            if ($defaultShippingAddr) {
                if (!empty($defaultBillingAddr['region_id'])) {
                    $region = Mage::getModel('directory/region')
                        ->load($defaultShippingAddr['region_id']);
                    $stateShip = $region->getName();
                } else {
                    $stateShip = $defaultShippingAddr['region'];
                }

                $scountryNm = $defaultShippingAddr['country_id'];

                if (!empty($scountryNm)) {
                    if(strlen($scountryNm) > 3){
                        $countryNameShip = $scountryNm;
                    }else{
                        $country = Mage::getModel('directory/country')
                            ->loadByCode($defaultShippingAddr['country_id']);
                        if($country->getId()){
                            $countryNameShip = $country->getName();
                        }
                    }
                }
            }

            $request = array(
                "Name"                => $fullName,
                //"AccountNumber"       => "",
                //"Site"                => "",
                //"AccountSource"       => "",
                //"Birth_Date_c"        => ($customer->getDob()) ? date("Y-m-d",strtotime($customer->getDob())) : null,//"YYYY-MM-DD",
                "Company__c"          => $customer->getCompany(),
                "Counterpoint_No__c"  => $customer->getCounterpointCustNo(),
                "Created_In__c"       => $customer->getCreatedIn(),
                "Customer_ID__c"      => $customer->getId(),
                "Customer_Note__c"    => $customer->getCustomerNote(),
                "Default_Billing__c"  => $customer->getDefaultBilling(),
                "Default_Shipping__c" => $customer->getDefaultShipping(),
                //"Description"         => "",
                "Email__c"            => $customer->getEmail(),
                //"Fax"                 => "",
                "Gender__c"           => ($customer->getGender()) ? $customer->getGender() : 4,
                "Phone"               => ($defaultBillingAddr) ? $defaultBillingAddr->getTelephone() : "",
                "Store__c"            => $oldStoreArr[$customer->getStoreId()],
                "Old_Store__c"          => $oldStoreArr[$customer->getOldStoreId()],
                "Teamwork_Customer_ID__c"   => $customer->getTeamworkCustomerId(),
                "TW_UC_GUID__c"             => $customer->getTwUcGuid(),
                "Group__c"            => $customer->getGroupId(),
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

            if ($customer->getDob()) {
                $request["Birth_Date__c"] =  date("Y-m-d",strtotime($customer->getDob()));
            }

            $contactRequest = array(
                "FirstName"           => $fName,
                "MiddleName"          => $mName,
                "LastName"            => $lName,
                "Email"               => $customer->getEmail(),
                "Phone"               => ($defaultBillingAddr) ? $defaultBillingAddr->getTelephone() : "",
                "MailingStreet"       => ($defaultBillingAddr) ? implode(", ", $defaultBillingAddr->getStreet()) : "",
                "MailingCity"         => ($defaultBillingAddr) ? $defaultBillingAddr->getCity() : "",
                "MailingState"        => ($defaultBillingAddr) ? $state : "",
                "MailingPostalCode"   => ($defaultBillingAddr) ? $defaultBillingAddr->getPostcode() : "",
                "MailingCountry"      => ($defaultBillingAddr) ? $countryName : "",
                "Contact_Id__c"       => $customer->getId(),
                "AccountID"           => ""
            );

            //tmwork fields accept marketing
            if ($customer->getTwAcceptMarketing()) {
                $request["Accept_Marketing__c"] = $customer->getTwAcceptMarketing();
            }
            //tmwork fields accept transactional
            if ($customer->getTwAcceptTransactional()) {
                $request["Accept_Transactional__c"] = $customer->getTwAcceptTransactional();
            }

            $helper->salesforceLog("----- customer data -----");
            $helper->salesforceLog($request);

            $response    = $helper->sendRequest($urlPath , $requestMethod , $request);
            $this->processCustomer($customer,$objectType,$sFieldName,$requestMethod,$response);
            $responseArr = json_decode($response,true);

            if ($responseArr["success"]) {
                $helper->salesforceLog("----- contact data -----");
                $helper->salesforceLog($contactRequest);

                $contactRequest['AccountID'] = $responseArr["id"];

                $contactResponse    = $helper->sendRequest($contactUrlPath , $requestMethodContact , $contactRequest);
                $this->processCustomer($customer,$objectTypeC,$sCFieldName,$requestMethodContact,$contactResponse);
                return $responseArr["id"];
            }

            if(!empty($salesforceContactId)){
                $helper->salesforceLog("----- contact data update -----");
                $helper->salesforceLog($contactRequest);

                $contactRequest['AccountID'] = $salesforceId;

                $contactResponse    = $helper->sendRequest($contactUrlPath , $requestMethodContact , $contactRequest);
                $this->processCustomer($customer,$objectTypeC,$sCFieldName,$requestMethodContact,$contactResponse);
            }
            return "";
        }
    }


    /**
     * after new customer add or update customer info send data to salesforce
     */
    public function changeCustomerToSalesforce($observer){
        $helper         = $this->getHelper();
        $helper->salesforceLog("changeCustomerToSalesforce request");

        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if(!$isEnable){
            $helper->salesforceLog("Salesforce Plugin Disabled.");
            return;
        }
        $customer = $observer->getEvent()->getCustomer();;
        $this->addCustomerToSalesforce($customer);
    }

    /**
     * when delete magento customer then delete from salesforce also
     */
    public function deleteCustomerToSalesforce($observer){
        $helper         = $this->getHelper();
        $helper->salesforceLog("deleteCustomerToSalesforce request");

        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if(!$isEnable){
            $helper->salesforceLog("Salesforce Plugin Disabled.");
            return;
        }

        $customer = $observer->getEvent()->getCustomer();
        if($customer){
            $salesforceId = $customer->getSalesforceCustomerId();
            if($salesforceId){
                $objectType     = $helper::ACCOUNT_OBJECT;
                $requestMethod  = "DELETE";
                $urlPath        = $helper::ACCOUNT_URL . "/" .$salesforceId;
                $response = $helper->sendRequest($urlPath , $requestMethod , null);
                $this->processCustomer($customer, $objectType, null, $requestMethod, $response);
                if($response == "")
                    $helper->salesforceLog("delete customer from salesforce");
            }
        }
    }


    /**
     * ----- customer address -----
     * send customer address info into salesforce
     */
    public function changeCustomerAddressToSalesforce($observer){
        $helper         = $this->getHelper();
        $helper->salesforceLog("changeCustomerAddressToSalesforce request.");

        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if(!$isEnable){
            $helper->salesforceLog("Salesforce Plugin Disabled.");
            return;
        }

        $customerAddress = $observer->getCustomerAddress();
        $helper->salesforceLog("customer id - ".$customerAddress->getCustomerId()." , address id - ".$customerAddress->getId());

        if(Mage::registry('customer_address_'.$customerAddress->getCustomerId())){
            return $this;
        }
        Mage::register('customer_address_'.$customerAddress->getCustomerId(),true);

        if($customerAddress){
            $customerAddressObj = $customerAddress;
            $customerAddress = $customerAddress->getData();
            $salesforceId   = $customerAddress['salesforce_address_id'];

            $objectType     = $helper::ADDRESS_OBJECT;
            $sFieldName     = $helper::S_ADDRESSID;

            $requestMethod  = "GET";
            $urlPath        = $helper::ADDRESS_URL;
            if($salesforceId){
                $urlPath .= "/" . $salesforceId;
                $requestMethod  = "PATCH";
            }else{
                $requestMethod  = "POST";
            }

            $state = "";
            if($customerAddress['region_id']){
                $region = Mage::getModel('directory/region')
                    ->load($customerAddress['region_id']);
                $state = $region->getName();
            }else{
                $state = $customerAddress['region'];
            }

            $country = Mage::getModel('directory/country')
                ->loadByCode($customerAddress['country_id']);

            $countryName = $country->getName();

            $request = array(
                "City__c"           => $customerAddress['city'],
                //"Country__c"        => $countryName,
                "Customer_ID__c"    => $customerAddress['customer_id'],
                "Fax__c"            => $customerAddress['fax'],
                "Increment_Id__c"   => $customerAddress['increment_id'],
                "Postal_Code__c"    => $customerAddress['postcode'],
                //"Region__c"         => $state,
                "Street__c"         => $customerAddress['street'],
                "Telephone__c"      => $customerAddress['telephone'],
                "VAT_Id__c"         => $customerAddress['vat_id']
            );
            $helper->salesforceLog("----- address data -----");
            $helper->salesforceLog($request);
            $response    = $helper->sendRequest($urlPath , $requestMethod , $request);
            /* $responseArr = json_decode($response,true);
            if($responseArr["success"]){
                $customerAddressObj->setData($sFieldName, $responseArr["id"]);
                $customerAddressObj->getResource()->saveAttribute($customerAddressObj, $sFieldName);
            } */
            $this->processCustomer($customerAddressObj,$objectType,$sFieldName,$requestMethod,$response);
        }
    }
}
