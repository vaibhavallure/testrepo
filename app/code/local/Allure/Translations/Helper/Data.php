<?php

class Allure_Translations_Helper_Data extends Mage_Customer_Helper_Data
{
    public function getCountryByIp($ip){
        return Mage::getModel('allure_geolocation/geoLocation')->getCountryCode();
    }
}
