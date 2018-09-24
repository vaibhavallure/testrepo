<?php
class Allure_AlertServices_Helper_Config extends Mage_Core_Helper_Abstract
{
	const XML_PATH_EMAIL_STATUS   		= 'allure_alert/email_group/enabled';
	const XML_PATH_GROUP_EMAILS  		= 'allure_alert/email_group/group_emails';
	const XML_PATH_GROUP_EMAILS_NAMES  	= 'allure_alert/email_group/group_names';
	const XML_PATH_PRODUCT_PRICE_TEMPLATE   = 'allure_alert/email_group/product_price';
	const XML_PATH_SALE_TEMPLATE	= 'allure_alert/email_group/salealert';
	const XML_PATH_CHECKOUT_ISSUE_TEMPLATE = 'allure_alert/email_group/checkout_issue';
    const XML_PATH_NULL_USERS_TEMPLATE = 'allure_alert/email_group/useralert';
    const XML_PATH_PAGE_NOT_FOUND_TEMPLATE = 'allure_alert/email_group/pnfalert';
    const XML_PATH_PAGE_LOAD_TEMPLATE = 'allure_alert/email_group/pageloadalert';
    const XML_PATH_GOOGLE_ANALYTICS_JSON = 'allure_alert/alr_analytics/alr_client_json';

    public function getEmailStatus(){
    	return Mage::getStoreConfig(self::XML_PATH_EMAIL_STATUS);
    }

    public function getEmailsGroup(){
    	return Mage::getStoreConfig(self::XML_PATH_GROUP_EMAILS);
    }

    public function getEmailGroupNames(){
    	return Mage::getStoreConfig(self::XML_PATH_GROUP_EMAILS_NAMES);
    }

    public function getProductPriceEmailTemplate(){
    	return Mage::getStoreConfig(self::XML_PATH_PRODUCT_PRICE_TEMPLATE);
    }

    public function getSaleEmailTemplate(){
    	return Mage::getStoreConfig(self::XML_PATH_SALE_TEMPLATE);
    }

    public function getCheckoutIssueEmailTemplate(){
    	return Mage::getStoreConfig(self::XML_PATH_CHECKOUT_ISSUE_TEMPLATE);
    }

    public function getNullUsersEmailTemplate(){
        return Mage::getStoreConfig(self::XML_PATH_NULL_USERS_TEMPLATE);
    }

    public function getPageNotFoundEmailTemplate(){
        return Mage::getStoreConfig(self::XML_PATH_PAGE_NOT_FOUND_TEMPLATE);
    }

    public function getPageLoadEmailTemplate(){
        return Mage::getStoreConfig(self::XML_PATH_PAGE_LOAD_TEMPLATE);
    }
    
    public function getGoogleAnayticsJson(){
        return Mage::getStoreConfig(self::XML_PATH_GOOGLE_ANALYTICS_JSON);
    }

}