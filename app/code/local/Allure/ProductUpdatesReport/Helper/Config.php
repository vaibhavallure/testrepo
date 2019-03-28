<?php
class Allure_ProductUpdatesReport_Helper_Config extends Mage_Core_Helper_Abstract
{
    const XML_PATH_PRODUCT_UPDATE_SENDER_EMAIL = 'productupdatereport/alr_productupdate/sender_email';
    const XML_PATH_PRODUCT_UPDATE_SENDER_NAME  = 'productupdatereport/alr_productupdate/sender_name';
	const XML_PATH_EMAIL_STATUS = 'productupdatereport/email_group/enabled';
    const XML_PATH_REPORT_DAYS = 'productupdatereport/email_group/report_days';    
	const XML_PATH_GROUP_EMAILS = 'productupdatereport/email_group/group_emails';
	const XML_PATH_GROUP_EMAILS_NAMES = 'productupdatereport/email_group/group_names';
	const XML_PATH_PRODUCT_UPDATE_TEMPLATE = 'productupdatereport/email_group/product_update_temp';
    const XML_PATH_ALERT_DEBUG_ENABLED = 'productupdatereport/email_group/debug_enabled';

    
    public function getProductUpdateSenderEmail(){
        return Mage::getStoreConfig(self::XML_PATH_PRODUCT_UPDATE_SENDER_EMAIL);
    }

    public function getProductUpdateSenderName(){
        return Mage::getStoreConfig(self::XML_PATH_PRODUCT_UPDATE_SENDER_NAME);
    }

    public function getProductUpdateEmailStatus(){
        return Mage::getStoreConfig(self::XML_PATH_EMAIL_STATUS);
    }

    public function getReportDays(){
        return Mage::getStoreConfig(self::XML_PATH_REPORT_DAYS);
    }

    public function getProductUpdateEmailsGroup(){
        return Mage::getStoreConfig(self::XML_PATH_GROUP_EMAILS);
    }

    public function getProductUpdateEmailGroupNames(){
        return Mage::getStoreConfig(self::XML_PATH_GROUP_EMAILS_NAMES);
    }

    public function getProductUpdateEmailTemplate(){
        return Mage::getStoreConfig(self::XML_PATH_PRODUCT_UPDATE_TEMPLATE);
    }

    public function getProductUpdateDebugStatus(){
        return Mage::getStoreConfig(self::XML_PATH_ALERT_DEBUG_ENABLED);
    }


}