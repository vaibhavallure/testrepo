<?php
class Allure_GeoLocation_Model_GeoLocation
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
    
    private $_session;
    
    public function __construct()
    {
    	$this->_session = Mage::getSingleton('core/session');
    	
    	$this->_helper = Mage::helper('allure_geolocation');
    }

    /**
     * @return array
     */
    public function getGeoInfo()
    {
    	$geoCountry = $this->_session->getGeoCountry();
    	
    	if (!$this->_helper->enableTestMode() && $geoCountry) {
    		return $this->_session->getGeoInfo()->getData();
    	}

        $geoInfo = $this->_getGeoInfo();
        
        $this->_session->setGeoInfo(new Varien_Object($geoInfo));
        $this->_session->setGeoAddress($this->_getGeoAddress($geoInfo));
        $this->_session->setGeoCountry($this->_session->getGeoInfo()->getCountry());

        return (array) $geoInfo;
    }

    /**
     * @return Varien_Object
     */
    public function getGeoAddress()
    {
        return new Varien_Object($this->getGeoInfo());
    }
    
    public function getCountryCode()
    {
    	if ($this->_helper->enableTestMode()) {
    		 
    		$overrideCountry = $this->_helper->getCountryOverride();
    
    		if (!empty($overrideCountry)){
    			return $overrideCountry;
    		}
    	}
        return $this->getGeoAddress()->getCountry();
    }
    
    public function getIpAddress() {
    	return $this->_getIpAddress();
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
    
    /**
     * @return array
     */
    protected function _getGeoInfo()
    {
        $geoInfo = array();
        $geoInfo["countryName"] = "United States";
        $geoInfo["country"] = "US";

        $info = $_SERVER;
        if(isset($info["HTTP_WEBSCALE_COUNTRY"]) && !empty($info["HTTP_WEBSCALE_COUNTRY"])){
            $countryCode = $info["HTTP_WEBSCALE_COUNTRY"];

            $geoInfo["countryName"] = $countryCode;
            $geoInfo["country"] = $countryCode;
        }

        Mage::log(json_encode($geoInfo), Zend_Log::DEBUG, 'allure_geolocation.log', $this->_helper->getDebugMode());
    	return (array) $geoInfo;
    }
    
   /* private function getAPIUrl()
    {
        return ($this->_helper->getAPIUrl() ? $this->_helper->getAPIUrl()  : self::IP_API_GATEWAY);
    }*/

    /**
     * @return mixed|string
     */
    protected function _getIpAddress()
    {
    	$this->_helper->switchRemoteHeaders();
    	
        $clientIP = Mage::helper('core/http')->getRemoteAddr();
        
        if ($this->_helper->isPrivateIp() && $this->_helper->getPrivateMode()) {
            $clientIP = '';
        }

        if ($this->_helper->enableTestMode()) {
        	 
        	$overrideIP = $this->_helper->getIpOverride();
        
        	if (!empty($overrideIP) && $this->_helper->validateIpAddress($overrideIP)){
        		$clientIP = $overrideIP;
        	}
        }
        
        return $clientIP;
    }
}