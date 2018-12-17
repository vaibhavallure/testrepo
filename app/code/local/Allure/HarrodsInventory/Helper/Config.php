<?php
class Allure_HarrodsInventory_Helper_Config extends Mage_Core_Helper_Abstract
{

    const XML_PATH_SENDER_EMAIL = 'harrodsinventory/alr_harrodsinventory/sender_email';
    const XML_PATH_SENDER_NAME  = 'harrodsinventory/alr_harrodsinventory/sender_name';
	const XML_PATH_EMAIL_STATUS = 'harrodsinventory/email_group/enabled';
	const XML_PATH_GROUP_EMAILS = 'harrodsinventory/email_group/group_emails';
	const XML_PATH_GROUP_EMAILS_NAMES = 'harrodsinventory/email_group/group_names';
	const XML_PATH_TEMPLATE = 'harrodsinventory/email_group/product_update_temp';
    const XML_PATH_ALERT_DEBUG_ENABLED = 'harrodsinventory/email_group/debug_enabled';
    const XML_PATH_MODULE_ENABLED = 'harrodsinventory/module_status/module_enabled';
    const XML_PATH_FILE_TYPE = 'harrodsinventory/email_group/filetype_enabled';

    
    public function getSenderEmail(){
        return Mage::getStoreConfig(self::XML_PATH_SENDER_EMAIL);
    }

    public function getSenderName(){
        return Mage::getStoreConfig(self::XML_PATH_SENDER_NAME);
    }

    public function getEmailStatus(){
        return Mage::getStoreConfig(self::XML_PATH_EMAIL_STATUS);
    }


    public function getEmailsGroup(){
        return Mage::getStoreConfig(self::XML_PATH_GROUP_EMAILS);
    }

    public function getEmailGroupNames(){
        return Mage::getStoreConfig(self::XML_PATH_GROUP_EMAILS_NAMES);
    }

    public function getEmailTemplate(){
        return Mage::getStoreConfig(self::XML_PATH_TEMPLATE);
    }

    public function getDebugStatus(){
        return Mage::getStoreConfig(self::XML_PATH_ALERT_DEBUG_ENABLED);
    }

    public function getModuleStatus(){
        return Mage::getStoreConfig(self::XML_PATH_MODULE_ENABLED);
    }
    public function getFileType(){
        return Mage::getStoreConfig(self::XML_PATH_FILE_TYPE);
    }



}