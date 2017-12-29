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
                            if(!empty($data['email'])){
                                $request['email1']    = (object) array("email"=> $data['email']);
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
                                ->setEmail($data['email'])
                                ->setAutoGenBillId($guid1)
                                ->setAutoGenShipId($guid2);
                            
                            if(!$responseObj->errorCode){
                                $teamworkCustomerId = $responseObj->customer->customerID;
                                $customerObj = Mage::getModel("customer/customer")->load($customer_id);
                                $customerObj->setTeamworkCustomerId($teamworkCustomerId);
                                $customerObj->save();
                                
                                $model->setResponse($response)
                                    ->setTeamworkCustomerId($teamworkCustomerId);
                                
                                Mage::log("id-:".$customer->getId()." email-:".$data['email']." == teamwork_id-:".$teamworkCustomerId,Zend_log::DEBUG,$teamwoek_log_file,true);
                            }
                            else {
                                $model->setIsError(1)
                                ->setResponse($response);
                                Mage::log("id-:".$customer->getId()." email-:".$data['email']." == error-:".$response,Zend_log::DEBUG,$teamwoek_log_file,true);
                            }
                            
                            $model->save();
                            $model = null;
                        }catch (Exception $e){
                            Mage::log("id-:".$customer->getId(). " email-:".$data['email']." == Exception-:".$e->getMessage(),Zend_log::DEBUG,$teamwoek_log_file,true);
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
}
