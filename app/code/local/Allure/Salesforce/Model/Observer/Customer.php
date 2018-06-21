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
     * after new customer add or update customer info send data to salesforce
     */
    public function changeCustomerToSalesforce($observer){
        $customer = $observer->getEvent()->getCustomer();
        if($customer){
            $helper         = $this->getHelper();
            $objectType     = $helper::ACCOUNT_OBJECT;
            $sFieldName     = $helper::S_CUSTOMERID;
            
            $salesforceId   = $customer->getSalesforceCustomerId();
            $requestMethod  = "GET";
            $urlPath        = $helper::ACCOUNT_URL;
            if($salesforceId){
                $urlPath       .=  "/" .$salesforceId;
                $requestMethod  = "PATCH";
            }else{
                $requestMethod  = "POST";
            }
            
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
            
            $request = array(
                "Name"                => $fullName,
                //"AccountNumber"       => "",
                //"Site"                => "",
                //"AccountSource"       => "",
                //"Birth_Date_c"        => ($customer->getDob()) ? date("Y-m-d",strtotime($customer->getDob())) : null,//"YYYY-MM-DD",
                "Company__c"          => $customer->getCompany(),
                "Counterpoint_No__c"  => $customer->getCounterpointCustNo(),
                "Created_In__c"       => "",
                "Customer_ID__c"      => $customer->getId(),
                "Customer_Note__c"    => $customer->getCustomerNote(),
                "Default_Billing__c"  => $customer->getDefaultBilling(),
                "Default_Shipping__c" => $customer->getDefaultShipping(),
                //"Description"         => "",
                "Email__c"            => $customer->getEmail(),
                //"Fax"                 => "",
                //"Gender__c"           => $customer->getGender(),
                "Phone"               => ($defaultBillingAddr) ? $defaultBillingAddr->getTelephone() : "",
                "Store__c"            => $customer->getStoreId(),
                "Group__c"            => "General",//$customer->getGroupId(),
                "BillingStreet"       => ($defaultBillingAddr) ? implode(", ", $defaultBillingAddr->getStreet()) : "",
                "BillingCity"         => ($defaultBillingAddr) ? $defaultBillingAddr->getCity() : "",
                "BillingState"        => ($defaultBillingAddr) ? $state : "",
                "BillingPostalCode"   => ($defaultBillingAddr) ? $defaultBillingAddr->getPostcode() : "",
                "BillingCountry"      => ($defaultBillingAddr) ? $countryName : "",
                "ShippingStreet"      => ($defaultShippingAddr) ? $defaultShippingAddr->getStreet() : "",
                "ShippingCity"        => ($defaultShippingAddr) ? $defaultShippingAddr->getCity() : "",
                "ShippingState"       => ($defaultShippingAddr) ? $stateShip : "",
                "ShippingPostalCode"  => ($defaultShippingAddr) ? $defaultShippingAddr->getPostcode() : "",
                "ShippingCountry"     => ($defaultShippingAddr) ? $countryNameShip : ""
            );
            
            if($customer->getDob()){
                $request["Birth_Date__c"] =  date("Y-m-d",strtotime($customer->getDob()));
            }
            
            Mage::log("----- customer data -----",Zend_Log::DEBUG,'abc.log',true);
            //Mage::log($request,Zend_Log::DEBUG,'abc.log',true);
            
            $response    = $helper->sendRequest($urlPath , $requestMethod , $request);
            Mage::log($response,Zend_Log::DEBUG,'abc.log',true);
            $helper->processResponse($customer,$objectType,$sFieldName,$requestMethod,$response);
        }
    }
    
    /**
     * when delete magento csutomer then delete from salesforce also
     */
    public function deleteCustomerToSalesforce($observer){
        $customer = $observer->getEvent()->getCustomer();
        if($customer){
            $salesforceId = $customer->getSalesforceCustomerId();
            if($salesforceId){
                $helper         = $this->getHelper();
                $requestMethod  = "DELETE";
                $urlPath        = $helper::ACCOUNT_URL . "/" .$salesforceId;
            }
        }
    }
    
    
    /**
     * ----- customer address -----
     * send customer address info into salesforce
     */
    public function changeCustomerAddressToSalesforce($observer){
        $customerAddress = $observer->getCustomerAddress();
        if($customerAddress){
            $customerAddressObj = $customerAddress;
            $customerAddress = $customerAddress->getData();
            $salesforceId   = $customerAddress['salesforce_address_id'];
            $helper         = $this->getHelper();
            
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
            Mage::log("----- address data -----",Zend_Log::DEBUG,'abc.log',true);
            Mage::log($request,Zend_Log::DEBUG,'abc.log',true);
            $response    = $helper->sendRequest($urlPath , $requestMethod , $request);
            Mage::log($response,Zend_Log::DEBUG,'abc.log',true);
            $helper->processResponse($customerAddressObj,$objectType,$sFieldName,$requestMethod,$response);
        }
    }
}
