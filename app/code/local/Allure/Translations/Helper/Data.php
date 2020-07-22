<?php

class Allure_Translations_Helper_Data extends Mage_Customer_Helper_Data
{
    public function getCountryByIp($ip) {

//      $url='http://geoip.allureinc.co/geoip/'.$ip;

        $helper = Mage::helper("allure_geolocation");
        $url=$helper->getAPIUrl().''.$ip;

        $ch = curl_init();

        $curlConfig = array(
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,

        );

        curl_setopt_array($ch, $curlConfig);
        $result = curl_exec($ch);
        curl_close($ch);

        $geoInfo = json_decode($result, true);
        if (!empty($geoInfo) && isset($geoInfo['countryCode'])){
            return $geoInfo['countryCode'];
        }
        return '';
    }
}
