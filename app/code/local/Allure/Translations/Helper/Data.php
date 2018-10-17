<?php

class Allure_Translations_Helper_Data extends Mage_Customer_Helper_Data
{
    public function getCountryByIp($ip){       
        $helper = Mage::helper("allure_geolocation");

        if ($helper->isEnabled() && ! $helper->isPrivateIp() &&
                 ! $helper->isCrawler() && ! $helper->isApi()) {
            Mage::getModel('allure_geolocation/geoLocation')->getGeoInfo();
        }

        return Mage::getModel('allure_geolocation/geoLocation')->getCountryCode();

    }
}
