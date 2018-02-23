<?php
/**
 * @author allure
 */
class Allure_Counterpoint_Model_Observer{	
    
    protected $_crm_log_file  = "crm_customer";
    protected $_is_enable_log = false;
    
    public function __construct(){
        $this->_is_enable_log = $this->isSugarCrmDebugLog();
    }
    
    private function isSugarCrmDebugLog(){
        $mainHelper = Mage::helper('allure_counterpoint');
        return $mainHelper->getSugarCRMDebugLogStatus();
    }
    
    private function getSugarCrmStatus(){
        $mainHelper = Mage::helper('allure_counterpoint');
        return $mainHelper->getSugarCRMStatus();
    }
    
    /** 
     * Add new customer or Update customer -
     * information into SugarCrm using api call.
     * @param Varien_Event_Observer $observer
     */
    public function sendCustomerInfoToSugarcrm($observer){
        if(!($this->getSugarCrmStatus()))
            return ;
        
        Mage::log("In addNewCustomerToCRM method",Zend_log::DEBUG,
                        $this->_crm_log_file,$this->_is_enable_log);
	    $event = $observer->getEvent();
	    $customer = $event->getCustomer();
	    Mage::log(json_encode($customer->getData()),Zend_log::DEBUG,
	                   $this->_crm_log_file,$this->_is_enable_log);
	    $fullName = $customer->getFirstname()." ".$customer->getLastname();
	    $email = $customer->getEmail();
	    $requestParams = array(
	        "first_name"=>$customer->getFirstname(),
	        "last_name"=>$customer->getLastname(),
	        'full_name'=>$fullName,
	        "email"=>array(array("email_address"=>$email,
	                             "primary_address"=>true
	                       )
	                 ),
	        "email1"=>$email,
	        'customer_name_c'=>$fullName
	    );
	    try{
	        $this->sendCustomerInfoToSugarcrmPost($requestParams,$email);
	    }catch (Exception $e){
	        Mage::log("Exception In sendCustomerInfoToCRM -",Zend_log::DEBUG,
	            $this->_crm_log_file,$this->_is_enable_log);
	        Mage::log("Exception-".$e->getMessage(),Zend_log::DEBUG,
	            $this->_crm_log_file,$this->_is_enable_log);
	    }
    }
    
    /**
     * @param array $requestParams
     * @param string $email
     */
    private function sendCustomerInfoToSugarcrmPost($requestParams,$email){
        $mainHelper = Mage::helper('allure_counterpoint');
        $helper = Mage::helper('allure_counterpoint/sugarcrmClient');
        $urlPath = $mainHelper::ADD_CUSTOMER_PATH;
        $requestURL = $helper->generateUrl($urlPath);
        $customerEmail = $email;
        if(!empty($email)){
            $oauthToken = $helper->login();
            $responseObj = $this->checkCustomerEmailInSugarcrm($oauthToken,$customerEmail);
            
            $isPresent = false;
            $sugarCrmId = '';
            if(array_key_exists('records',$responseObj)){
                if(count($responseObj['records']) > 0){
                    $isPresent = true;
                    $sugarCrmId = $responseObj['records'][0]['id'];
                }
            }
            
            $msgText = ($isPresent)?" Present":" Not Present";
            Mage::log("Email=:".$customerEmail . $msgText ." into SugarCrm.",
                        Zend_log::DEBUG,$this->_crm_log_file,$this->_is_enable_log);
            $requestType = "POST";
            if($isPresent){
                Mage::log("Sugar CRM Customer ID:-".$sugarCrmId,Zend_log::DEBUG,
                    $this->_crm_log_file,$this->_is_enable_log);
                $requestType = "PUT";
                $requestURL = $requestURL."/".$sugarCrmId;
            }
            $response = $helper->sendRequest($requestURL,$requestParams,true,
                        $oauthToken,$requestType);
            $responseObj = json_decode($response,true);
            
            if(!empty($responseObj['id'])){
                    Mage::log("Customer Information saved successfully.",Zend_log::DEBUG,
                            $this->_crm_log_file,$this->_is_enable_log);
            }else{
                Mage::log($response,Zend_log::DEBUG,
                    $this->_crm_log_file,$this->_is_enable_log);
            }
        }
    }
    
    /**
     * Send customer address information into sugarcrm-
     * when customer address is updated or created.
     * @param Varien_Event_Observer $observer
     */
    public function sendCustomerAddressInfoToSugarcrm($observer){
        if(!($this->getSugarCrmStatus()))
            return ;
        
        $customerAddress = $observer->getCustomerAddress()->getData();
        Mage::log("In sendCustomerAddressInfoToSugarcrm method",Zend_log::DEBUG,
            $this->_crm_log_file,$this->_is_enable_log);
        Mage::log(json_encode($customerAddress),Zend_log::DEBUG,
            $this->_crm_log_file,$this->_is_enable_log);
        if(array_key_exists('is_default_billing', $customerAddress)){
            $customerId = $customerAddress['customer_id'];
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $email = $customer->getEmail();
            
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
            
            $requestParams = array(
                "primary_address_street"     =>$customerAddress['street'],
                "primary_address_city"       =>$customerAddress['city'],
                "primary_address_state"      =>$state,
                "primary_address_postalcode" =>$customerAddress['postcode'],
                "primary_address_country"    =>$countryName,
                "phone_mobile"               =>$customerAddress['telephone']
            );
            $this->sendCustomerAddressInfoToSugarcrmPost($requestParams,$email);
        }
    }
	
    private function sendCustomerAddressInfoToSugarcrmPost($requestParams,$email){
        try{
            $this->sendCustomerInfoToSugarcrmPost($requestParams,$email);
        }catch (Exception $e){
            Mage::log("Exception In sendCustomerAddressInfoToSugarcrmPost -",Zend_log::DEBUG,
                $this->_crm_log_file,$this->_is_enable_log);
            Mage::log("Exception-".$e->getMessage(),Zend_log::DEBUG,
                $this->_crm_log_file,$this->_is_enable_log);
        }
    }
    
    /**
     * @param string $oauthToken
     * @param string $email
     * @return array object
     * Check into SugarCrm the email id present of any customer's
     */
    private function checkCustomerEmailInSugarcrm($oauthToken,$email){
	    Mage::log("Check customer email in SugarCrm.Email=:".$email,Zend_log::DEBUG,
	           $this->_crm_log_file,$this->_is_enable_log);
	    $mainHelper = Mage::helper('allure_counterpoint');
	    $helper = Mage::helper('allure_counterpoint/sugarcrmClient');
	    $urlPath = $mainHelper::CUSTOMER_SEARCH_PATH;
	    $requestURL = $helper->generateUrl($urlPath);
	    $filterParams = '{"filter":[{"email":{"$equals":"'.$email.'"}}]}';
	    $filterParams = json_decode($filterParams,true);
	    $response = $helper->sendRequest($requestURL,$filterParams,true,$oauthToken,"GET");
	    Mage::log("Response:-".$response,Zend_log::DEBUG,$this->_crm_log_file,
	        $this->_is_enable_log);
	    $responseObj = json_decode($response,true);
	    return $responseObj;
	}
	    
}
