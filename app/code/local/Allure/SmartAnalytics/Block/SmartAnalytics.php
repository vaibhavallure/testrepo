<?php
class Allure_SmartAnalytics_Block_SmartAnalytics extends Mage_Core_Block_Template
{
    public function getAccountId()
    {
        return Mage::getStoreConfig('allure_smartanalytics/general/account_id');
    }
    public function isAnon()
    {
        if(Mage::getStoreConfigFlag('allure_smartanalytics/support/AnonIP')){
            return true;
        }
        return false;
    }
    public function isUserOptOutEnable(){
        if(Mage::getStoreConfigFlag('allure_smartanalytics/support/OptOut')){
            return true;
        }
        return false;
    }
	public function isActive()
    {
        if(Mage::getStoreConfigFlag('allure_smartanalytics/general/enable')
            ){
                return true;
        }
        return false;
    }
	public function getBrandAttr(){

		return Mage::getStoreConfig('allure_smartanalytics/ecommerce/brand') != "" ? Mage::getStoreConfig('allure_smartanalytics/ecommerce/brand') : "";
	}
    public function isEcommerce()
    {
        $successPath =  Mage::getStoreConfig('allure_smartanalytics/ecommerce/success_url') != "" ? Mage::getStoreConfig('allure_smartanalytics/ecommerce/success_url') : '/checkout/onepage/success';
        if(Mage::getStoreConfigFlag('allure_smartanalytics/general/enable')
            && strpos($this->getRequest()->getPathInfo(), $successPath) !== false){
                return true;
        }
        return false;
    }
    public function isCheckout()
    {
        $checkoutPath =  Mage::getStoreConfig('allure_smartanalytics/ecommerce/checkout_url') != "" ?  Mage::getStoreConfig('allure_smartanalytics/ecommerce/checkout_url') : '/checkout/onepage';
        if(Mage::getStoreConfigFlag('allure_smartanalytics/general/enable')
            && strpos($this->getRequest()->getPathInfo(), $checkoutPath) !== false){
            return true;
        }
        return false;
    }
    public function getTransactionIdField()
    {
        return 'entity_id';
    }
    public function getProduct()
    {
        return Mage::registry('current_product');
    }
    public function getHomeId()
    {
        return Mage::getStoreConfig('allure_smartanalytics/ecommerce/home_id') != '' ? Mage::getStoreConfig('allure_smartanalytics/ecommerce/home_id') : '';
    }
}
