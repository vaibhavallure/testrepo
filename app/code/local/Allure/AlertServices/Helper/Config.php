<?php
class Allure_AlertServices_Helper_Config extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ALERT_SENDER_EMAIL   = 'allure_alert/alr_alert/sender_email';
    const XML_PATH_ALERT_SENDER_NAME    = 'allure_alert/alr_alert/sender_name';
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
    const XML_PATH_AVG_PAGE_LOAD = 'allure_alert/alr_analytics/avg_page_load';
    const XML_PATH_ALR_ENABLED = 'allure_alert/alr_analytics/alr_enabled';
    const XML_PATH_PRODUCT_PRICE_ENABLED='allure_alert/email_group/enabled_product_price';
    const XML_PATH_SALES_ENABLED    =   'allure_alert/email_group/enabled_sales';
    const XML_PATH_CHECKOUT_ISSUES_ENABLED = 'allure_alert/email_group/enabled_checkout';
    const XML_PATH_PAGE_LOAD_ENABLED  = 'allure_alert/email_group/enabled_pageloadalert';
    const XML_PATH_ALERT_DEBUG_ENABLED = 'allure_alert/email_group/enabled_debug_alert';

    const XML_PATH_TEST_EMAILS_ENABLED ='allure_alert/alr_alert_test/test_emails_enable';
    const XML_PATH_GROUP_TEST_EMAILS   ='allure_alert/alr_alert_test/group_test_emails';
    const XML_PATH_GROUP_TEST_EMAILS_NAMES = 'allure_alert/alr_alert_test/group_test_names';
    /*INSTAGRAM ALERT SETTINGS*/
    const XML_PATH_INSTA_ENABLE = 'allure_alert/alr_instatoken/alr_enabled';
    const XML_PATH_INSTA_EMAIL_TEMPLATE = 'allure_alert/alr_instatoken/instatoken_template';
    const XML_PATH_INSTA_EMAIL_ENABLE = 'allure_alert/alr_instatoken/enabled_instatoken_email';
    const XML_PATH_INSTA_GROUP_EMAILS  		= 'allure_alert/alr_instatoken/insta_group_emails';
    const XML_PATH_INSTA_GROUP_EMAILS_NAMES  	= 'allure_alert/alr_instatoken/insta_group_names';
    public function getAlertDebugStatus(){
        return Mage::getStoreConfig(self::XML_PATH_ALERT_DEBUG_ENABLED);
    }

    public function getProductPriceStatus(){
        return Mage::getStoreConfig(self::XML_PATH_PRODUCT_PRICE_ENABLED);
    }

    public function getSalesStatus(){
        return Mage::getStoreConfig(self::XML_PATH_SALES_ENABLED);
    }
    public function getCheckoutIssuesStatus(){
        return Mage::getStoreConfig(self::XML_PATH_CHECKOUT_ISSUES_ENABLED);
    }
    public function getPageLoadStatus(){
        return Mage::getStoreConfig(self::XML_PATH_PAGE_LOAD_ENABLED);
    }


    public function getAlertSenderEmail(){
        return Mage::getStoreConfig(self::XML_PATH_ALERT_SENDER_EMAIL);
    }

    public function getAlertSenderName(){
        return Mage::getStoreConfig(self::XML_PATH_ALERT_SENDER_NAME);
    }

    public function getEmailStatus(){
    	return Mage::getStoreConfig(self::XML_PATH_EMAIL_STATUS);
    }

    public function getAlrStatus(){
        return Mage::getStoreConfig(self::XML_PATH_ALR_ENABLED);
    }

    public function getEmailsGroup(){
    	return Mage::getStoreConfig(self::XML_PATH_GROUP_EMAILS);
    }

    public function getEmailGroupNames(){
    	return Mage::getStoreConfig(self::XML_PATH_GROUP_EMAILS_NAMES);
    }

    public function getTestEmailStatus(){
        return Mage::getStoreConfig(self::XML_PATH_TEST_EMAILS_ENABLED);
    }
    
    public function getTestEmailsGroup(){
        return Mage::getStoreConfig(self::XML_PATH_GROUP_TEST_EMAILS);
    }

    public function getTestEmailGroupNames(){
        return Mage::getStoreConfig(self::XML_PATH_GROUP_TEST_EMAILS_NAMES);
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

    public function getAvgLoadTimeArray(){
        return Mage::getStoreConfig(self::XML_PATH_AVG_PAGE_LOAD);
    }

    public function getAvgLoadTimePath(){
        return self::XML_PATH_AVG_PAGE_LOAD;
    }
    public function getInstagramTokenStatus(){
        return Mage::getStoreConfig(self::XML_PATH_INSTA_ENABLE);
    }
    public function getInstagramTokenEmailStatus(){
        return Mage::getStoreConfig(self::XML_PATH_INSTA_EMAIL_ENABLE);
    }
    public function getInstagramTokenEmailTemplate(){
        return Mage::getStoreConfig(self::XML_PATH_INSTA_EMAIL_TEMPLATE);
    }
    public function getInstaEmailsGroup(){
        return Mage::getStoreConfig(self::XML_PATH_INSTA_GROUP_EMAILS);
    }

    public function getInstaEmailGroupNames(){
        return Mage::getStoreConfig(self::XML_PATH_INSTA_GROUP_EMAILS_NAMES);
    }
}