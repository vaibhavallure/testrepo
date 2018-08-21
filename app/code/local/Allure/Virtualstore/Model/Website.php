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
        foreach ($wbStrore as $c)
        {
            $websiteArray[] = array('label'=>$c['name'],'value'=>$c['website_id']);
        }
        return $websiteArray;
    }
}
