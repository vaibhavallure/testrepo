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


     /*if wholesale customer logged in and store is not equal to wholesale store
     * redirect to wholesale store
     */
     if($this->isWholesaleCustomer() &&  $this->getCurrentStoreId()!=$this->helper()->getStoreId())
    {
        Mage::getSingleton('customer/session')->logout();
        Mage::app()->getResponse()->setRedirect($this->getStoreUrl($this->helper()->getStoreId()));
        return;
    }

     /*if customer not logged in and store equal to wholesale store
     * redirect wholesale store to login page
     */
     if(!Mage::getSingleton('customer/session')->isLoggedIn() &&  $this->getCurrentStoreId()==$this->helper()->getStoreId())
     {
        if(!$this->isWholeSaleLoginPage())
            Mage::app()->getResponse()->setRedirect($this->getStoreUrl($this->helper()->getStoreId())."customer/account");

         return;
     }

     /*if retail customer logged in and store is equal to wholesale store
     * redirect to wholesale store login page
     */
     if(Mage::getSingleton('customer/session')->isLoggedIn() && !$this->isWholesaleCustomer() && !$this->isWholeSaleLoginPage() &&  $this->getCurrentStoreId()==$this->helper()->getStoreId())
     {
         Mage::getSingleton('customer/session')->logout();
         Mage::app()->getResponse()->setRedirect($this->getStoreUrl($this->helper()->getStoreId())."customer/account");
         return;
     }



     Mage::log($this->helper()->getStoreId(),Zend_Log::DEBUG,"adi.log",true);


 }

 public function afterLoginCheckUser($observer)
 {
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