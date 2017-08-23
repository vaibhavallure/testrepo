<?php
/*
 *  Get List of customer's from sugarsrm &
 *  add into magento.
 *
 */

require_once('../app/Mage.php');
umask(0);
Mage::app();

die;
$helper = Mage::helper('allure_counterpoint/sugarcrmClient');

$oauth_token = $helper->login();

$offset =  0;
while($offset<=200){
    $paramArgs = array('max_num'=>'100','offset'=>$offset);
    $requestURL = "https://mariatash.sugarondemand.com/rest/v10/Contacts/filter";
    $filter_response = $helper->
                sendRequest($requestURL, $paramArgs, $is_auth,$oauth_token,"GET");
    echo "<pre>";
    $arr = json_decode($filter_response,true);
    $offset = $arr['next_offset'];
    print_r(json_encode($arr));
}

function createCustomer($data){
    //random string 6 characters for passwoard generation
    $alphabets = range('A','Z');
    $numbers = range('0','9');
    $additional_characters = array('#','@','$');
    $final_array = array_merge($alphabets,$numbers,$additional_characters);
    
    $websiteId = 1;
    $store = 1;
    
    try{
        $email = "sagar91@allureinc.co";$data['email1'];
        
        $customer = Mage::getModel("customer/customer")->loadByEmail($email);
        if($customer->getId()){
            //customer already present
            Mage::log("Customer Present in Magento:".$customer->getId(),Zend_log::DEBUG,'crm_customer',true);
        }else {
            Mage::log("Customer Not Present in Magento ",Zend_log::DEBUG,'crm_customer',true);
            $firstname = $data['first_name'];
            $lastname = $data['last_name'];
            $groupId = 1;
            $password = '';
            $length = 6;  //password length
            while($length--) {
                $key = array_rand($final_array);
                $password .= $final_array[$key];
            }
            
            try{
                $customer = Mage::getModel("customer/customer");
                $customer->setWebsiteId($websiteId)
                    ->setStoreId($store)
                    ->setGroupId($groupId)
                    ->setFirstname($firstname)
                    ->setLastname($lastname)
                    ->setEmail($email)
                    ->setPassword($password)
                    ->setCustomerType(2)  //counterpoint
                    ->save();
                
                Mage::log("New Customer Create From CRM - Custsomer Id-:".$customer->getId(),Zend_log::DEBUG,'crm_customer',true);
                echo "customer create";
                //create customer address
                if(!empty($data['primary_address_street'])){
                    $_custom_address = array (
                        'firstname'  => $customer->getFirstname(),
                        'lastname'   => $customer->getLastname(),
                        'street'     => array (
                            '0' => $data['primary_address_street'],
                            '1' => $data['primary_address_street_2']
                        ),
                        'city'       => $data['primary_address_city'],
                        'postcode'   => $data['primary_address_postalcode'],
                        'country_id' => $data['primary_address_country'],
                        'region' 	=> 	$data['primary_address_state'],
                        //'telephone'  => $data['phone_1'],
                       /// 'fax'        => $data['fax_1'],
                    );
                    
                    $address = Mage::getModel("customer/address");
                    $address->setData($_custom_address)
                        ->setCustomerId($customer->getId())
                        ->setIsDefaultBilling('1')
                        ->setIsDefaultShipping('1')
                        ->setSaveInAddressBook('1');
                    $address->save();
                    Mage::log("New Customer has add address.customer id:".$customer->getId()." address id:".$address->getId(),Zend_log::DEBUG,'crm_customer',true);
                }
            
            }catch (Exception $e){
                Mage::log("Exc: ".$e->getMessage(),Zend_log::DEBUG,'crm_customer',true);
            }
        }
    }catch (Exception $e){
        Mage::log("Exception: ".$e->getMessage(),Zend_log::DEBUG,'crm_customer',true);
    }
}

die('Finish...');





