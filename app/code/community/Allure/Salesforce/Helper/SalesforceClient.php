<?php
/**
 * Allure-Salesforce Integration
 * @author aws02
 *
 */
class Allure_Salesforce_Helper_SalesforceClient extends Mage_Core_Helper_Abstract{
    
    protected $_salesforce_log_file = "salesforce.log";
    protected $_retry_limit         = 1;
    protected $_retry_count         = 0;
    
    //used for generating the new tokens
    const OAUTH_URL                         = "/services/oauth2/token?"; 
    //used for product
    const PRODUCT_URL                       = "/services/data/v42.0/sobjects/Product2";
    //used for add price to product in salesforce
    const PRODUCT_PRICEBOOK_URL             = "/services/data/v42.0/composite/tree/PricebookEntry";
    //used for update product price book
    const PRODUCT_UPDATE_PRICEBK_URL        = "/services/data/v42.0/composite/sobjects";
    //used for customer
    const ACCOUNT_URL                       = "/services/data/v42.0/sobjects/account";
    //used for contact
    const CONTACT_URL                       = "/services/data/v42.0/sobjects/contact";
    //used for customer address
    const ADDRESS_URL                       = "/services/data/v42.0/sobjects/Address__c";
    //used for order
    const ORDER_URL                         = "/services/data/v42.0/sobjects/Order";
    //used to place order
    const ORDER_PLACE_URL                   = "/services/data/v30.0/commerce/sale/order";
    //used for order invoice
    const INVOICE_URL                       = "/services/data/v42.0/sobjects/Invoice__c";
    //used for order refuned product
    const CREDIT_MEMO_URL                   = "/services/data/v42.0/sobjects/Credit_Memo__c";
    //used for order shipment
    const SHIPMENT_URL                      = "/services/data/v42.0/sobjects/Shipment__c"; 
    //used to update composite objects
    const UPDATE_COMPOSITE_OBJECT_URL       = "/services/data/v42.0/composite/sobjects";
    //used to shipment tracking
    const SHIPMENT_TRACK_URL                = "/services/data/v42.0/composite/tree/Tracking_Information__c";
    //shipment track url for delete
    const SHIPMENT_TRACK_URL_1              = "/services/data/v42.0/sobjects/Tracking_Information__c";
    //invoice pdf attachement
    const INVOICE_PDF_URL_UPLOAD            = "/services/data/v37.0/sobjects/Attachment";   
    //invoice pdf upload using contentversion object
    const CONTENTVERSION_URL                = "/services/data/v43.0/sobjects/ContentVersion";
    //invoice pdf link using document object
    const DOCUMENTLINK_URL                  = "/services/data/v43.0/sobjects/ContentDocumentLink";
    
    //Salesforce object's type
    const PRODUCT_OBJECT            = "PRODUCT";
    const PRODUCT_PRICEBOOK_OBJECT  = "PRODUCT_PRICE_BOOK";
    const ACCOUNT_OBJECT            = "CUSTOMER";
    const CONTACT_OBJECT            = "CONTACT";
    const ADDRESS_OBJECT            = "ADDRESS";
    const ORDER_OBJECT              = "ORDER";
    const INVOICE_OBJECT            = "INVOICE";
    const CREDITMEMO_OBJECT         = "CREDITMEMO";
    const SHIPMENT_OBJECT           = "SHIPMENT";
    const UPLOAD_DOC_OBJECT         = "UPLOAD_INVOICE_PDF";
    
    //salesforce magento mapping field
    const S_PRODUCTID           = "salesforce_product_id";
    const S_CUSTOMERID          = "salesforce_customer_id";
    const S_CONTACTID          = "salesforce_contact_id";
    const S_ADDRESSID           = "salesforce_address_id";
    const S_ORDERID             = "salesforce_order_id";
    const S_INVOICEID           = "salseforce_invoice_id";
    const S_CREDITMEMO          = "salesforce_creditmemo_id";
    const S_STANDARD_PRICEBK    = "salesforce_standard_pricebk";
    const S_WHOLESALE_PRICEBK   = "salesforce_wholesale_pricebk";
    
    //product price-book id for retailer & wholesaller in salesforce
    const RETAILER_PRICEBOOK_ID     = "01s6A000006NMtlQAG"; //standard pricebook
    const WHOLESELLER_PRICEBOOK_ID  = "01s290000001ivyAAA"; //wholeseller pricebook
    
    const GUEST_CUSTOMER_ACCOUNT    = "0012900000Ls44hAAB";
    
    /**
     * keep track of salesforce log data
     */
    public function salesforceLog($logData){
        $logFile = "salesforce_".date("Y_m_d").".log";
        Mage::log($logData,Zend_Log::DEBUG,$logFile,true);
    }
    
    /**
     * keep track of salesforce access token 
     * & instance url info
     * @param - sOauthToken & sInstanceUrl
     */
    public function getSalesforceSession(){
        return Mage::getSingleton("core/session");
    }
    
    /**
     * @return Allure_Salesforce_Helper_Data
     */
    public function getDataHelper(){
        return Mage::helper('allure_salesforce');
    }
    
    /**
     * @return string - generate new url 
     */
    public function getUrl($path){
        $helper = $this->getDataHelper();
        $url = $helper->getHost() . "" . $path;
        return  $url;
    }
    
    
    /**
     * generate new access token 
     * @return array
     */
    public function refreshToken(){
        $helper         = $this->getDataHelper();
        $grantType      = $helper->getGrantType();
        $clientId       = $helper->getClientId();
        $clientSecret   = $helper->getClientSecret();
        $username       = $helper->getUsername();
        $password       = $helper->getPassword();
        
        $tokenUrl = $this->getUrl(self::OAUTH_URL);
        $tokenUrl = $tokenUrl."grant_type={$grantType}&client_id={$clientId}&".
            "client_secret={$clientSecret}&username={$username}&password={$password}" ;
        
        $this->salesforceLog("In refreshToken method of salesforceClient class.");
        $this->salesforceLog($tokenUrl);
        
        $tokenRequest = curl_init($tokenUrl);
        curl_setopt($tokenRequest, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($tokenRequest, CURLOPT_HEADER, false);
        curl_setopt($tokenRequest, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($tokenRequest, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($tokenRequest, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($tokenRequest, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($tokenRequest, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json"
        ));
        
        // execute $tokenRequest
        $tokenResponse      = curl_exec($tokenRequest);
        $tokenResponseArr   = json_decode($tokenResponse, true);
        $this->salesforceLog($tokenResponse);
        if($tokenResponseArr["access_token"]){ //successfully generate access token
            $this->getSalesforceSession()->setSOauthToken($tokenResponseArr["access_token"]);
            $this->getSalesforceSession()->setSInstanceUrl($tokenResponseArr["instance_url"]);
        }else{ //error - access token not generated
            $this->getSalesforceSession()->setSOauthToken(null);
            $this->getSalesforceSession()->setSInstanceUrl(null);
        }
        return $tokenResponseArr;;
    }
    
    /**
     * make api request call to salesforce through curl
     * @param - urlPath - contains which api object call
     * @param - requestMethod - contains GET|POST|DELETE|PUT|PATCH|OPTIONS|HEAD
     * @param - requestArgs - contains input parameters of request
     */
    public function sendRequest($urlPath, $requestMethod = "GET", $requestArgs, $multipart = false,$boundary = null){
        $salesfoeceSession = $this->getSalesforceSession();
        $oauthToken = $salesfoeceSession->getSOauthToken();
        $instaceUrl = $salesfoeceSession->getSInstanceUrl();
        if(!$oauthToken || !$instaceUrl){
            $responseArr = $this->refreshToken();
            if($responseArr["access_token"]){
                $oauthToken = $responseArr["access_token"];
                $instaceUrl = $responseArr["instance_url"];
            }
        }
        
        if($oauthToken && $instaceUrl){
            $requestURL = $instaceUrl . $urlPath;
            $sendRequest = curl_init($requestURL);
            curl_setopt($sendRequest, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
            curl_setopt($sendRequest, CURLOPT_HEADER, false);
            curl_setopt($sendRequest, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($sendRequest, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($sendRequest, CURLOPT_CUSTOMREQUEST, $requestMethod);
            curl_setopt($sendRequest, CURLOPT_FOLLOWLOCATION, 0);
            
            if($multipart){
                $requestArgs = implode("\r\n", $requestArgs);
                curl_setopt($sendRequest, CURLOPT_HTTPHEADER, array(
                    "Content-Type: multipart/form-data; boundary={$boundary}",
                    "Authorization: Bearer {$oauthToken}",
                    "Content-Length: " . strlen($requestArgs)
                ));
                
                if ($requestArgs != null) {
                    curl_setopt($sendRequest, CURLOPT_POSTFIELDS, $requestArgs);
                }
            }else{
                curl_setopt($sendRequest, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json",
                    "Authorization: Bearer {$oauthToken}"
                ));
                
                // convert requestArgs to json
                if ($requestArgs != null) {
                    $json_arguments = json_encode($requestArgs);
                    curl_setopt($sendRequest, CURLOPT_POSTFIELDS, $json_arguments);
                }
            }
            
            // execute sendRequest
            $response       = curl_exec($sendRequest);
            $responseArr    = json_decode($response,true);
            //$this->salesforceLog("count = ".$this->_retry_count);
            if($responseArr[0]["errorCode"] == "INVALID_SESSION_ID"){
                $this->salesforceLog("retry count is = ".$this->_retry_count);
                if($this->_retry_count < $this->_retry_limit){
                    $salesfoeceSession->setSOauthToken(null);
                    $salesfoeceSession->setSInstanceUrl(null);
                    return $this->sendRequest($urlPath,$requestMethod,$requestArgs);
                }
                $this->_retry_count ++;
            }
            $this->salesforceLog($requestURL);
            return $response;
        }
        return json_encode(array("success" =>false,"message"=>"Unkwon error."));
    }
    
    /**
     * get collection of salesforce log record using object type & request method & magento id
     */
    public function getSalesforceLogCollection($objectType ,$requestMethod ,$magentoId ){
        $collection = Mage::getModel("allure_salesforce/salesforcelog")->getCollection()
        ->addFieldToFilter("object_type" ,  $objectType)
        ->addFieldToFilter("operation_name" ,$requestMethod)
        ->addFieldToFilter("magento_id" , $magentoId);
        return $collection;
    }
    
    /**
     * insert record into allure_salesforce_log table
     */
    public function addSalesforcelogRecord($objectType ,$requestMethod ,$magentoId,$response){
        try{
            $collection = $this->getSalesforceLogCollection($objectType,$requestMethod,$magentoId);
            $salesforceLog = Mage::getModel("allure_salesforce/salesforcelog");
            if($collection->getSize()){
                $salesforceLog = $collection->getFirstItem();
            }
            $salesforceLog->setObjectType($objectType)
                ->setOperationName($requestMethod)
                ->setMagentoId($magentoId)
                ->setResponse($response)
                ->save();
        }catch (Exception $e){
            $this->salesforceLog("Exception in ".get_class($this)." Class of Method addSalesforcelogRecord.");
            $this->salesforceLog("Message :".$e->getMessage());
        }
    }
    
    /**
     * delete salesforce log record from allure_salesforce_log table
     * after successfull updation or insertion of record
     */
    public function deleteSalesforcelogRecord($objectType ,$requestMethod ,$magentoId){
        try{
            $collection = $this->getSalesforceLogCollection($objectType,$requestMethod,$magentoId);
            $salesforceLog = Mage::getModel("allure_salesforce/salesforcelog");
            if($collection->getSize()){
                $salesforceLog = $collection->getFirstItem();
                $salesforceLog->delete();
            }
        }catch (Exception $e){
            $this->salesforceLog("Exception in ".get_class($this)." Class of Method deleteSalesforcelogRecord.");
            $this->salesforceLog("Message :".$e->getMessage());
        }
    }
    
    /**
     * process salesforce api response
     */
    public function processResponse($object , $objectType , $fieldName , $requestMethod , $response){
        $responseArr = json_decode($response,true);
        if($responseArr["success"]){
            try{
                $object->setData($fieldName , $responseArr["id"])->save();
                $this->deleteSalesforcelogRecord($objectType , $requestMethod , $object->getId());
            }catch (Exception $e){
                $this->salesforceLog("Exception in " . get_class($this) . " Class of Method processResponse.");
                $this->salesforceLog("When processing of " . $objectType . " object.");
                $this->salesforceLog("Message :" . $e->getMessage());
                $isFailure = true;
            }
        }else{
            if($responseArr[0]["errorCode"]){
                $isFailure = true;
            }else{
                $this->deleteSalesforcelogRecord($objectType , $requestMethod , $object->getId());
            }
        }
        
        if($isFailure){
            $this->addSalesforcelogRecord($objectType , $requestMethod , $object->getId(),$response);
        }
    }
}
