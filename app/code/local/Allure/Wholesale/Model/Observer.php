<?php
/**
 * Created by allure.
 * User: adityagatare
 * Date: 13/11/18
 * Time: 11:17 PM
 */

class Allure_Wholesale_Model_Observer
{
 public function checkUserIsWholesale()
 {
     if(!$this->helper()->getStatus())
         return;

//     Mage::log($this->isWholeSaleLoginPage(),Zend_Log::DEBUG,"adi.log",true);

     if($this->isWholesaleCustomer() &&  $this->getCurrentStoreId()!=$this->helper()->getStoreId())
    {
        Mage::getSingleton('customer/session')->logout();
//        Mage::app()->getResponse()->setRedirect($this->getStoreUrl($this->helper()->getStoreId()));
        return;
    }

     if(!Mage::getSingleton('customer/session')->isLoggedIn() &&  $this->getCurrentStoreId()==$this->helper()->getStoreId())
     {
        if(!$this->isWholeSaleLoginPage())
            Mage::app()->getResponse()->setRedirect($this->getStoreUrl($this->helper()->getStoreId())."customer/account/login/");

         return;
     }

     if(!$this->isWholeSaleLoginPage() &&  $this->getCurrentStoreId()==$this->helper()->getStoreId())
     {
         if(!$this->isWholeSaleLoginPage()) {
             Mage::getSingleton('customer/session')->logout();
             Mage::app()->getResponse()->setRedirect($this->getStoreUrl(1));
         }
         return;
     }



     Mage::log($this->helper()->getStoreId(),Zend_Log::DEBUG,"adi.log",true);


     // Mage::app()->getResponse()->setRedirect(Mage::getUrl("checkout/cart"));

//     Mage::log(Mage::app()->getStore($store->getId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK),Zend_Log::DEBUG,"adi.log",true);

     if($this->isWholesaleCustomer())
    {
    // ;
    }

 }
 public function isWholesaleCustomer()
 {
        if(Mage::getSingleton('customer/session')->isLoggedIn()){
            $groupId    = Mage::getSingleton('customer/session')->getCustomerGroupId();
            $group      = Mage::getModel('customer/group')->load($groupId);

            if(strtolower($group->getCode())=="wholesale")
                return true;
        }
     return false;
 }
 private function getCurrentStoreId()
 {
     $store = Mage::app()->getStore();
     return $store->getId();
 }
 private function helper()
 {
     return Mage::helper("wholesale");
 }
 private function getStoreUrl($storeId)
 {
     return Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
 }
 private function isWholeSaleLoginPage()
 {
     if(Mage::app()->getRequest()->getControllerName()=="account" && Mage::app()->getRequest()->getActionName()=="login" && Mage::app()->getRequest()->getModuleName()=="customer")
         return true;

     return false;
 }
}