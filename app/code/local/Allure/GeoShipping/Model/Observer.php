<?php

class Allure_GeoShipping_Model_Observer
{

    public function updateGeoShipping (Varien_Event_Observer $observer)
    {
        $shippingHelper = Mage::helper("allure_geoshipping");
        $helper = Mage::helper("allure_geolocation");
        
        if ($shippingHelper->isEnabled() && ! $helper->isPrivateIp() && ! $helper->isCrawler() &&
                 ! $helper->isApi()) {
            
            Mage::getModel('allure_geoshipping/geoShipping')->updateGeoAddress(
                    $observer->getEvent()
                        ->getCart());
        }
    }
}