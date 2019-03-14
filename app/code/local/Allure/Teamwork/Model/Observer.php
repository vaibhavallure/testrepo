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
            $collection->getSelect()->group('customer_id');

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

                            $model = Mage::getModel("allure_teamwork/cpcustomer")->load($custNo,"cust_no");
                            if($model->getId()){
                                $cust_note = $model->getCustNote();
                                $customer->setCustNote($cust_note);
                            }
                            $customer->setCounterpointCustNo($custNo);
                            $customer->setTempEmail($email);
                            $customer->save();
                            Mage::log($cnt ." customer_id:".$customerId,Zend_log::DEBUG,$logFile,true);

                            if (($cnt % 100) == 0) {
                                $writeAdapter->commit();
                                $writeAdapter->beginTransaction();
                            }
                        }
                        $customer = null;

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
                                $email = $email2;
                            }else {
                                if(!empty($name)){
                                    $email = str_replace(' ', '', $name);
                                    if(preg_match("/OL/", $custNo)){
                                        $custNum = str_replace('-', '', $custNo);
                                        $email = $email.$custNum;
                                    }
                                }else{
                                    if(!empty($fstName) && !empty($lstName)){
                                        $email = $fstName.$lstName;
                                        if(preg_match("/OL/", $custNo)){
                                            $custNum = str_replace('-', '', $custNo);
                                            $email = $email.$custNum;
                                        }
                                    }
                                }
                                $email = $email."@customers.mariatash.com";
                            }
                        }else{
                            $email = $email1;
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

                        $collectionCust  = Mage::getModel('customer/customer')
                        ->getCollection()
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('counterpoint_cust_no', array('eq' => $custNo));

                        if(!($collectionCust->getSize()>0)){
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

                            $customerObj = Mage::getModel('customer/customer')
                            ->setWebsiteId(0)
                            ->loadByEmail($email);

                            if($customerObj->getId()){
                                $customerId = $customerObj->getId();
                                $tempModel = Mage::getModel("allure_teamwork/temp")->load($customerId,"customer_id");
                                if(!$tempModel->getId()){
                                    $emailCustomer = $customerObj->getEmail();
                                    $tempModel->setCustNo($custNo);
                                    $tempModel->setCustNote($custNote);
                                    $tempModel->setEmail($emailCustomer);
                                    $tempModel->setTempEmail($email);
                                    $tempModel->setCustomerId($customerId);
                                    $tempModel->save();
                                    Mage::log($cnt." add customer_id:".$customerId." into temp table",Zend_log::DEBUG,$logFile,true);
                                }
                            }else{
                                Mage::log("come in add",Zend_log::DEBUG,$logFile,true);

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
                            }
                        }else{
                            Mage::log($cnt." exist id",Zend_log::DEBUG,$logFile,true);
                        }
                        $collectionCust = null;
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


    /**
     * add customers data into teamwork crm
     */
    public function addCustomersIntoTeamwork(){
        $helper = Mage::helper("allure_teamwork");
        $status = $helper->getTeamworkStatus();
        $teamwoek_log_file = "teamwork_customer_crm.log";
        Mage::log("Teamwork Cron starting...",Zend_log::DEBUG,$teamwoek_log_file,true);
        if($status){
            Mage::log("start",Zend_log::DEBUG,$teamwoek_log_file,true);
            /* $collection = Mage::getModel("allure_teamwork/teamwork")
            ->getCollection()
            ->setOrder('customer_id', 'asc'); */
            try{

                $operation = "import_customer_to_teamwork";
                $mLog = Mage::getModel("allure_teamwork/log")
                    ->load($operation,'operation');
                $latestItemId = $mLog->getPage();
                $limit = $mLog->getSize();

                //$latestItemId = $collection->getLastItem()->getCustomerId();

                if($latestItemId){

                    $start = $latestItemId + 1;
                    $end   = $start + $limit;

                    $customers  = Mage::getModel('customer/customer')
                    ->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('entity_id',array('gteq' => $start))
                    ->addAttributeToFilter('entity_id',array('lteq' => $end))
                    ->load();


                     $model  = Mage::getModel("allure_teamwork/teamwork");
                     $_accessToken = $helper->getTeamworkAccessToken();
                     $_url         = "https://api.teamworksvs.com/externalapi3/customers/register";
                     $guid1 = "";
                     $guid2 = "";
                     foreach ($customers as $customer){
                        try{
                            $data           = $customer->getData();
                            if(!$customer->getIsDuplicate()){

                                $customer_id    = $customer->getId();
                                $request        = array();
                                $request['firstName'] = $data['firstname'];
                                $request['lastName']  = $data['lastname'];
                                $email = strtolower($data['email']);

                                $request['magentoID'] = $customer_id;

                                $cpCustNo = $customer->getCounterpointCustNo();
                                if(!empty($cpCustNo)){
                                    $request['customText4'] = $cpCustNo;
                                }

                                $customerNote = $customer->getCustNote();
                                if(!empty($customerNote)){
                                    $request['largeMemo'] = $customerNote;
                                }

                                if(!empty($data['email'])){
                                    $request['email1']    = (object) array(
                                        "email"=> $email,
                                        "acceptMarketing"=>true
                                    );
                                }

                                $taxVat = $customer->getTaxvat();
                                if(!empty($taxVat)){
                                    $request['VATRegistrationNumber'] = $taxVat;
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
                                    if($billingAddrData['telephone'] !="000-000-0000"){
                                        $request['phone2'] = (object) array("number"=>$billingAddrData['telephone']);
                                    }
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

                                  $custType = $customer->getCustomerType();
                                  $model->setCustomerId($customer_id)
                                        ->setEmail($email)
                                        ->setAutoGenBillId($guid1)
                                        ->setAutoGenShipId($guid2)
                                        ->setCounterpointCustNo($cpCustNo)
                                        ->setCustomerNote($customerNote)
                                        ->setIsCounterpointCust($custType);

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
                            }
                        }catch (Exception $e){
                            Mage::log("id-:".$customer->getId(). " email-:".$email." == Exception-:".$e->getMessage(),Zend_log::DEBUG,$teamwoek_log_file,true);
                        }
                     }
                     //set next number
                     $mLog->setPage($end)->save();
                }
            }catch (Exception $ee){
                Mage::log("Exc:".$ee->getMessage(),Zend_log::DEBUG,$teamwoek_log_file,true);
            }
       }
       Mage::log("Finish...",Zend_log::DEBUG,$teamwoek_log_file,true);
  }


  /**
   * sync customer from teamwork to magento
   * and vice versa
   */
  public function syncTeamworkCustomer(){
      $helper  = Mage::helper("allure_teamwork");
      $logFile = $helper::SYNC_TM_MAG_LOG_FILE;

      $alphabets = range('A','Z');
      $numbers = range('0','9');
      $additional_characters = array('#','@','$');

      $status = $helper->getTeamworkStatus();
      $logStatus = $helper->getLogStatus();
      $logStatus = ($logStatus)?true:false;
      if(!$status){
          Mage::log("teamwork customer sync status - ".$status,Zend_Log::DEBUG,$logFile,$logStatus);
          return;
      }

      try{
          $operation = "last_query_time";
          $mLog = Mage::getModel("allure_teamwork/log")
            ->load($operation,'operation');

          $lastQueryTime = (int) $mLog->getPage();//$helper->getLastSyncQueryTime();
          $syncURL   = $helper::SYNC_TEAMWORK_CUSTOMER_URLPATH;
          $pageLimit = (int) $helper->getTeamworkPageLimit();
          Mage::log("Teamwork sync start",Zend_log::DEBUG,$logFile,$logStatus);
          $teamworkClient = Mage::helper('allure_teamwork/teamworkClient');
          $request = array();
          $request['pageSize']      = $pageLimit;
          Mage::log($lastQueryTime,Zend_log::DEBUG,$logFile,$logStatus);
          $request['modifiedAfter'] = $lastQueryTime;
          $response     = $teamworkClient->send($syncURL,$request);
          $responseObj  = json_decode($response);

          $nextSyncTime = $responseObj->queryTimestamp;
          $mLog->setPage($nextSyncTime)->save();
          //Mage::getConfig()->saveConfig($helper::XML_NEXT_QUERY_SYNC_TIME, $nextSyncTime);

          Mage::log("Total-:".count($responseObj->entities),Zend_log::DEBUG,$logFile,$logStatus);

          if(count($responseObj->entities) > 0){
              foreach ($responseObj->entities as $customer){
                  try{
                      $email1 = $customer->email1;
                      if(!empty($email1)){
                          $email = $email1->email;
                          if(!empty($email)){
                              $customerObj = Mage::getModel('customer/customer')
                                ->setWebsiteId(0)
                                ->loadByEmail($email);
                              $teamworkId   = $customer->customerID;
                              $firstName    = $customer->firstName;
                              $lastName     = $customer->lastName;
                              $customerNote = $customer->largeMemo;
                              $isWolesale   = $customer->customFlag1;
                              $magentoId    = $customer->magentoID;
                              $taxVatId     = $customer->VATRegistrationNumber;

                              $groupId      = (!empty($isWolesale))?($isWolesale)?2:1:1;

                              if($customerObj->getId()){
                                  $customerObj->setCustNote($customerNote)
                                    ->setTeamworkCustomerId($teamworkId)
                                    ->save();
                                  Mage::log("upadte customer id:".$customerObj->getId()." email:".$email,Zend_log::DEBUG,$logFile,$logStatus);
                              }else{
                                  $final_array = array_merge($alphabets,$numbers,$additional_characters);
                                  $password = '';
                                  $length = 6;
                                  while($length--) {
                                      $keyV = array_rand($final_array);
                                      $password .= $final_array[$keyV];
                                  }

                                  $customerObj = Mage::getModel("customer/customer");
                                  $customerObj->setWebsiteId(1)
                                    ->setStoreId(1)
                                    ->setGroupId($groupId)
                                    ->setFirstname($firstName)
                                    ->setLastname($lastName)
                                    ->setEmail($email)
                                    ->setPassword($password)
                                    //synced teamwork new customer
                                    ->setCustomerType(7)
                                    ->setCustNote($customerNote)
                                    ->setTeamworkCustomerId($teamworkId)
                                    ->setTaxvat($taxVatId);

                                    if($email->acceptMarketing){
                                        $customerObj->setTwAcceptMarketing($email->acceptMarketing);
                                    }
                                    if($email->acceptTransactional){
                                        $customerObj->setTwAcceptTransactional($email->acceptTransactional);
                                    }

                                    $customerObj->save();
                                    Mage::log("new customer id:".$customerObj->getId()." email:".$email,Zend_log::DEBUG,$logFile,$logStatus);
                              }

                              //send magento id to teamwork
                              if(empty($magentoId)){
                                $magentoId  = $customerObj->getId();
                                $requestObj = array();
                                $requestObj['customerID'] = $teamworkId;
                                $requestObj['magentoID'] = $magentoId;
                                $updateURL = $helper::UPADTE_CUSTOMER_URLPATH;
                                $response1 = $teamworkClient->send($updateURL,$requestObj);
                                Mage::log("update request called for magento id:".$magentoId,Zend_log::DEBUG,$logFile,$logStatus);
                              }else{
                                Mage::log("magento id:".$magentoId." already present in teamwork",Zend_log::DEBUG,$logFile,$logStatus);
                              }

                          }else{
                              Mage::log("Customer email is empty",Zend_log::DEBUG,$logFile,$logStatus);
                          }
                      }else{
                          Mage::log("Can't create customer",Zend_log::DEBUG,$logFile,$logStatus);
                      }
                  }catch (Exception $e){
                      Mage::log("Excp :".$e->getMessage(),Zend_log::DEBUG,$logFile,$logStatus);
                  }
              }
          }else{
              Mage::log("No New Teamwork customer",Zend_log::DEBUG,$logFile,$logStatus);
          }
      }catch (Exception $e){
          Mage::log("Exc :".$e->getMessage(),Zend_log::DEBUG,$logFile,$logStatus);
      }
      Mage::log("Finish...",Zend_log::DEBUG,$logFile,$logStatus);
  }
  function changeOrderStatus($observer){
      $order = $observer->getEvent()->getOrder();

      Mage::log("changeOrderStatus:: ORDER #".$order->getIncrementId().", ORDER ID: ".$order->getIncrementId().", STATUS: ".$order->getStatus(),Zend_log::DEBUG,'change_status.log',true);

      if ($order->getStatus()=='processing'){
         try{
              $order=Mage::getModel('sales/order')->load($order->getId());
              $order->setStatus('in_chq', true)->save();
         } catch(Exception $e) {
             Mage::log("Order::".$order->getId()." Exception::".$e->getMessage(),Zend_log::DEBUG,'change_status.log',true);
         }
      }
  }


  /**
   * reupdate teamwork customer field with counterpoint customer
   */

  public function reupdateCustomerToTeamwork(){
      $logFile = "tewamwor_reupdate_customer.log";
      $operation = "reupdate_customer";

      try{
          $helper = Mage::helper("allure_teamwork");
          $_url   = $helper->getTeamworkUrl() . $helper::UPADTE_CUSTOMER_URLPATH;
          $_accessToken = $helper->getTeamworkAccessToken();

          $mLog = Mage::getModel("allure_teamwork/log")->load($operation,'operation');;
          $page = $mLog->getPage();
          $size = $mLog->getSize();
          // $total= $mLog->getTotal();
          $total=0;
          $collection = Mage::getModel('customer/customer')
          ->getCollection()
          ->addAttributeToSelect('*');
          // $collection->setOrder('entity_id', 'asc');
          // $collection->setCurPage($page);
          // $collection->setPageSize($size);
          $collection->addFieldToFilter('entity_id',array('gteq'=>$page))
          ->addFieldToFilter('entity_id',array('lteq'=>$size));
          $collection->setOrder('entity_id', 'asc');

          //  $lastPage = $collection->getLastPageNumber();
          if(true){
              foreach ($collection as $customer){
                  try{
                      $page=$customer->getEntityId();
                      if(empty($customer->getCounterpointCustNo()))
                          continue;
                          $request = array();
                          $request['customerID']  = $customer->getTeamworkCustomerId();
                          //  $request['primaryEmail']= array($customer->getEmail());
                          $request['customText4'] = $customer->getCounterpointCustNo();
                          //  $page=$customer->getEntityId();
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
                          $total=$total++;
                          $json_arguments = json_encode($request);
                          curl_setopt($sendRequest, CURLOPT_POSTFIELDS, $json_arguments);
                          $response = curl_exec($sendRequest);
                          curl_close($sendRequest);
                          $responseObj = json_decode($response);
                          Mage::log("Email-:".$customer->getEmail(),Zend_Log::DEBUG,$logFile,true);
                          Mage::log("ID-:".$customer->getId(),Zend_Log::DEBUG,$logFile,true);
                          // Mage::log("Response -:".$response,Zend_Log::DEBUG,$logFile,true);
                  }catch (Exception $ee){
                      Mage::log("Customer Id-:".$customer->getId(),Zend_Log::DEBUG,$logFile,true);
                      Mage::log("EXC:".$ee->getMessage(),Zend_Log::DEBUG,$logFile,true);
                  }
              }
          }

          $page=$size;
          $size=$page+250;
          $resource = Mage::getSingleton('core/resource');
          $writeAdapter = $resource->getConnection('core_write');
          $table = $resource->getTableName('allure_teamwork_log_table');
          $query="";
          $query = "update {$table} set  page = '{$page}',size = '{$size}' where id = 4";
          $writeAdapter->query($query);

      }catch (Exception $e){
          Mage::log("Exception:".$e->getMessage(),Zend_Log::DEBUG,$logFile,true);
      }
      //  $this->reupdateCustomerToTeamwork();
  }

  //remove salesrule id from quote for teamwork order
  public function removeSalesRuleForTeamworkOrder($observer){
      $event = $observer->getEvent();
      $quote = $event->getQuote();
      $appliedRule = $event->getRule();
      $result = $event->getResult();
      $method = $quote->getCreateOrderMethod();
      if( $method == 2 || $method == 4){
          $result->setDiscountAmount(0);
          $result->setBaseDiscountAmount(0);
          $quote->setAppliedRuleIds(null);
      }
      return $this;
  }

}
