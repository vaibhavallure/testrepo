<?php
class Allure_GeoCurrency_Model_GeoCurrency
{
    private $_session;
    
    public function __construct()
    {
    	$this->_session = Mage::getSingleton('core/session');
    	
    	$this->_helper = Mage::helper('allure_geocurrency');
    }
    
    public function updateGeoInfo()
    {
    	$helper = Mage::helper("allure_geolocation");
        
        if ($this->_helper->isEnabled() && !$helper->isPrivateIp() && !$helper->isCrawler() && !$helper->isApi()) {
	        $countryCode = Mage::getSingleton('allure_geolocation/geoLocation')->getCountryCode();
	        
	        if (empty($countryCode)) {
	            Mage::log("Country code returned empty. Please ensure you have at least one GeoIP method installed/enabled", Zend_Log::DEBUG, "allure_geocurrency.log",$this->_helper->getDebugMode());
	        }
	        
        	$pairArr = $this->_helper->getMappingArray();
	        foreach ($pairArr as $searchArr){
	            if (in_array($countryCode, $searchArr)){
	                $this->_setCurrency($searchArr);
	            }
	        }
        }
    }

    protected function _setCurrency($searchArr)
    {
    	$currencyCode = next($searchArr);
    	$geolocation = Mage::getSingleton('allure_geolocation/geoLocation');
    	
    	if (Mage::app()->getStore()->getCurrentCurrencyCode() != $currencyCode){
	        Mage::log(sprintf("IP::%s, COUNTRY:: %s, CURRENCY:: %s",$geolocation->getIpAddress(), $geolocation->getCountryCode(), $currencyCode), Zend_Log::DEBUG, "allure_geocurrency.log",$this->_helper->getDebugMode());
    		Mage::app()->getStore()->setCurrentCurrencyCode($currencyCode);
    	}
    }
}