<?php
class Allure_AlertServices_Helper_Config extends Mage_Core_Helper_Abstract
{
	const XML_PATH_EMAIL_STATUS   		= 'allure_alert/email_group/enabled';
	const XML_PATH_GROUP_EMAILS  		= 'allure_alert/email_group/group_emails';
	const XML_PATH_GROUP_EMAILS_NAMES  	= 'allure_alert/email_group/group_names';
	const XML_PATH_PRODUCT_PRICE_TEMPLATE   = 'allure_alert/email_group/product_price';
	const XML_PATH_SALE_TEMPLATE	= 'allure_alert/email_group/sale_alert';
	const XML_PATH_CHECKOUT_ISSUE_TEMPLATE = 'allure_alert/email_group/checkout_issue';

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

}