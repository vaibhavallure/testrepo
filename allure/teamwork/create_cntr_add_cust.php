<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$logFile = "cntr_create_cust_in_mag.log";
$page = $_GET['page'];
$size = $_GET['size'];
if(empty($page)){
    die("please specify page");
}


if(empty($size)){
    $size   = 100;
}


try{
    $cnt    = 0;
    
    $resource       = Mage::getSingleton('core/resource');
    $writeAdapter   = $resource->getConnection('core_write');
    $writeAdapter->beginTransaction();
    
    $collection = Mage::getModel("allure_teamwork/cpcustomer")->getCollection();
    $collection->setCurPage($page);
    $collection->setPageSize($size);
    $collection->setOrder('id', 'asc');
    
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
                     
                     try{
                         $customerObj->setCustNote($custNote);
                         if(!$customerObj->getCustomerType() ){
                             $customerObj->setCustomerType(6);   //magento cust
                         }
                         $customerObj->setCounterpointCustNo($custNo);
                         $customerObj->setCustNote($custNote);
                         $customerObj->setTempEmail($email);
                         $customerObj->save();
                         Mage::log($cnt." update customer_id:".$customerId,Zend_log::DEBUG,$logFile,true);
                     }catch (Exception $ee){
                         Mage::log("excep:".$ee->getMessage()." customer_id:".$customerId,Zend_log::DEBUG,$logFile,true);
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
    $writeAdapter->commit();
}catch (Exception $e){
    Mage::log("Exception:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
}
Mage::log("Finish...",Zend_log::DEBUG,$logFile,true);
