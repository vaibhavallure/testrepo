<?php

class Allure_GeoLocation_Model_Mysql4_GeoInfo extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('allure_geolocation/geoinfo', 'ip');
    }
}