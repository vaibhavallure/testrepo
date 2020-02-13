<?php
class Allure_BackorderRecord_Helper_Config extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ALERT_SENDER_EMAIL   = 'backorderrecord/alr_backorderrecord/sender_email';
    const XML_PATH_ALERT_SENDER_NAME    = 'backorderrecord/alr_backorderrecord/sender_name';
	const XML_PATH_EMAIL_STATUS   		= 'backorderrecord/email_group/enabled';
	const XML_PATH_STORES   		= 'backorderrecord/email_group/enable_stores';
	const XML_PATH_GROUP_EMAILS  		= 'backorderrecord/email_group/group_emails';
	const XML_PATH_GROUP_EMAILS_NAMES  	= 'backorderrecord/email_group/group_names';
	const XML_PATH_EMAIL_TEMPLATE   = 'backorderrecord/email_group/email_temp';
    const XML_PATH_NUMBER_DAYS   = 'backorderrecord/email_group/days';
    const XML_PATH_DEBUG_STATUS   		= 'backorderrecord/email_group/debug_enabled';


    public function getSenderEmail(){
        return Mage::getStoreConfig(self::XML_PATH_ALERT_SENDER_EMAIL);
    }

    public function getSenderName(){
        return Mage::getStoreConfig(self::XML_PATH_ALERT_SENDER_NAME);
    }

    public function getEmailStatus(){
    	return Mage::getStoreConfig(self::XML_PATH_EMAIL_STATUS);
    }

    public function getDebugStatus(){
        return Mage::getStoreConfig(self::XML_PATH_DEBUG_STATUS);
    }


    public function getEmailsGroup(){
    	return Mage::getStoreConfig(self::XML_PATH_GROUP_EMAILS);
    }

    public function getEmailGroupNames(){
    	return Mage::getStoreConfig(self::XML_PATH_GROUP_EMAILS_NAMES);
    }

    public function getEmailTemplate(){
    	return Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE);
    }


    public function getStores(){
        return Mage::getStoreConfig(self::XML_PATH_STORES);
    }


}