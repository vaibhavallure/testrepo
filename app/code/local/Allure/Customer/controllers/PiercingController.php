<?php
/**
 * 
 * @author aws02
 *
 */
class Allure_Customer_PiercingController extends Mage_Core_Controller_Front_Action{
    
    const GENERAL_CUSTOMER = 1;
    const MAIN_WEBSITE_ID = 1;
    const STORE_ID = 1;
    
    public function indexAction(){
        die("Hiii");
    }
    
    /**
     * add customer into magento through
     * piercing release-form interface
     */
    public function addCustomerAction(){
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers:*");
        $requestData = $this->getRequest()->getParams();
        Mage::log("--------- Request Paramas ------",Zend_log::DEBUG,'release_form.log',true);
        Mage::log($requestData,Zend_log::DEBUG,'release_form.log',true);
        $response = array("success" => false);
        try{
            if(isset($requestData["email"]) && !empty($requestData["email"])){
                $email = $requestData["email"];
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(self::MAIN_WEBSITE_ID)
                    ->loadByEmail($email);
                if(!$customer->getId()){
                    $password = $this->generatePassword();
                    $customer = Mage::getModel("customer/customer");
                    $customer->setWebsiteId(self::MAIN_WEBSITE_ID)
                        ->setStoreId(self::STORE_ID)
                        ->setGroupId(self::GENERAL_CUSTOMER)
                        ->setFirstname($requestData["firstname"])
                        ->setLastname($requestData["lastname"])
                        ->setEmail($email)
                        ->setPassword($password)
                        ->setPasswordConfirmation($password)
                        ->setPasswordCreatedAt(time())
                        ->setCustomerType(25)   //release-form customer
                        ->save();
                    
                        $customer->sendNewAccountEmail();
                    
                    $response["success"] = true;
                    $response["message"] = $email . " has account created successfully.";
                    Mage::log($email . " has account created successfully.",Zend_log::DEBUG,'release_form.log',true);
                }else{
                    $response["success"] = true;
                    $response["message"] = $email . " customer already exists.";
                    Mage::log($email . " customer already exists.",Zend_log::DEBUG,'release_form.log',true);
                    
                }
                
                $response["customer_id"] = $customer->getId();
            }else{
                $response["message"] = "Customer request body empty.";
            }
        }catch (Exception $e){
            Mage::log("Exception : ".$e->getMessage(),Zend_log::DEBUG,'release_form.log',true);
            $response["message"] = $e->getMessage();
        }
       
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }
    
    /**
     *  generate dynamic password for new customer's 
     *  that created from release-form
     *  @return string
     */
    private function generatePassword(){
        $alphabets = range('A','Z');
        $numbers = range('0','9');
        $additional_characters = array('#','@','$');
        $final_array = array_merge($alphabets,$numbers,$additional_characters);
        $password = '';
        $length = 8; //password length
        while($length--) {
            $keyV = array_rand($final_array);
            $password .= $final_array[$keyV];
        }
        return $password;
    }
    /**
    Add customer address details to magento which came from release form
     **/
    public function addCustomerAddressAction(){
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers:*");
        $requestData = $this->getRequest()->getParams();
        Mage::log("--------- Request Paramas Save Address ------",Zend_log::DEBUG,'release_form.log',true);
        Mage::log($requestData,Zend_log::DEBUG,'release_form.log',true);
        $response = array("success" => false);
        try {

            $email = $requestData['email'];
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(self::MAIN_WEBSITE_ID)
                ->loadByEmail($email);
            if(!$customer->getId()) {
                $password = $this->generatePassword();
                $customer = Mage::getModel("customer/customer");
                $customer->setWebsiteId(self::MAIN_WEBSITE_ID)
                    ->setStoreId(self::STORE_ID)
                    ->setGroupId(self::GENERAL_CUSTOMER)
                    ->setFirstname($requestData["firstname"])
                    ->setLastname($requestData["lastname"])
                    ->setDob($requestData['dob'])
                    ->setEmail($email)
                    ->setPassword($password)
                    ->setPasswordConfirmation($password)
                    ->setPasswordCreatedAt(time())
                    ->setCustomerType(25)   //release-form customer
                    ->save();

                $customer->sendNewAccountEmail();
            }
            else{
                $customer->setDob($requestData['dob']);
                if(!empty($requestData['gender']) ) {
                    $customer->setGender($requestData['gender']);
                }
                $customer->save();
            }

            /*Add Address Details*/
            if($customer->getId()) {
                $address = Mage::getModel("customer/address");
                $address->setCustomerId($customer->getId())
                    ->setFirstname($customer->getFirstname())
                    ->setLastname($customer->getLastname())
                    ->setCountryId($requestData['country_code'])
                    ->setRegion($requestData['region'])
                    ->setPostcode($requestData['zip_code'])
                    ->setCity($requestData['city'])
                    ->setTelephone($requestData['telephone'])
                    ->setStreet($requestData['street_address'])
                    ->setSaveInAddressBook('1');
                $address->save();
                $response["success"] = true;
                $response["message"] = $email . "Address saved successfully";
            }
        }
        catch (Exception $e){
            Mage::log("Exception : ".$e->getMessage(),Zend_log::DEBUG,'release_form.log',true);
            $response["message"] = $e->getMessage();
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

}