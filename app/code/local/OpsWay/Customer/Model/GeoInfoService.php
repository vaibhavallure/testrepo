<?php

class OpsWay_Customer_Model_GeoInfoService
{
    const IP_API_GATEWAY = 'http://ip-api.com/json/';

    /**
     * @var array
     */
    protected $infoMap = array(
        'countryName' => 'country',
        'country'     => 'countryCode',
        'regionCode'  => 'region',
        'region'      => 'regionName',
        'postal'      => 'zip',
    );

    /**
     * @param Mage_Customer_Model_Session $session
     *
     * @return $this
     */
    public function updateCustomerGeoInfo(Mage_Customer_Model_Session $session)
    {
        $clientAddress = $this->_getIPAddress();
        $geoInfo = (array) $session->getGeoInfo();
        
        Mage::log($geoInfo,1,'geoinfo.log');

        if (!$geoInfo || !isset($geoInfo[$clientAddress])) {
            $geoInfo[$clientAddress] = $this->_getGeoInfo();
            $session->setGeoInfo($geoInfo);
        }

        $geoAddress = $session->getGeoAddress();

        if (!$geoAddress && ($geoInfo && isset($geoInfo[$clientAddress]))) {
            $session->setGeoAddress($this->_getGeoAddress($geoInfo[$clientAddress]));
        }

        return $this;
    }
    
    public function getIPAddress() {
    	return $this->_getIPAddress();
    }

    /**
     * @return mixed|string
     */
    protected function _getIPAddress()
    {
        // @todo use Mage::helper('core/http')->getRemoteAddr();
        $clientIP = Mage::app()->getRequest()->getServer('REMOTE_ADDR');
        $proxyIP = Mage::app()->getRequest()->getServer('HTTP_X_FORWARDED_FOR');

        if ($proxyIP && !$this->_isPrivateAddress($proxyIP)) {
            $clientIP = $proxyIP;
        }

        if ($this->_isPrivateAddress($clientIP)) {
            $clientIP = '';
        }

        return $clientIP;
    }

    /**
     * @param string $ip
     *
     * @return bool
     */
    protected function _isPrivateAddress($ip)
    {
        $longIp = ip2long($ip);
        if ($longIp != -1) {
            $privateAddresses = Mage::helper('opsway_customer')->getPrivateIpAddress();
            foreach ($privateAddresses AS $privateAddress) {
                list($start, $end) = explode('|', $privateAddress);
                if ($longIp >= ip2long($start) && $longIp <= ip2long($end))
                    return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    protected function _getGeoInfo()
    {
        $url = self::IP_API_GATEWAY . $this->_getIPAddress();

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, Mage::helper('opsway_customer')->getCurlTimeout());
        $response = curl_exec($ch);
        curl_close($ch);

        $geoInfo = json_decode($response, true);

        foreach ($this->infoMap as $key => $value) {
            $geoInfo[$key] = isset($geoInfo[$value]) ? $geoInfo[$value] : null;
        }

        return (array) $geoInfo;
    }

    /**
     * @param array $info
     *
     * @return Mage_Customer_Model_Address
     */
    protected function _getGeoAddress(array $info)
    {
        $country = (string) (isset($info['country']) ? $info['country'] : null);
        $postcode = (string) (isset($info['postal']) ? $info['postal'] : null);
        $city = (string) (isset($info['city']) ? $info['city'] : null);
        $region = (string) (isset($info['region']) ? $info['region'] : null);
        $regionId = (string) Mage::getModel('directory/region')->load($region, 'default_name')->getRegionId();

        return Mage::getModel('customer/address')
            ->setCountryId($country)
            ->setCity($city)
            ->setPostcode($postcode)
            ->setRegionId($regionId)
            ->setRegion($region);
    }
}
