<?php
/**
 * @author allure
 */
class Allure_Teamwork_Model_Observer{	
    
    public function addCustomers(){
        $helper = Mage::helper("allure_teamwork");
        $status = $helper->getTeamworkStatus();
        $teamwoek_log_file = "teamwork_mag_customer_3.log";
        Mage::log("Teamwork Cron start",Zend_log::DEBUG,$teamwoek_log_file,true);
        if($status){
            
            $collection = Mage::getModel("allure_teamwork/teamwork")
                ->getCollection()
                ->setOrder('customer_id', 'asc');
            
            $latestItemId = $collection->getLastItem()->getCustomerId();
            
            if($latestItemId){
                
                $start = $latestItemId + 1;
                $end   = $start + 100;
            
                $customers  = Mage::getModel('customer/customer')
                    ->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('entity_id',
                        array(
                                'gteq' => $start
                            )
                        )
                    ->addAttributeToFilter('entity_id',
                        array(
                                'lteq' => $end
                            )
                        )
                    ->load();
                
                    
                    $model  = Mage::getModel("allure_teamwork/teamwork");
                    $_accessToken = $helper->getTeamworkAccessToken();
                    $_url         = "https://api.teamworksvs.com/externalapi3/customers/register";
                    $guid1 = "";
                    $guid2 = "";
                    foreach ($customers as $customer){
                        try{
                            $data    = $customer->getData();
                            $customer_id = $customer->getId();
                            $request = array();
                            $request['firstName'] = $data['firstname'];
                            $request['lastName']  = $data['lastname'];
                            $email = strtolower($data['email']);
                            if(!empty($data['email'])){
                                $request['email1']    = (object) array("email"=> $email);
                            }
                            $request['customText1'] = $data['website_id'];
                            $request['customFlag1'] = ($data['group_id'] == 2 )?true:false;
                            
                            $billingAddr  = $customer->getDefaultBillingAddress();
                            $shippingAddr = $customer->getDefaultShippingAddress();
                            
                            $addressArr =  array();
                            
                            if($billingAddr){
                                $billingAddrData = $billingAddr->getData();
                                $guid1 = $this->getGuid4();
                                $addressArr[] = (object) array(
                                    "addressID" => $guid1,
                                    "firstName" =>  $billingAddrData['firstname'],
                                    "lastName"  =>  $billingAddrData['lastname'],
                                    "address1"  =>  ($billingAddrData['street'])?$billingAddrData['street']:null,
                                    "city"    =>  ($billingAddrData['city'])?$billingAddrData['city']:null,
                                    "region"    =>  ($billingAddrData['state'])?$billingAddrData['state']:null,
                                    "countryCode"   =>  ($billingAddrData['country_id'])?$billingAddrData['country_id']:null,
                                    "postalCode"    =>  ($billingAddrData['postcode'])?$billingAddrData['postcode']:null,
                                    "phone" =>  ($billingAddrData['telephone'])?$billingAddrData['telephone']:null
                                );
                                $request['defaultBillingAddressID'] = $guid1;
                            }
                            
                            if(!empty($billingAddrData['telephone'])){
                                if($billingAddrData['telephone'] !="000-000-0000")
                                $request['phone1'] = (object) array("number"=>$billingAddrData['telephone']);
                            }
                            
                            if($shippingAddr){
                                $shippingAddrData = $shippingAddr->getData();
                                $guid2 = $this->getGuid4();
                                $addressArr[] =  (object) array(
                                    "addressID" => $guid2,
                                    "firstName" =>  $shippingAddrData['firstname'],
                                    "lastName"  =>  $shippingAddrData['lastname'],
                                    "address1"  =>  ($shippingAddrData['street'])?$shippingAddrData['street']:null,
                                    "city"    =>  ($shippingAddrData['city'])?$shippingAddrData['city']:null,
                                    "region"    =>  ($shippingAddrData['state'])?$shippingAddrData['state']:null,
                                    "countryCode"   =>  ($shippingAddrData['country_id'])?$shippingAddrData['country_id']:null,
                                    "postalCode"    =>  ($shippingAddrData['postcode'])?$shippingAddrData['postcode']:null,
                                    "phone" =>  ($shippingAddrData['telephone'])?$shippingAddrData['telephone']:null
                                );
                                $request['defaultShippingAddressID'] = $guid2;
                            }
                            
                            $request['addresses'] = $addressArr;
                            
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
                            
                            $model = Mage::getModel("allure_teamwork/teamwork")->load($customer_id,'customer_id');
                            if(!$model->getId()){
                                $model = Mage::getModel("allure_teamwork/teamwork");
                            }
                            
                            $model->setCustomerId($customer_id)
                                ->setEmail($email)
                                ->setAutoGenBillId($guid1)
                                ->setAutoGenShipId($guid2);
                            
                            if(!$responseObj->errorCode){
                                $teamworkCustomerId = $responseObj->customer->customerID;
                                $customerObj = Mage::getModel("customer/customer")->load($customer_id);
                                $customerObj->setTeamworkCustomerId($teamworkCustomerId);
                                $customerObj->save();
                                
                                $model->setResponse($response)
                                    ->setTeamworkCustomerId($teamworkCustomerId);
                                
                                    Mage::log("id-:".$customer->getId()." email-:".$email." == teamwork_id-:".$teamworkCustomerId,Zend_log::DEBUG,$teamwoek_log_file,true);
                            }
                            else {
                                $model->setIsError(1)
                                ->setResponse($response);
                                Mage::log("id-:".$customer->getId()." email-:".$email." == error-:".$response,Zend_log::DEBUG,$teamwoek_log_file,true);
                            }
                            
                            $model->save();
                            $model = null;
                        }catch (Exception $e){
                            Mage::log("id-:".$customer->getId(). " email-:".$email." == Exception-:".$e->getMessage(),Zend_log::DEBUG,$teamwoek_log_file,true);
                        }
                   }
              }
          }
     }
    
     function getGuid4(){
        $charid = strtolower(md5(uniqid(rand(), true)));
        $hyphen = chr(45);                  // "-"
        $lbrace = "";//$trim ? "" : chr(123);    // "{"
        $rbrace = "";//$trim ? "" : chr(125);    // "}"
        $guidv4 = $lbrace.
        substr($charid,  0,  8).$hyphen.
        substr($charid,  8,  4).$hyphen.
        substr($charid, 12,  4).$hyphen.
        substr($charid, 16,  4).$hyphen.
        substr($charid, 20, 12).
        $rbrace;
        return strtoupper($guidv4);
    }
    
    public function updateCustomers(){
        $_url         = "https://api.teamworksvs.com/externalapi3/customers/update";
        $_accessToken = "bWFyaWF0dGVzdDIgNTYyOTQ5OTUzNDIxMzEyMCB1ZnlQM3VIM05nN1g1WTJYODdaWk5PSk91SjF1dXEzUw==";
        
        $teamwoek_log_file = "teamwork_mag_customer_3_update.log";
        
        $_collection = Mage::getModel('allure_teamwork/teamwork')->getCollection();
        $_collection->getSelect()
        ->where("last_id = (select max(last_id) from allure_teamwork_customer)");
        
        $latestItemId = $_collection->getLastItem()->getLastId();
        $count=$latestItemId;
        $start = $_collection->getLastItem()->getId();
        $end = $start + 400;
        
        $collection = Mage::getModel("allure_teamwork/teamwork")->getCollection()
            ->addFieldToFilter('id', array(
            'gteq' => $start
        ))
            ->addFieldToFilter('id', array(
            'lteq' => $end
        ))
     /*        ->addFieldToFilter('is_counterpoint_cust', array(
            'eq' => 1
        )) */
            ->addFieldToFilter('teamwork_customer_id', array(
            'notnull' => true
        ));
        foreach ($collection as $teamwork) {
            try {
                $count ++;
                $email = $teamwork->getEmail();
                if (! empty($email)) {
                    $request = array();
                    $request['customerID'] = $teamwork->getTeamworkCustomerId();
                    
                    $magentoCustomerId = $teamwork->getCustomerId();
                    if ($magentoCustomerId != 0) {
                        $request['magentoID'] = $magentoCustomerId;
                        $customer = Mage::getModel("customer/customer")->load($magentoCustomerId);
                        if ($customer->getId()) {
                            if (! empty($customer->getTaxvat())) {
                                $request['VATRegistrationNumber'] = $customer->getTaxvat();
                            }
                        }
                    }
                    
                    if (! empty($email)) {
                        $request['email1'] = (object) array(
                            "email" => $email,
                            'acceptMarketing' => true
                        );
                    }
                    
                    $customerNote = $teamwork->getCustomerNote();
                    if (! empty($customerNote)) {
                        $request['largeMemo'] = $customerNote;
                    }
                    
                    $counterpntCustNo = $teamwork->getCounterpointCustNo();
                    $isCounterpointCust = $teamwork->getIsCounterpointCust();
                    
                    $request['customText4'] = $counterpntCustNo;
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
                    echo "<pre>";
                    // print_r($responseObj);
                    Mage::log("id-:" . $teamwork->getId() . " email-:" . $email . " updated", Zend_log::DEBUG, 'teamwork_mag_customer_3_update_response.log', true);
                    Mage::log("id-:" . $teamwork->getId() . " Response-:" . $response, Zend_log::DEBUG, 'teamwork_mag_customer_3_update_response.log', true);
                    Mage::log("id-:" . $teamwork->getId() . " email-:" . $email . " updated", Zend_log::DEBUG, $teamwoek_log_file, true);
                    
                    $model = Mage::getModel('allure_teamwork/teamwork')->load($teamwork->getId());
                    if ($model->getId()) {
                        $model->setLastId($count)->save();
                    }
                }
            } catch (Exception $e) {
                Mage::log("id-:" . $customer->getId() . " email-:" . $email . " == Exception-:" . $e->getMessage(), Zend_log::DEBUG, $teamwoek_log_file, true);
            }
        }
    }
    
    
    /**
     * add cp customer related info into mage customer
     * table allure_customer_counterpoint
     *
     */
    public function addCustomerDataUsingOrder(){
        $logFile = "cntr_customer_prepare.log";
        $size = 100;
        $operation = "al_cust_to_mag_cust";
        $mLog = Mage::getModel("allure_teamwork/log")->load($operation,'operation');;
        $page = $mLog->getPage();
        $size = $mLog->getSize();
        try{
            $collection = Mage::getModel("sales/order")->getCollection();
            $collection->addFieldToFilter( 'create_order_method', array('eq'=>1));
            $collection->setCurPage($page);
            $collection->setPageSize($size);
            $collection->setOrder('entity_id', 'asc');
            $collection->getSelect()->group('customer_email');
            
            $lastPage = $collection->getLastPageNumber();
            if($page < $lastPage){
            
                Mage::log("count = ".$collection->getSize(),Zend_log::DEBUG,$logFile,true);
                $cnt = 0;
                $resource       = Mage::getSingleton('core/resource');
                $writeAdapter   = $resource->getConnection('core_write');
                $writeAdapter->beginTransaction();
                foreach ($collection as $order){
                    $customerId = $order->getCustomerId();
                    $email      = $order->getCustomerEmail();
                    try{
                        $customer   = Mage::getModel("customer/customer")->load($customerId);
                        if($customer->getId()){
                            $extraInfo = unserialize($order->getCounterpointExtraInfo());
                            $custNo    = $extraInfo['cust_no'];
                            if($customer->getCustomerType() == 0){
                                $customer->setCustomerType(4);   //magento cust
                            }
                            $customer->setCounterpointCustNo($custNo);
                            $customer->setTempEmail($email);
                            $customer->save();
                            Mage::log($cnt ." customer_id:".$customerId,Zend_log::DEBUG,$logFile,true);
                        }
                        $customer = null;
                        if (($cnt % 100) == 0) {
                            $writeAdapter->commit();
                            $writeAdapter->beginTransaction();
                        }
                    }catch (Exception $exc){
                        Mage::log("customer_id:".$customerId." Exc:".$exc->getMessage(),Zend_log::DEBUG,$logFile,true);
                    }
                    $cnt++;
                }
                $writeAdapter->commit();
                $page +=1;
                if($mLog->getId()){
                    $mLog->setPage($page)->save();
                }
            }
        }catch (Exception $e){
            Mage::log("Exception:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
        }
        Mage::log("Finish...",Zend_log::DEBUG,$logFile,true);
    }
    
    
    /**
     * add arr_cust csv of cptr into magento customer
     * table allure_teamwork_ar_cust_cp
     */
    public function addCpCustomerIntoMagento(){
        $logFile = "cntr_create_cust_in_mag.log";
        
        
        try{
            $cnt    = 0;
            $size = 100;
            $operation = "arr_cust_csv_to_mag_cust";
            $mLog = Mage::getModel("allure_teamwork/log")->load($operation,'operation');;
            $page = $mLog->getPage();
            $size = $mLog->getSize();
            
            $resource       = Mage::getSingleton('core/resource');
            $writeAdapter   = $resource->getConnection('core_write');
            $writeAdapter->beginTransaction();
            
            $collection = Mage::getModel("allure_teamwork/cpcustomer")->getCollection();
            $collection->setCurPage($page);
            $collection->setPageSize($size);
            $collection->setOrder('id', 'asc');
            
            $lastPage = $collection->getLastPageNumber();
            if($page < $lastPage){
                
                $store = 'counterpoint_vmt';
                
                $storeVMT   = Mage::getModel('core/store')->load($store,'code');
                $storeId    = $storeVMT->getId();
                $websiteId  = $storeVMT->getWebsiteId();
                
                $alphabets = range('A','Z');
                $numbers = range('0','9');
                $additional_characters = array('#','@','$');
                
                foreach ($collection as $cpcust){
                    try{
                        $custNo = $cpcust->getCustNo();
                        $name  = $cpcust->getName();
                        $fstName = $cpcust->getFstName();
                        $lstName = $cpcust->getLstName();
                        $addr1 = $cpcust->getAddr1();
                        $addr2 = $cpcust->getAddr2();
                        $city = $cpcust->getCity();
                        $state = $cpcust->getState();
                        $country = $cpcust->getCountry();
                        $zipCode = $cpcust->getZipCode();
                        $phone = $cpcust->getPhone();
                        $email1 = $cpcust->getEmail();
                        $email2 = $cpcust->getOptionalEmail();
                        $group = $cpcust->getGroup();
                        $strId = $cpcust->getStrId();
                        $custNote = $cpcust->getCustNote();
                        if(empty($email1)){
                            if(!empty($email2)){
                                $email1 = $email2;
                            }else {
                                if(!empty($name)){
                                    $email = str_replace(' ', '', $name);
                                    $email = $email."@customers.mariatash.com";
                                }else{
                                    if(!empty($fstName) && !empty($lstName)){
                                        $email = $fstName.$lstName."@customers.mariatash.com";
                                    }
                                }
                            }
                        }
                        
                        $firstName = $fstName;
                        $lastName  = $lstName;
                        
                        if(empty($firstName) && empty($lastName)){
                            $name        = explode(" ", $name);
                            $firstName  = $name[0];
                            $lastName   = $name[0];
                            if(count($name) > 1){
                                $lastName = $name[1];
                            }
                        }
                        
                        $email = strtolower($email);
                        
                        $collection  = Mage::getModel('customer/customer')
                        ->getCollection()
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('counterpoint_cust_no', array('eq' => $custNo));
                        
                        if(!($collection->getSize()>0)){
                            $groupId = 1; //general;
                            if($group == "B"){
                                $groupId = 2; //wholesale;
                            }
                            
                            $final_array = array_merge($alphabets,$numbers,$additional_characters);
                            $password = '';
                            $length = 6;  //password length
                            while($length--) {
                                $keyV = array_rand($final_array);
                                $password .= $final_array[$keyV];
                            }
                            
                            //$password = $this->generateRandomPassword();
                            $customer = Mage::getModel("customer/customer");
                            $customer->setWebsiteId($websiteId)
                            ->setStoreId($storeId)
                            ->setGroupId($groupId)
                            ->setFirstname($firstName)
                            ->setLastname($lastName)
                            ->setEmail($email)
                            ->setPassword($password)
                            ->setCustomerType(3)  //counterpoint arr_cust
                            ->setCounterpointCustNo($custNo)
                            ->setCustNote($custNote)
                            ->save();
                            
                            $_billing_address = array (
                                'firstname'  => $customer->getFirstname(),
                                'lastname'   => $customer->getLastname(),
                                'street'     => array (
                                    '0' => (!empty($addr1))?$addr1:$addr2,
                                    '1' => $addr2
                                ),
                                'city'       => $city,
                                'postcode'   => $zipCode,
                                'country_id' => $country,
                                'region' 	=> 	$state,
                                'telephone'  => $phone,
                                'fax'        => '',
                            );
                            
                            $address = Mage::getModel("customer/address");
                            $address->setData($_billing_address)
                            ->setCustomerId($customer->getId())
                            ->setIsDefaultBilling('1')
                            ->setIsDefaultShipping('1')
                            ->setSaveInAddressBook('1')
                            ->save();
                            Mage::log($cnt." add id:".$customer->getId(),Zend_log::DEBUG,$logFile,true);
                        }else{
                            Mage::log($cnt." exist id:".$customer->getId(),Zend_log::DEBUG,$logFile,true);
                        }
                        
                        $address  = null;
                        $customer = null;
                        
                        if (($cnt % 100) == 0) {
                            $writeAdapter->commit();
                            $writeAdapter->beginTransaction();
                        }
                        
                    }catch (Exception $e){
                        Mage::log("exc:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
                    }
                    $cnt++;
                }
                $page +=1;
                if($mLog->getId()){
                    $mLog->setPage($page)->save();
                }
                $writeAdapter->commit();
                
            }
        }catch (Exception $e){
            Mage::log("Exception:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
        }
        Mage::log("Finish...",Zend_log::DEBUG,$logFile,true);
    }
}
