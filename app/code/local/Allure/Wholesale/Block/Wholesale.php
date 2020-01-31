<?php

class Allure_Wholesale_Block_Wholesale extends Mage_Core_Block_Template
{
    public function application()
    {
        $formData=Mage::getSingleton("core/session")->getData('application_data');
        $data = new Varien_Object();
        if ($formData) {
            $data->addData($formData);
        }

        return $data;
    }
    public function getAction()
    {
        return Mage::getBaseUrl('web').'wholesale-customer/wholesale/applicationSave';
    }
    public function getCountryList()
    {
       return Mage::getModel('directory/country')->getResourceCollection()
            ->loadByStore()
            ->toOptionArray(true);
    }
    public function clearSessionData()
    {
        Mage::getSingleton("core/session")->unsetData('application_data');
    }

    public function getWholesaleStoreUrl()
    {
       return $this->getStoreUrl($this->helper()->getStoreId());
    }

    public function getRetailStoreUrl()
    {
        return $this->getStoreUrl(Allure_Wholesale_Model_Observer::RETAIL_STORE_ID);
    }

    public function helper()
    {
        return Mage::helper("wholesale/data");
    }

    public function get_domain($url)
    {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];

        return $domain;
    }
    public function getStoreUrl($storeId)
    {
        return Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
    }
}
