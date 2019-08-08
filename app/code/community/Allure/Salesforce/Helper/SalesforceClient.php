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
    //used for creating nested Product including PriceBookEntry
    const PRODUCT_COMPOSITE_TREE_URL        = "/services/data/v42.0/composite/tree/Product2";
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
    public function salesforceLog($logData, $isBulk = false)
    {
        $logFileObserver = "salesforce_".date("Y_m_d").".log";
        $logFileUpdate = "salesforce_update".date("Y_m_d").".log";

        Mage::log($logData,Zend_Log::DEBUG,$isBulk?$logFileUpdate:$logFileObserver,true);
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
                    $this->salesforceLog("sendRequest Data -".$json_arguments);
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

    public function getOrderRequestData($order, $create)
    {
        $orderItemList = array();
        $salesforceOrderId = $order->getSalesforceOrderId();

        if($create && !empty($salesforceOrderId)){
            $this->salesforceLog("Tried to create Order - ".$order->getId().". But Order already Present in SF -".$salesforceOrderId);
            return;
        }

        if(!$create && empty($salesforceOrderId)){
            $this->salesforceLog("Tried to UPDATE Order - ".$order->getId().". But Order Not Present in SF -".$salesforceOrderId);
            return;
        }
        
        $orderId = $order->getId();
        $orderStatus = $order->getStatus();
        $this->salesforceLog($create?"CREATE:":"UPDATE"."Order Id {$orderId} Status - " . $orderStatus);

        $items = $order->getAllVisibleItems();

        //check product is in salesforce or not.if not add into salesforce.
        $isTeamwork = false;
        $createOrderMethod = $order->getCreateOrderMethod();
        if ($createOrderMethod) {
            $isTeamwork = true;
            $status = Mage::helper("allure_teamwork")->getTeamworkSalesforceStatus();
            if (!$status) {
                Mage::log("Teamwork data transfer to salesforce disabled.", Zend_Log::DEBUG, "salesforce.log", true);
                return;
            }
        }
        // #CH1
        if ($create)
            Mage::getModel("allure_salesforce/observer_product")->addOrderProduct($items, $isTeamwork);

        $orderId = $order->getId();
        $status = ($order->getStatus()) ? $order->getStatus() : "pending";
        $customerId = $order->getCustomerId();

        // #CH2
        $salesforceAccountId = $this::GUEST_CUSTOMER_ACCOUNT;
        if ($customerId) {
            $customer = Mage::getModel("customer/customer")->load($customerId);
            $salesforceAccountId = $customer->getSalesforceCustomerId();
            if (!$salesforceAccountId && $create) {
                //$guestAccount = Mage::helper('allure_salesforce')->getGuestAccount();
                //$salesforceAccountId = $guestAccount; //$this::GUEST_CUSTOMER_ACCOUNT;

                //create new account for the customer
                $this->salesforceLog("In Order - account creating.");
                //$customer->save();
                $salesforceAccountId = Mage::getModel("allure_salesforce/observer_customer")
                    ->addCustomerToSalesforce($customer);
            }
            $this->salesforceLog("SalesforceAccount id  - " . $salesforceAccountId);
            /* if(!$salesforceAccountId){
                $customer->save();
                $salesforceAccountId = $customer->getSalesforceCustomerId();
            } */
        }

        $customerEmail = $order->getCustomerEmail();
        $customerGroup = $order->getCustomerGroupId();

        $pricebookId = Mage::helper('allure_salesforce')->getGeneralPricebook(); //$this::RETAILER_PRICEBOOK_ID;
        if ($customerGroup == 2) {
            $pricebookId = Mage::helper('allure_salesforce')->getWholesalePricebook(); //$this::WHOLESELLER_PRICEBOOK_ID;
        }

        $totalQty = $order->getTotalQtyOrdered();

        $totalItemCount = $order->getTotalItemCount();

        $incrementId = $order->getIncrementId();
        $shipingMethod = $order->getShippingMethod();
        $createdAt = $order->getCreatedAt();
        $counterpointOrderId = $order->getCounterpointOrderId();
        $shippingDescription = $order->getShippingDescription();

        //for teamwork currency rate
        $currencyRate = 1;
        if ($order->getCreateOrderMethod() == 2) {
            $currencyRate = $order->getStoreToBaseRate();
            if (!$currencyRate) {
                $currencyRate = 1;
            }
        }

        $subtotal = $order->getSubtotal() * $currencyRate;
        $baseSubtotal = $order->getBaseSubtotal() * $currencyRate;
        //$grandTotal = $order->getGrandTotal() * $currencyRate;
        $grandTotal = $order->getGrandTotal();
        $baseGrandTotal = $order->getBaseGrandTotal() * $currencyRate;
        //$discountAmount = $order->getDiscountAmount() * $currencyRate;
        $discountAmount = $order->getDiscountAmount();
        $baseDiscountAmount = $order->getBaseDiscountAmount() * $currencyRate;
        //$shippingAmount = $order->getShippingAmount() * $currencyRate;
        $shippingAmount = $order->getShippingAmount();
        $baseShippingAmount = $order->getBaseShippingAmount() * $currencyRate;

        //$taxAmount = $order->getTaxAmount() * $currencyRate;
        $taxAmount = $order->getTaxAmount();
        $baseTaxAmount = $order->getBaseTaxAmount() * $currencyRate;

        //$totalPaid = $order->getTotalPaid() * $currencyRate;
        $totalPaid = $order->getTotalPaid();
        $baseTotalPaid = $order->getBaseTotalPaid() * $currencyRate;
        //$totalRefunded = $order->getTotalRefunded() * $currencyRate;
        $totalRefunded = $order->getTotalRefunded();
        $baseTotalRefunded = $order->getBaseTotalRefunded() * $currencyRate;
        //$totalInvoiced = $order->getTotalInvoiced() * $currencyRate;
        $totalInvoiced = $order->getTotalInvoiced();
        $baseTotalInvoiced = $order->getBaseTotalInvoiced() * $currencyRate;

        $baseTotalDue = $order->getBaseTotalDue() * $currencyRate;

        $billingAddr = $order->getBillingAddress();
        $shippingAddr = $order->getShippingAddress();

        $customerNote = Mage::helper('giftmessage/message')->getEscapedGiftMessage($order);

        $paymentMethod = $order->getPayment()->getMethodInstance()->getTitle();

        $state = "";
        $countryName = "";
        if ($billingAddr) {
            if ($billingAddr['region_id']) {
                $region = Mage::getModel('directory/region')
                    ->load($billingAddr['region_id']);
                $state = $region->getName();
            } else {
                $state = $billingAddr['region'];
            }

            $bcountryNm = $billingAddr['country_id'];
            if ($bcountryNm) {
                if (strlen($bcountryNm) > 3) {
                    $countryName = $bcountryNm;
                } else {
                    $country = Mage::getModel('directory/country')
                        ->loadByCode($billingAddr['country_id']);
                    if ($country->getId()) {
                        $countryName = $country->getName();
                    }
                }
            }

        }

        $stateShip = "";
        $countryNameShip = "";
        if ($shippingAddr) {
            if ($shippingAddr['region_id']) {
                $region = Mage::getModel('directory/region')
                    ->load($shippingAddr['region_id']);
                $stateShip = $region->getName();
            } else {
                $stateShip = $shippingAddr['region'];
            }

            $scountryNm = $shippingAddr['country_id'];
            if ($scountryNm) {
                if (strlen($scountryNm) > 3) {
                    $countryNameShip = $scountryNm;
                } else {
                    $country = Mage::getModel('directory/country')
                        ->loadByCode($shippingAddr['country_id']);
                    if ($country->getId()) {
                        $countryNameShip = $country->getName();
                    }
                }
            }
        }

        $createOrderMethod = $order->getCreateOrderMethod();
        $isTeamworkOrder = false;
        if ($createOrderMethod == 2) {
            $isTeamworkOrder = true;
        }

        $orderItem = array();
        $orderItem["records"] = array();

        $magOrderItemArr = array();
        foreach ($items as $item) {

            $salesforcePricebkEntryId = "";
            if ($isTeamwork) {
                $productId = Mage::getModel("catalog/product")->getIdBySku($item->getSku());
                if ($productId) {
                    $product = Mage::getModel("catalog/product")->load($productId);
                    if ($product) {
                        $salesforcePricebkEntryId = $product->getSalesforceStandardPricebk();
                        if ($customerGroup == 2) {
                            $salesforcePricebkEntryId = $product->getSalesforceWholesalePricebk();
                        }
                    }
                } else {
                    $tmProduct = Mage::getModel("allure_teamwork/tmproduct")
                        ->load($item->getSku(), "sku");
                    if ($tmProduct->getId()) {
                        $salesforcePricebkEntryId = $tmProduct->getSalesforceStandardPricebk();
                        if ($customerGroup == 2) {
                            $salesforcePricebkEntryId = $tmProduct->getSalesforceWholesalePricebk();
                        }
                    }
                }
            } else {
                $productId = Mage::getModel("catalog/product")->getIdBySku($item->getSku());
                $product = Mage::getModel("catalog/product")->load($productId);
                if ($product) {
                    $salesforcePricebkEntryId = $product->getSalesforceStandardPricebk();
                    if ($customerGroup == 2) {
                        $salesforcePricebkEntryId = $product->getSalesforceWholesalePricebk();
                    }
                }
            }

            $this->salesforceLog("Product id - " . $item->getSku() . " salesforcePriceBookId - " . $salesforcePricebkEntryId);

            if (!$salesforcePricebkEntryId) {
                $this->salesforceLog("Failed to create order: Product not present in SF");
                return;
            }

            $magOrderItemArr[] = array("item_id" => $item->getItemId(),
                "salesforce_id" => $item->getSalesforceItemId(),
                "sku" => $item->getSku(),
                "order_id" => $item->getOrderId()
            );

            $options = $item->getProductOptions()["options"];
            $postLength = "";
            foreach ($options as $option) {
                if ($option["label"] == "Post Length") {
                    $postLength = $option["value"];
                    break;
                }
            }

            $unitPrice = $item->getBasePrice() * $currencyRate;

            $reasonText = "";
            if ($item->getTeamworkReason()) {
                $reasonText = $item->getTeamworkReason();
            } elseif ($item->getOtherSysQty() < 0) {
                $reasonText = "Return";
            }
            $salesforceItemId = $item->getSalesforceItemId();
            $itemArray = array(
                "attributes" => array("type" => "OrderItem", "referenceId" => "order_items-" . $item->getItemId()),
                "PricebookEntryId" => $salesforcePricebkEntryId,//"01u290000037WAR",
                "quantity" => ($isTeamworkOrder) ? ($item->getOtherSysQty() ? $item->getOtherSysQty() : 1) : $item->getQtyOrdered(),
                "UnitPrice" => $unitPrice,
                "Post_Length__c" => $postLength,
                "Magento_Order_Item_Id__c" => $item->getItemId(),
                "SKU__c" => $item->getSku(),
                "reason__c" => $reasonText
            );
            if (!$create) {
                $this->salesforceLog("Splitting Orders and OrderItems for BULK Update");
                $itemArray["Id"] = $salesforceItemId;
                array_push($orderItemList, $itemArray);
            } else {
                array_push($orderItem["records"], $itemArray);
            }
        }

        $ostores = Mage::helper("allure_virtualstore")->getVirtualStores();
        $oldStoreArr = array();
        foreach ($ostores as $storeO) {
            $oldStoreArr[$storeO->getId()] = $storeO->getName();
        }

        $oldStoreArr[0] = "Admin";

        $request= array(
            "attributes" => array("type" => "Order", "referenceId" => "orders-" . $order->getData("entity_id")),
            "EffectiveDate" => date("Y-m-d", strtotime($createdAt)),
            "Created_At__c" => date("Y-m-d", strtotime($createdAt)) . "T" . date("H:i:s", strtotime($createdAt)) . "+00:00",//date("Y-m-d H:i:s",strtotime($createdAt)),
            "Status" => $status,
            "accountId" => $salesforceAccountId,    //"0012900000Ls44hAAB",
            "Pricebook2Id" => $pricebookId,    //"01s290000001ivyAAA",//$pricebookId,
            "BillingCity" => ($billingAddr) ? $billingAddr["city"] : "",
            "BillingCountry" => $countryName,
            "BillingPostalCode" => ($billingAddr) ? $billingAddr["postcode"] : "",
            "BillingState" => $state,
            "BillingStreet" => ($billingAddr) ? $billingAddr["street"] : "",
            "ShippingCity" => ($shippingAddr) ? $shippingAddr["city"] : "",
            "ShippingCountry" => $countryNameShip,
            "ShippingPostalCode" => ($shippingAddr) ? $shippingAddr["postcode"] : "",
            "ShippingState" => $stateShip,
            "ShippingStreet" => ($shippingAddr) ? $shippingAddr["street"] : "",

            "Shipping_Method__c" => $shippingDescription,
            "Quantity__c" => $totalQty,
            "Item_s_count__c" => $totalItemCount,

            "Shipping_Amount__c" => $baseShippingAmount,

            "Total_Refunded_Amount__c" => $baseTotalRefunded,
            "Tax_Amount__c" => $baseTaxAmount,

            "Sub_Total__c" => $baseSubtotal,
            "Discount__c" => $discountAmount,
            "Discount_Base__c" => $baseDiscountAmount,
            "Grant_Total__c" => $grandTotal,
            "Grand_Total_Base__c" => $baseGrandTotal,

            "Total_Paid__c" => $baseTotalPaid,
            "Total_Due__c" => $baseTotalDue,

            //"Name" => "Magento Order #".$incrementId,

            "Payment_Method__c" => $paymentMethod,
            "Store__c" => $oldStoreArr[$order->getStoreId()],
            "Old_Store__c" => $oldStoreArr[$order->getOldStoreId()],
            "Order_Id__c" => $order->getId(),
            "Increment_Id__c" => $incrementId,
            "Customer_Group__c" => $customerGroup,
            "Customer_Email__c" => $customerEmail,
            "Counterpoint_Order_ID__c" => $counterpointOrderId,
            "Customer_Note__c" => ($customerNote) ? $customerNote : "",
            "Signature__c" => ($order->getNoSignatureDelivery()) ? "Yes" : "No",
            //"OrderItems" => $orderItem
        );

        if (!$create) {
            $request["Id"] = $salesforceOrderId;
            $this->salesforceLog("Add ID to Order Request for Update");
            //   $request["order_item"] = $orderItem;
        }else{
            $request["OrderItems"] = $orderItem;
            $this->salesforceLog("Merging OrderItems for Create");
        }

        $payment = $order->getPayment();
        $code = $payment->getData('cc_type');
        $aType = Mage::getSingleton('payment/config')->getCcTypes();
        if (isset($aType[$code])) {
            $sName = $aType[$code];
            $request["Card_Type__c"] = $sName;
        }

        $last4Digits = $payment->getCcLast4();
        if ($last4Digits) {
            $request["Card_Number__c"] = "XXXX-" . $last4Digits;
        }

        $transactionId = $payment->getData("last_trans_id");
        if ($transactionId) {
            $request["Transaction_Id__c"] = $transactionId;
        }

        if ($order->getCreateOrderMethod() == 2) {
            $tmOrginalOrderId = $order->getTeamworkOrigReceiptId();
            if ($tmOrginalOrderId) {
                $tmOrginalOrderId = "TW-" . $tmOrginalOrderId;
                $orderObj = Mage::getModel('sales/order')->loadByIncrementId($tmOrginalOrderId);
                if ($orderObj) {
                    if ($orderObj->getSalesforceOrderId()) {
                        $request["Reference_Order__c"] = $orderObj->getSalesforceOrderId();
                    }
                    $request["Magento_Reference_Order__c"] = $orderObj->getIncrementId();
                }
            }
            $tmData = json_decode($order->getOtherSysExtraInfo(), true);
            $request["Teamwork_Receipt_Id__c"] = $tmData["ReceiptNum"];
            $request["Teamwork_Universal_Id__c"] = $tmData["DeviceTransactionNumber"];
            $request["Teamwork_Cashier__c"] = $tmData["EMPNAME"];
        }

        if(empty($orderItemList)){
            $this->salesforceLog("Return merged array for Order Event");
            return array("request" => $request, "orderItem" => $magOrderItemArr);
        }
        else{
            $this->salesforceLog("Return merged array for BULK Update");
            return array("order" => $request, "orderItem" => $orderItemList);
        }
    }

    public function getCustomerRequestData($customer,$create,$isFromEvent)
    {
        $ostores = Mage::helper("allure_virtualstore")->getVirtualStores();
        $oldStoreArr = array();

        foreach ($ostores as $storeO) {
            $oldStoreArr[$storeO->getId()] = $storeO->getName();
        }
        $oldStoreArr[0] = "Admin";

        if ($customer) {
            $customer = Mage::getModel('customer/customer')->load($customer->getId());
            $this->salesforceLog("Customer {$customer->getId()}");

            $salesforceId = $customer->getSalesforceCustomerId();
            $salesforceContactId = $customer->getSalesforceContactId();

            if($create && (!empty($salesforceId) && !empty($salesforceContactId))){
                $this->salesforceLog("Tried to create Customer and Contact - ".$customer->getId().". But Customer and Contact already Present in SF -".$salesforceId);
                return;
            }

            if(!$create && (empty($salesforceId) || empty($salesforceContactId))) {
                $this->salesforceLog("Tried to update Customer or Contact - ".$customer->getId().". But Customer or Contact not Present in SF -".$salesforceId);
                return;
            }

            $prefix = $customer->getPrefix();
            $fName = $customer->getFirstname();
            $mName = $customer->getMiddlename();
            $lName = $customer->getLastname();
            $fullName = "";
            if ($prefix) {
                $fullName .= $prefix . " ";
            }
            $fullName .= $fName . " ";
            if ($mName) {
                $fullName .= $mName;
            }
            $fullName .= $lName;

            $defaultBillingAddr = $customer->getDefaultBillingAddress();
            $state = "";
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
                        if ($country->getId()) {
                            $countryName = $country->getName();
                        }
                    }
                }
            }

            $stateShip = "";
            $countryNameShip = "";
            $defaultShippingAddr = $customer->getDefaultShippingAddress();

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
                    if (strlen($scountryNm) > 3) {
                        $countryNameShip = $scountryNm;
                    } else {
                        $country = Mage::getModel('directory/country')
                            ->loadByCode($defaultShippingAddr['country_id']);
                        if ($country->getId()) {
                            $countryNameShip = $country->getName();
                        }
                    }
                }
            }

            $accountRequest = array(
                "Name" => $fullName,
                //"AccountNumber"       => "",
                //"Site"                => "",
                //"AccountSource"       => "",
                //"Birth_Date_c"        => ($customer->getDob()) ? date("Y-m-d",strtotime($customer->getDob())) : null,//"YYYY-MM-DD",
                "Company__c" => $customer->getCompany(),
                "Counterpoint_No__c" => $customer->getCounterpointCustNo(),
                "Created_In__c" => $customer->getCreatedIn(),
                "Customer_ID__c" => $customer->getId(),
                "Customer_Note__c" => $customer->getCustomerNote(),
                "Default_Billing__c" => $customer->getDefaultBilling(),
                "Default_Shipping__c" => $customer->getDefaultShipping(),
                //"Description"         => "",
                "Email__c" => $customer->getEmail(),
                //"Fax"                 => "",
                "Gender__c" => ($customer->getGender()) ? $customer->getGender() : 4,
                "Phone" => ($defaultBillingAddr) ? $defaultBillingAddr->getTelephone() : "",
                "Store__c" => $oldStoreArr[$customer->getStoreId()],
                "Old_Store__c" => $oldStoreArr[$customer->getOldStoreId()],
                "Teamwork_Customer_ID__c" => $customer->getTeamworkCustomerId(),
                "TW_UC_GUID__c" => $customer->getTwUcGuid(),
                "Group__c" => $customer->getGroupId(),
                "BillingStreet" => ($defaultBillingAddr) ? implode(", ", $defaultBillingAddr->getStreet()) : "",
                "BillingCity" => ($defaultBillingAddr) ? $defaultBillingAddr->getCity() : "",
                "BillingState" => ($defaultBillingAddr) ? $state : "",
                "BillingPostalCode" => ($defaultBillingAddr) ? $defaultBillingAddr->getPostcode() : "",
                "BillingCountry" => ($defaultBillingAddr) ? $countryName : "",
                "ShippingStreet" => ($defaultShippingAddr) ? implode(", ", $defaultShippingAddr->getStreet()) : "",
                "ShippingCity" => ($defaultShippingAddr) ? $defaultShippingAddr->getCity() : "",
                "ShippingState" => ($defaultShippingAddr) ? $stateShip : "",
                "ShippingPostalCode" => ($defaultShippingAddr) ? $defaultShippingAddr->getPostcode() : "",
                "ShippingCountry" => ($defaultShippingAddr) ? $countryNameShip : ""
            );
            if(!$isFromEvent)
                $accountRequest["attributes"] = array("type" => "Account", "referenceId" => "customers-" . $customer->getData("entity_id"));

            if (!$create){
                $this->salesforceLog("Customer add ID for Accounts:Update");
                $accountRequest["Id"] = $salesforceId;
            }

            if ($customer->getDob()) {
                $accountRequest["Birth_Date__c"] = date("Y-m-d", strtotime($customer->getDob()));
            }

            $contactRequest = array(
                //"Id" => $salesforceContactId,
                "FirstName" => $fName,
                "MiddleName" => $mName,
                "LastName" => $lName,
                "Email" => $customer->getEmail(),
                "Phone" => ($defaultBillingAddr) ? $defaultBillingAddr->getTelephone() : "",
                "MailingStreet" => ($defaultBillingAddr) ? implode(", ", $defaultBillingAddr->getStreet()) : "",
                "MailingCity" => ($defaultBillingAddr) ? $defaultBillingAddr->getCity() : "",
                "MailingState" => ($defaultBillingAddr) ? $state : "",
                "MailingPostalCode" => ($defaultBillingAddr) ? $defaultBillingAddr->getPostcode() : "",
                "MailingCountry" => ($defaultBillingAddr) ? $countryName : "",
                "Contact_Id__c" => $customer->getId(),
                "AccountID" => $salesforceId
            );

            if(!$isFromEvent)
                $contactRequest["attributes"] = array("type" => "Contact", "referenceId" => "contact-" . $customer->getData("entity_id"));

            if (!$create){
                $this->salesforceLog("Customer add ID for Contact:Update");
                $contactRequest["Id"] = $salesforceContactId;
            }

            //tmwork fields accept marketing
            if ($customer->getTwAcceptMarketing()) {
                $accountRequest["Accept_Marketing__c"] = $customer->getTwAcceptMarketing();
            }
            //tmwork fields accept transactional
            if ($customer->getTwAcceptTransactional()) {
                $accountRequest["Accept_Transactional__c"] = $customer->getTwAcceptTransactional();
            }

            return array("customer" => $accountRequest, "contact" => $contactRequest);
        }
    }

    public function getInvoiceRequestData($invoice,$create,$isFromEvent){
        $order = $invoice->getOrder();
        $order = Mage::getModel("sales/order")->load($order->getId());
        $this->salesforceLog("INVOICE OrderID :" . $order->getId());
        $salesforceOrderId = $order->getSalesforceOrderId();
        $salesforceInvoiceId = $invoice->getSalesforceInvoiceId();

        //don't push to SF if the ID is present
        if ($create && !empty($salesforceInvoiceId)){
            $this->salesforceLog("Tried to create Invoice- ".$invoice->getId().". But Invoice already Present in SF -".$salesforceInvoiceId);
            return;
        }

        if (!$create && empty($salesforceInvoiceId)){
            $this->salesforceLog("Tried to Update Invoice- ".$invoice->getId().". But Invoice not Present in SF -".$salesforceInvoiceId);
            return;
        }

        $currencyRate = $order->getStoreToBaseRate();
        if (!$currencyRate) {
            $currencyRate = 1;
        }

        $this->salesforceLog("INVOICE : SalesforceOrderId :" . $salesforceOrderId);
        if ($salesforceOrderId) {
            $baseGrandTotal = $invoice->getBaseGrandTotal();
            $basTaxAmount = $invoice->getBaseTaxAmount();
            $baseShippingAmount = $invoice->getBaseShippingAmount();
            $baseSubtotal = $invoice->getBaseSubtotal();
            $baseDiscountAmount = $invoice->getBaseDiscountAmount();
            $discountDescrption = $invoice->getDiscountDescription();
            $createdAt = $invoice->getCreatedAt();
            $invoiceIncrementId = $invoice->getIncrementId();

            $orderDate = $order->getCreatedAt();
            $orderIncrementId = $order->getIncrementId();

            $status = $invoice->getState();
            $storeId = $invoice->getStoreId();

            //$totalQty = $invoice->getTotalQty();
            $totalQty = 0;
            foreach ($invoice->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                $qty = $item->getQty();
                $totalQty += $qty;
            }


            $ostores = Mage::helper("allure_virtualstore")->getVirtualStores();
            $oldStoreArr = array();
            foreach ($ostores as $storeO) {
                $oldStoreArr[$storeO->getId()] = $storeO->getName();
            }
            $oldStoreArr[0] = "Admin";

            $orderDate = date("Y-m-d", strtotime($orderDate)) . "T" . date("H:i:s", strtotime($orderDate)) . "+00:00";
            $createdAt = date("Y-m-d", strtotime($createdAt)) . "T" . date("H:i:s", strtotime($createdAt)) . "+00:00";

            $request = array(
                "Discount_Amount__c" => $baseDiscountAmount,
                "Discount_Descrition__c" => "for advertisment",
                "Grand_Total__c" => $baseGrandTotal,
                "Invoice_Id__c" => $invoiceIncrementId,
                "Order_Date__c" => $orderDate,//date("Y-m-d",strtotime($orderDate)),
                "Order_Id__c" => $orderIncrementId,
                "Status__c" => $status,
                "Total_Quantity__c" => $totalQty,
                "Invoice_Date__c" => $isFromEvent?$createdAt:$orderDate,
                "Shipping_Amount__c" => $isFromEvent?$baseShippingAmount:($baseShippingAmount * $currencyRate),
                "Subtotal__c" => $isFromEvent?$baseSubtotal:($baseSubtotal * $currencyRate),
                "Tax_Amount__c" => $isFromEvent?$basTaxAmount:($basTaxAmount *$currencyRate),
                "Store__c" => $oldStoreArr[$storeId],
                "Order__c" => $salesforceOrderId,
                "Name" => "Invoice for Order #" . $orderIncrementId
            );
            if(!$isFromEvent)
                $request["attributes"] = array("type" => "Invoice__c", "referenceId" => "invoice-" . $invoice->getId());

            if (!$create){
                $this->salesforceLog("INVOICE : Add Id for Update:");
                $request["Id"] = $salesforceInvoiceId;
            }
            return $request;
        } else {
            $this->salesforceLog("INVOICE : SalesforceOrderID not found.");
        }
    }

    public function getCreditMemoRequestData($creditMemo,$create,$isFromEvent){
        $order = $creditMemo->getOrder();

        $salesforceCreditmemoId = $creditMemo->getSalesforceCreditmemoId();
        $salesforceOrderId = $order->getSalesforceOrderId();

        $currencyRate = $order->getStoreToBaseRate();
        if(!$currencyRate){
            $currencyRate = 1;
        }

        //if there is ID present in Magento then don't create
        if ($create && !empty($salesforceCreditmemoId)){
            $this->salesforceLog("Tried to create CreditMemo- ".$creditMemo->getId().". But CreditMemo already Present in SF -".$salesforceCreditmemoId);
            return;
        }
        if (!$create && empty($salesforceCreditmemoId)){
            $this->salesforceLog("Tried to Update CreditMemo- ".$creditMemo->getId().". But CreditMemo Not Present in SF -".$salesforceCreditmemoId);
            return;
        }

        if (!$salesforceOrderId) {
            $this->salesforceLog("CreditMemo : No SalesforeOrderId found :");
            return;
        }else{
            $this->salesforceLog("CreditMemo : SalesforeOrderId found :" . $salesforceOrderId);
        }

        $incrementId = $creditMemo->getIncrementId();
        $orderIncrementId = $order->getIncrementId();
        $baseAdjustment = $creditMemo->getBaseAdjustment();
        $createdAt = $creditMemo->getCreatedAt();
        $status = $creditMemo->getState();
        $discountAmount = $creditMemo->getBaseDiscountAmount();
        $grandTotal = $creditMemo->getBaseGrandTotal();
        $orderDate = $order->getCreatedAt();
        $shippingAmount = $creditMemo->getBaseShippingAmount();
        $storeId = $creditMemo->getStoreId();
        $subtotal = $creditMemo->getBaseSubtotal();
        $taxAmount = $creditMemo->getBaseTaxAmount();

        $ostores = Mage::helper("allure_virtualstore")->getVirtualStores();
        $oldStoreArr = array();
        foreach ($ostores as $storeO) {
            $oldStoreArr[$storeO->getId()] = $storeO->getName();
        }
        $oldStoreArr[0] = "Admin";

        $createdAt = date("Y-m-d", strtotime($createdAt)) . "T" . date("H:i:s", strtotime($createdAt)) . "+00:00";
        $orderDate = date("Y-m-d", strtotime($orderDate)) . "T" . date("H:i:s", strtotime($orderDate)) . "+00:00";

        $request = array(
            "Adjustment__c" => $baseAdjustment,
            "Created_At__c" => $isFromEvent ? $createdAt : $orderDate,//date("Y-m-d",strtotime($createdAt)),
            "Credit_Memo_Id__c" => $incrementId,
            "Stauts__c" => $status,
            "Discount_Amount__c" => $isFromEvent ? $discountAmount : ($discountAmount * $currencyRate),
            "Grand_Total__c" => $isFromEvent ? $grandTotal : ($grandTotal * $currencyRate),
            "Order_Date__c" => $orderDate,//date("Y-m-d",strtotime($orderDate)),
            "Order_Id__c" => $orderIncrementId,
            "Shipping_Amount__c" => $isFromEvent ? $shippingAmount : ($shippingAmount * $currencyRate),
            "Store__c" => $oldStoreArr[$storeId],
            "Subtotal__c" => $isFromEvent ? $subtotal : ($subtotal * $currencyRate),
            "Tax_Amount__c" => $isFromEvent ? $taxAmount : ($taxAmount * $currencyRate),
            "Order__c" => $salesforceOrderId,
            "Name" => "Credit Memo for Order #" . $orderIncrementId
        );

        if(!$isFromEvent)
            $request["attributes"] = array("type" => "Credit_Memo__c", "referenceId" => "credit_memo-" . $creditMemo->getId());

        if (!$create){
            $this->salesforceLog("CreditMemo: Add Id to request for Updating");
            $request["Id"] = $salesforceCreditmemoId;
        }
         return $request;
    }

    public function getOrderItemUpdateDataForCreditMemo($creditMemoIds) {
        $request = array();

        foreach ($creditMemoIds as $creditMemoId){
            $creditMemo = Mage::getModel('sales/order_creditmemo')->load($creditMemoId);
            $salesforceCreditmemoId = $creditMemo->getSalesforceCreditmemoId();
            $items      = $creditMemo->getAllItems();

            foreach ($items as $item){
                $orderItemId = $item->getOrderItemId();
                $orderItem = Mage::getModel("sales/order_item")->load($orderItemId);
                if(!$orderItem){
                    continue;
                }
                $salesforceItemId = $orderItem->getSalesforceItemId();
                if(!$salesforceItemId){
                    continue;
                }
                $tempArr = array(
                    "attributes"        => array("type" => "OrderItem"),
                    "id"                => $salesforceItemId,
                    "Credit_Memo__c"    => $salesforceCreditmemoId
                );
                array_push($request,$tempArr);
            }
        }
        return $request;
    }

    public function getShipmentRequestData($shipment,$create,$isFromEvent)
    {
        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if (!$isEnable) {
            $this->salesforceLog("Salesforce Plugin Disabled.");
            return;
        }
        $salesforceShipmentId = $shipment->getSalesforceShipmentId();

        if ($create && !empty($salesforceShipmentId)){
            $this->salesforceLog("Tried to create Shipment- ".$shipment->getId().". But Shipment already Present in SF -".$salesforceShipmentId);
            return;
        }
        if (!$create && empty($salesforceShipmentId)){
            $this->salesforceLog("Tried to Update Shipment- ".$shipment->getId().". But Shipment Not Present in SF -".$salesforceShipmentId);
            return;
        }

        $order = $shipment->getOrder();

        $salesforceOrderId = $order->getSalesforceOrderId();
        $customerId = $shipment->getCustomerId();
        $incrementId = $shipment->getIncrementId();
        $orderIncrementId = $order->getIncrementId();

        $totalQty = $shipment->getTotalQty();
        $shippingLabel = $shipment->getShippingLabel();

        $weight = $order->getWeight();

        if (!$salesforceOrderId) {
            $this->salesforceLog("SHIPMENT: Salesforce ID Not Found");
            return;
        }

        $request = array(
            "Customer_Id__c" => $customerId,
            "Increment_ID__c" => $incrementId,
            "Order__c" => $salesforceOrderId,
            "Order_Id__c" => $orderIncrementId,
            "Quantity__c" => $totalQty,
            "Shipping_Label__c" => "",
            "Weight__c" => $weight,
            //"Carrier__c"        => $carrierTitles,
            //"Track_Number__c"   => $trackNums,
            "Name" => "Shipment for Order #" . $orderIncrementId
        );
        if(!$isFromEvent)
            $request["attributes"] = array("type" => "Shipment__c", "referenceId" => "shipment-" . $shipment->getId());

        if (!$create){
            $this->salesforceLog("SHIPMENT: ID added for UPDATE");
            $request["Id"] = $salesforceShipmentId;
        }

        return $request;
    }

    public function getTrackingInformationData($track,$create,$isFromEvent){

        $salesforceShipmentTrackId = $track->getData('salesforce_shipment_track_id');
        $shipment = $track->getShipment();
        $shipmentModel = Mage::getModel('sales/order_shipment')->load($shipment->getParentId());
        $trackingId = $track->getNumber();

        $this->salesforceLog("Tracking Id:".$trackingId);
        $salesforceShipmentId = $shipment->getSalesforceShipmentId();

        if ($create && !empty($salesforceShipmentTrackId)){
            $this->salesforceLog("Tried to Create ShipmentTrack- ".$shipment->getId().". But ShipmentTrack already Present in SF -".$salesforceShipmentTrackId);
            return;
        }
        if (!$create && empty($salesforceShipmentTrackId)){
            $this->salesforceLog("Tried to Update ShipmentTrack- ".$shipment->getId().". But ShipmentTrack Not Present in SF -".$salesforceShipmentTrackId);
            return;
        }

        $request = array(
            "Magento_Tracker_Id__c" => $track->getData("entity_id"),
            "Name" => $track->getData("title"),
            "Shipment__c" => $shipmentModel->getSalesforceShipmentId(),
            "Tracking_Number__c" => $track->getData("track_number"),
            "Carrier__c" => $track->getData("carrier_code")
        );

        if(!$isFromEvent)
            $request["attributes"] = array("type" => "Tracking_Information__c", "referenceId" => "shipment_track-".$shipment->getId());

        if (!$create){
            $this->salesforceLog("TRACK: ID added to request for UPDATE");
            $request["Id"] = $salesforceShipmentTrackId;
        }
        return $request;
    }

    public function getProductData($productOb,$create,$isFromObserver){

            $product = Mage::getModel('catalog/product')->load($productOb->getId());
            try {
                if ($product) {
                    $salesforceId = $product->getSalesforceProductId();

                    if ($create && !empty($salesforceId)){
                        $this->salesforceLog("Tried to Create Product- ".$product->getId().". But Product already Present in SF -".$salesforceId);
                        return;
                    }
                    if (!$create && empty($salesforceId)){
                        $this->salesforceLog("Tried to Create Product- ".$product->getId().". But Product already Present in SF -".$salesforceId);
                        return;
                    }

                    $metalColor = $product->getMetal();
                    $taxClassId = $product->getTaxClassId();
                    $gemstone = $product->getGemstone();
                    $amount = $product->getAmount();      //amount - select
                    $frSize = $product->getFrSize();      //fr_size - select
                    $sideEar = $product->getSideEar();     //side_ear - select
                    $direction = $product->getDirection(); //direction - select
                    $neckLength = $product->getNeckLengt(); //neck_lengt - select
                    $noseBend = $product->getNoseBend();    //nose_bend - select
                    $cLength = $product->getCLength();      //c_length - select
                    $size = $product->getSize();            //size - select
                    $gauge = $product->getGauge();           //gauge - select
                    $postOption = $product->getPostOptio(); //post_optio - select
                    $rise = $product->getRise();            //rise - select
                    $sLength = $product->getSLength();    //s_length - select
                    $placement = $product->getPlacement(); //placement - select
                    $material = $product->getMaterial(); //material - multiselect

                    $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
                    $attributeSetModel->load($product->getAttributeSetId());
                    $attributeSetName = $attributeSetModel->getAttributeSetName();

                    $categoryIds = $product->getCategoryIds();//array of product categories

                    //TODO - for sending categories into Salesforce
                    foreach ($categoryIds as $categoryId) {
                        $category = Mage::getModel('catalog/category')->load($categoryId);
                        $categoryName = $category->getName();
                    }

                    $request = array(
                        "attributes" => array("type" => "Product2", "referenceId" => "product-".$product->getId()),
                        "IsActive" => ($product->getStatus()) ? true : false,
                        //"Diamond_Color__c"          => "",
                        "DisplayUrl" => $product->getUrlKey(),
                        "ExternalId" => $product->getId(),
                        //"Gemstone__c"               => $product->getGemstone(),
                        "Jewelry_Care__c" => $product->getJewelryCare(),
                        //"Metal_Color__c"            => $product->getMetal(),
                        "ProductCode" => $product->getId(),
                        "Description" => htmlspecialchars($product->getDescription()),
                        "Family" => $product->getTypeId(),
                        "Name" => $product->getName(),
                        "StockKeepingUnit" => $product->getSku(),
                        "Return_Policy__c" => $product->getReturnPolicy(),
                        //"Tax_Class_Id__c"           => $product->getTaxClassId(),
                        "Vendor_Item_No__c" => $product->getVendorItemNo(),
                        "Location__c" => $attributeSetName,
                    );

                    //var_dump($request);die;

                    if ($metalColor) {
                        $metalColor = $product->getOptionLabel("metal", $metalColor);
                        $request["Metal_Color__c"] = $metalColor;
                    }

                    if ($taxClassId) {
                        $request["Tax_Class_Id__c"] = $taxClassId;
                    }

                    if ($gemstone) {
                        $gemstone = $product->getOptionLabel("gemstone", $gemstone);
                        $request["Gemstone__c"] = $gemstone;
                    }

                    if ($amount) {
                        $amount = $product->getOptionLabel("amount", $amount);
                        $request["Amount__c"] = $amount;
                    }

                    if ($frSize) {
                        $frSize = $product->getOptionLabel("fr_size", $frSize);
                        $request["FR_SIZE__c"] = $frSize;
                    }

                    if ($sideEar) {
                        $sideEar = $product->getOptionLabel("side_ear", $sideEar);
                        $request["SIDE_EAR__c"] = $sideEar;
                    }

                    if ($direction) {
                        $direction = $product->getOptionLabel("direction", $direction);
                        $request["DIRECTION__c"] = $direction;
                    }

                    if ($neckLength) {
                        $neckLength = $product->getOptionLabel("neck_lengt", $neckLength);
                        $request["NECK_LENGT__c"] = $neckLength;
                    }

                    if ($noseBend) {
                        $noseBend = $product->getOptionLabel("nose_bend", $noseBend);
                        $request["NOSE_BEND__c"] = $noseBend;
                    }

                    if ($cLength) {
                        $cLength = $product->getOptionLabel("c_length", $cLength);
                        $request["C_LENGTH__c"] = $cLength;
                    }

                    if ($size) {
                        $size = $product->getOptionLabel("size", $size);
                        $request["SIZE__c"] = $size;
                    }

                    if ($gauge) {
                        $gauge = $product->getOptionLabel("gauge", $gauge);
                        $request["GAUGE__c"] = $gauge;
                    }

                    if ($postOption) {
                        $postOption = $product->getOptionLabel("post_optio", $postOption);
                        $request["POST_OPTIO__c"] = $$postOption;
                    }

                    if ($rise) {
                        $rise = $product->getOptionLabel("rise", $rise);
                        $request["RISE__c"] = $rise;
                    }

                    if ($sLength) {
                        $sLength = $product->getOptionLabel("s_length", $sLength);
                        $request["S_Length__c"] = $sLength;
                    }

                    if ($placement) {
                        $placement = $product->getOptionLabel("placement", $placement);
                        $request["PLACEMENT__c"] = $placement;
                    }

                    if ($material) {
                        $tMaterial = array();
                        $materialArr = $product->getOptionLabelArray("material");
                        foreach (explode(",", $material) as $mat) {
                            $tMaterial[] = $materialArr[$mat];
                        }
                        $request["Material__c"] = implode(",", $tMaterial);
                    }

                    if(!$create)
                        $request["Id"] = $salesforceId;

                    $standardPriceBkId = $product->getSalesforceStandardPricebk();
                    $wholesalePriceBkId = $product->getSalesforceWholesalePricebk();
                    $retailerPrice = $product->getPrice();
                    $wholesalePrice = 0;
                    foreach ($product->getData('group_price') as $gPrice) {
                        if ($gPrice["cust_group"] == 2) { //wholesaler group : 2
                            $wholesalePrice = $gPrice["price"];
                        }
                    }

                    if ($standardPriceBkId) {
                        $sRequest["records"] = array(
                            array(
                                "attributes" => array("type" => "PricebookEntry"),
                                "id" => $standardPriceBkId,
                                "UnitPrice" => $retailerPrice
                            )
                        );

                        if ($wholesalePrice  && $wholesalePriceBkId) {
                            $sTemp = array(
                                "attributes" => array("type" => "PricebookEntry"),
                                "id" => $wholesalePriceBkId,
                                "UnitPrice" => $wholesalePrice
                            );
                            //array_push($sRequest["records"], $sTemp);
                            $sRequest["records"] = $sTemp;
                        }
                    } else {
                        $sRequest["records"] = array(
                            array(
                                "attributes" => array(
                                    "type" => "PricebookEntry",
                                    "referenceId" => "productG-" . $product->getId()
                                ),
                                "Pricebook2Id" => Mage::helper('allure_salesforce')->getGeneralPricebook(),//$this::RETAILER_PRICEBOOK_ID,
                                //"Product2Id" => $salesforceProductId,
                                "UnitPrice" => $retailerPrice
                            )
                        );

                        if ($wholesalePrice) {
                            $sTemp = array(
                                "attributes" => array(
                                    "type" => "PricebookEntry",
                                    "referenceId" => "productW-" . $product->getId()
                                ),
                                "Pricebook2Id" => Mage::helper('allure_salesforce')->getWholesalePricebook(),//$this::WHOLESELLER_PRICEBOOK_ID,
                                //"Product2Id" => $salesforceProductId,
                                "UnitPrice" => $wholesalePrice
                            );
                            array_push($sRequest["records"], $sTemp);
                        }
                    }

                    if($create){
                        $this->salesforceLog("PRODUCT: Adding PriceBookEntries for CREATE");
                        $request["PriceBookEntries"] = $sRequest;
                        return $request;
                    } else {
                        $this->salesforceLog("PRODUCT: Splitting Product and PricebookEntries for UPDATE");
                        return array("product" => $request,"pricebookEntries" => $sRequest["records"]);
                    }
                }
            } catch (Exception $e) {
                $this->salesforceLog("Exception in ADD/UPDATE into salesforce, message ". $e->getMessage());
            }
    }

    /**
     * @param $sql query to be executed
     * @desc This will execute the SQL Query
     */
    public function executeQuery($sql)
    {
        try {
            $coreResource = Mage::getSingleton('core/resource');
            $write = $coreResource->getConnection('core_write');
            $this->salesforceLog("SQL Execute executing Query :-".$sql);
            $write->query($sql);
        } catch (Exception $e) {
            $this->salesforceLog("Error in SQL Execute :" . $e->getMessage(). "\n For Query :".$sql);
        }
    }

    public function bulkProcessResponse($responseArr = null, $modelName)
    {
        $this->salesforceLog("BULK Update: processResponse START ",true);

        if (!empty($responseArr)) {
            $responseMapping = array(
                "orders" => array("sales_flat_order", "salesforce_order_id"),
                "order_items" => array("sales_flat_order_item", "salesforce_item_id"),
                "customers" => array("customer_entity_varchar", "salesforce_customer_id"),
                "contact" => array("customer_entity_varchar", "salesforce_contact_id"),
                "invoice" => array("sales_flat_invoice", "salesforce_invoice_id"),
                "credit_memo" => array("sales_flat_creditmemo", "salesforce_creditmemo_id"),
                "shipment" => array("sales_flat_shipment", "salesforce_shipment_id"),
                "shipment_track" => array("sales_flat_shipment_track","salesforce_shipment_track_id"),
                "product" =>  array("_", "salesforce_product_id"),
                "productG" =>  array("_", "salesforce_standard_pricebk"),
                "productW" =>  array("_", "salesforce_wholesale_pricebk"),
            );

            $results = $responseArr["results"];
            $sql = "";
            $creditMemoIds = array();
            //if (!$create) {
            if ($modelName === "customers" || $modelName === "contact") {
                foreach ($results as $res) {
                    $ref = $res["referenceId"];
                    $refArr = explode("-", $ref);
                    $modelName = $refArr[0];
                    $attribute_id = $modelName === "customers" ? '384' : '402';
                    $refId = $refArr[1];

                    $tableName = $responseMapping[$modelName][0];
                    $sql .= "INSERT INTO " . $tableName . " VALUES(NULL,'1'," . $attribute_id . "," . $refId . "," . $res["id"] . ");";
                    $logString = "Adding for ". $modelName . " Attribute Id = " . $attribute_id . " Value = ". $res["id"];
                    $this->salesforceLog($logString,true);
                }
                $this->executeQuery($sql);
            } else if($modelName === "products"){
                $mainStoreId = 1;

                foreach ($results as $res) {
                    $productAttrArray = array();
                    $ref = $res["referenceId"];
                    $refArr = explode("-", $ref);
                    $refId = $refArr[1];
                    $modelName = $refArr[0];
                    $fieldName = $responseMapping[$modelName][1];

                    $productAttrArray[$fieldName] = $res["id"];

                    try {
                        Mage::getResourceSingleton('catalog/product_action')
                            ->updateAttributes(array($refId), $productAttrArray, $mainStoreId);

                        //$this->deleteSalesforcelogRecord($objectType1, $requestMethod, $res['id']);
                        //$this->salesforceLog("Pricebook Data added. Product Id :" . $res['id'],true);
                        $logString = "Adding for ". $modelName . " Field name = " . $fieldName. " Value = ". $res["id"];
                        $this->salesforceLog($logString,true);
                    } catch (Exception $ee) {
                        $this->salesforceLog("Exception in add or update product into salesforce",true);
                        $this->salesforceLog("Message :" . $ee->getMessage(),true);
                    }
                }
            } else {
                foreach ($results as $res) {
                    $ref = $res["referenceId"];
                    $refArr = explode("-", $ref);

                    $modelName = $refArr[0];
                    $refId = $refArr[1];
                    array_push($creditMemoIds,$refId);

                    $tableName = $responseMapping[$modelName][0];
                    $fieldName = $responseMapping[$modelName][1];
                    $sql .= "UPDATE " . $tableName . " SET " . $fieldName . "='" . $res["id"] . "' WHERE entity_id ='" . $refId . "';";
                    $logString = "Adding for ". $modelName . " Field name = " . $fieldName. " Value = ". $res["id"];
                    $this->salesforceLog($logString,true);
                }
                $this->executeQuery($sql);
            }
            if($modelName === "credit_memo"){
                $requestData = $this->getOrderItemUpdateDataForCreditMemo($creditMemoIds);
                $cRequest = array("allOrNone"=>false);
                $cRequest["records"] = $requestData;
                $requestMethod = "PATCH";
                $urlPath = $this::UPDATE_COMPOSITE_OBJECT_URL;
                $response = $this->sendRequest($urlPath,$requestMethod,$cRequest);
                $responseArr1 = json_decode($response,true);
                if($responseArr1[0]["success"]){
                    $this->salesforceLog("creditmemo items updated into salesforce.",true);
                }else{
                    if($responseArr == "") {
                        $this->salesforceLog("creditmemo items not updated into salesforce.",true);
                    }
                }
            }
        }
        $this->salesforceLog("BULK Update: processResponse END ",true);
    }
}
