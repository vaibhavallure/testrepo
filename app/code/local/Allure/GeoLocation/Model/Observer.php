<?php

class Allure_GeoLocation_Model_Observer
{

    public function loadGeoInfo (Varien_Event_Observer $observer)
    {
        $helper = Mage::helper("allure_geolocation");
        
        if ($helper->isEnabled() && ! $helper->isPrivateIp() &&
                 ! $helper->isCrawler() && ! $helper->isApi()) {
            Mage::getModel('allure_geolocation/geoLocation')->getGeoInfo();
        }
    }
}