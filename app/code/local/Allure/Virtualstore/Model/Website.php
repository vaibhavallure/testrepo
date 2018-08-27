<?php

class Allure_Virtualstore_Model_Website extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('allure_virtualstore/website');
    }
    public function getWebsiteIds()
    {
        $collection = Mage::getModel('allure_virtualstore/website')->getCollection();
        $wbStrore=$collection->getData();
        $websiteArray = array();
        foreach ($wbStrore as $c)
        {
            $websiteArray[] = array('label'=>$c['name'],'value'=>$c['website_id']);
        }
        return $websiteArray;
    }
    public function getWebsite()
    {
        $collection = Mage::getModel('allure_virtualstore/website')->getCollection();
        $websiteArray = array();
        foreach ($collection as $c)
        {
            $websiteArray[$c->getData('website_id')] = $c->getData('name');
        }
        return $websiteArray;
    }
}
