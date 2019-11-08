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
        $helper->salesforceLog($responseArr);
        $isFailure = false;
        if($responseArr["success"]){
            try{
//                $helper->salesforceLog('Set data on '.$fieldName. '  for objectType'.$objectType.' resMethod'.$requestMethod);
                $object->setData($fieldName, $responseArr["id"]);
                $object->getResource()->saveAttribute($object, $fieldName);
                $helper->salesforceLog("Saved Salesforce ID succesfully for Object ".$objectType. " With ID -".$object->getId());
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

        if ($customer) {
            $helper->salesforceLog("In Customer Observer -addCustomerToSalesforce ".$customer->getId());
            $objectType     = $helper::ACCOUNT_OBJECT;
            $objectTypeC    = $helper::CONTACT_OBJECT;   //created for Contact
            $sFieldName     = $helper::S_CUSTOMERID;
            $sCFieldName    = $helper::S_CONTACTID;      //created for Contact

            $salesforceId   = $customer->getSalesforceCustomerId();
            $salesforceContactId   = $customer->getSalesforceContactId();
            $IsTeamworkCustomer = $customer->getIsTeamworkCustomer();

            if($IsTeamworkCustomer){
                $helper->salesforceLog("Return from Customer Event - Teamwork customer -".$customer->getId());
                return;
            }

            $requestMethod  = "POST";
            $requestMethodContact  = "POST";
            $urlPath        = $helper::ACCOUNT_URL;
            $contactUrlPath = $helper::CONTACT_URL;     //created for Contact

            if(!empty($salesforceId ) && !empty($salesforceContactId)){
                $helper->salesforceLog("Tried to create Customer and Contact but they are already present in SF :-".$customer->getId());
                return;
            }

            $requestData = $helper->getCustomerRequestData($customer,true,true);
            $accountRequest = $requestData["customer"];
            $contactRequest = $requestData["contact"];

            $helper->salesforceLog("----- customer data -----");
            $helper->salesforceLog($accountRequest);

            if(empty($salesforceId)){
                $requestMethod  = "POST";
                $response    = $helper->sendRequest($urlPath , $requestMethod , $accountRequest);
            }
            else{
                $helper->salesforceLog("Customer already present in SF :-".$customer->getId());
                return;
            }

            $this->processCustomer($customer,$objectType,$sFieldName,$requestMethod,$response);
            $responseArr = json_decode($response,true);

            if ($responseArr["success"]) {
                $helper->salesforceLog("----- contact data -----");
                $helper->salesforceLog($contactRequest);

                $contactRequest['AccountID'] = $responseArr["id"];

                if(empty($salesforceContactId)){
                    $contactResponse    = $helper->sendRequest($contactUrlPath , $requestMethodContact , $contactRequest);
                    $this->processCustomer($customer,$objectTypeC,$sCFieldName,$requestMethodContact,$contactResponse);
                    return $responseArr["id"];
                }
                else{
                    $helper->salesforceLog("Contact already present in SF :-".$customer->getId());
                    return;
                }
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
