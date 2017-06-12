<?php

class OpsWay_Customer_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_PRIVATE_IP_ADDRESSES = 'system/customer_geoip/private_ip_address';
    const XML_PATH_CURL_TIMEOUT = 'system/customer_geoip/curl_timeout';

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return array
     */
    public function getPrivateIpAddress($store = null)
    {
        $addresses = Mage::getStoreConfig(self::XML_PATH_PRIVATE_IP_ADDRESSES, $store);
        $addresses = preg_replace("#\r\n|\r|\n#", PHP_EOL, $addresses);
        $addresses = trim($addresses);
        return $addresses ? explode(PHP_EOL, $addresses) : array();
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return int
     */
    public function getCurlTimeout($store = null)
    {
        $timeout = (int) Mage::getStoreConfig(self::XML_PATH_CURL_TIMEOUT, $store);
        $timeout = $timeout > 0 ? $timeout : 60;
        return $timeout;
    }
}
