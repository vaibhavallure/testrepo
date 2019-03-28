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
    	$url = $this->getAPIUrl() . $this->_getIpAddress();
    
    	$ch = curl_init($url);
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    	curl_setopt($ch, CURLOPT_TIMEOUT, $this->_helper->getCurlTimeout());
    	$response = curl_exec($ch);
    	
    	if (curl_errno($ch) || empty($response)) {
    	    $response = '{"as":"AS12271 Time Warner Cable Internet LLC","city":"New York","country":"US","countryCode":"US","isp":"Time Warner Cable","lat":40.7769,"lon":-73.9813,"org":"Time Warner Cable","query":"66.65.83.126","region":"New York","regionName":"New York","status":"success","timezone":"America\/New_York","zip":"10023","countryName":"United States","regionCode":"NY","postal":"10023"}';
    	    //$response = '{"offset":"5","timezone":"Asia\/Kolkata","organization":"AS18207 YOU Broadband & Cable India Ltd.","country":"India","dma_code":"0","area_code":"0","region_code":"09","ip":"123.201.100.12","region":"Gujarat","continent_code":"AS","city":"Ahmedabad","postal_code":"380009","longitude":72.6167,"latitude":23.0333,"country_code":"IN","country_code3":"IND"}';
    	}
    	
    	curl_close($ch);
    
    	$geoInfo = json_decode($response, true);
    
    	if (isset($geoInfo['status']) && $geoInfo['status'] == 'fail') {
    		return false;
    	}
    
    	foreach ($this->infoMap as $key => $value) {
    		$geoInfo[$key] = isset($geoInfo[$value]) ? $geoInfo[$value] : null;
    	}
    	if($this->_helper->getDebugMode()){
    	       Mage::log(json_encode($geoInfo), Zend_Log::DEBUG, 'allure_geolocation.log', $this->_helper->getDebugMode());
    	}
    	return (array) $geoInfo;
    }
    
    private function getAPIUrl()
    {
        return ($this->_helper->getAPIUrl() ? $this->_helper->getAPIUrl()  : self::IP_API_GATEWAY);
    }

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