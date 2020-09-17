<?php

class Allure_Translations_Helper_Data extends Mage_Customer_Helper_Data
{
    public function getCountryByIp($ip) {

        $country = "US";

        $info = $_SERVER;
        if(isset($info["HTTP_WEBSCALE_COUNTRY"]) && !empty($info["HTTP_WEBSCALE_COUNTRY"])){
            $country = $info["HTTP_WEBSCALE_COUNTRY"];
        }

        return $country;
    }
}
