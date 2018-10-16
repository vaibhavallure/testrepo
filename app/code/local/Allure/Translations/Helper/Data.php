<?php

class Allure_Translations_Helper_Data extends Mage_Customer_Helper_Data
{
	
/*    public function getCountryByIp($ip){
        
        $url='http://www.geoplugin.net/json.gp?ip='.$ip;
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
        if (!empty($geoInfo)){
            return $geoInfo['geoplugin_countryCode'];
        }
        return ''; 
    }*/

    public function getCountryByIp($ip){       
        $geoInfo = Mage::getModel('allure_geolocation/geoLocation')->getCountryCode();
        return $geoInfo;        
    }
}
