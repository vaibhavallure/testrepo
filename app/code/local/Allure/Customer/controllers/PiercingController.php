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
                    
                    $response["success"] = true;
                    $response["message"] = $email . " has account created successfully.";
                }else{
                    $response["success"] = true;
                    $response["message"] = $email . " customer already exists.";
                }
                
                $response["customer_id"] = $customer->getId();
            }else{
                $response["message"] = "Customer request body empty.";
            }
        }catch (Exception $e){
            Mage::log("Exception : ".$e->getMessage(),Zend_log::DEBUG,'release_form.log',true);
            $response["message"] = $e->getMessage();
        }
       /*  echo "<pre>";
        print_r($response);
        die; */
        Mage::log($response,Zend_log::DEBUG,'release_form.log',true);
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
}