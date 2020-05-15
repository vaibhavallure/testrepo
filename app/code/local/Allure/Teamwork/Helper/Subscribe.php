<?php
/**
 * MT-1430 : Subscribe and Unsubscribe function for teamwork customer
 */

class Allure_Teamwork_Helper_Subscribe extends Mage_Core_Helper_Data
{
    private  $_customerEmail = '';
    private $_customerMarketingFlag = 0;
    private $_customerId;
    const STATUS_SUBSCRIBED     = 1;
    const STATUS_NOT_ACTIVE     = 2;
    const STATUS_UNSUBSCRIBED   = 3;
    const STATUS_UNCONFIRMED    = 4;
    private function subscribeModel(){
        return Mage::getModel('newsletter/subscriber');
    }
    private function customerModel(){
        return Mage::getModel('customer/customer');
    }
    public function writeLog($message){
        Mage::log($message,Zend_Log::DEBUG,'subscribe_log.log',true);
    }
    private function isSubscribed(){
        $subscriber = $this->subscribeModel()->loadByEmail($this->_customerEmail);

        if($subscriber->getId()){
            $this->writeLog('Already Subscribed :' . $this->_customerEmail);
            $this->writeLog('Status :' . $subscriber->getStatus());
            if($subscriber->getStatus() !== self::STATUS_SUBSCRIBED){
                return false;
            }else {
                return true;
            }
        }
        return false;
    }
    private function subscribe(){
        try{
            $this->subscribeModel()->subscribe($this->_customerEmail);
            $subscriber =  $this->subscribeModel()->loadByEmail($this->_customerEmail);
            $subscriber->setCustomerId($this->_customerId);
            $subscriber->save();
            $this->writeLog('Subscribed Successfully : '.$this->_customerEmail);
        }catch (Exception $ex){
            $this->writeLog('Exception while subscribing :'.$ex->getMessage());
        }
    }
    private function unsubscribe(){
        if($this->isSubscribed()){
            try {
                $this->subscribeModel()->loadByEmail($this->_customerEmail)->unsubscribe();
                $this->writeLog('Unsubscribed Successfully : '.$this->_customerEmail);
            }
            catch (Exception $ex){
                $this->writeLog('Exception while unsubscribing :'.$ex->getMessage());
            }
        }
    }
    public function setCustomerData($customerMarkentingFlag,$customerEmail){
        $this->writeLog('------------ Customer Data -------------');
        $this->writeLog('Email:'.$customerEmail);
        $this->writeLog('Marketing Flag:'.$customerMarkentingFlag);
        $this->_customerEmail = $customerEmail;
        $this->_customerMarketingFlag = $customerMarkentingFlag;
    }
    public function doSubscriptionOperations(){

        $customer = $this->customerModel()->loadByEmail($this->_customerEmail);
        if($customer->getId()){
            $this->_customerId = $customer->getId();
            if(!$this->isSubscribed() && $this->_customerMarketingFlag){
                $this->subscribe();
            }elseif(!$this->_customerMarketingFlag){
                $this->unsubscribe();
            }
        }else{
            $this->writeLog('Customer Not Found :'.$this->_customerEmail);
        }
    }
    public function hello(){
        echo "Hello";
    }
}