<?php
class Allure_GeoStore_Model_GeoStore
{
    private $_session;
    
    public function __construct()
    {
    	$this->_session = Mage::getSingleton('core/session');
    	
    	$this->_helper = Mage::helper('allure_geostore');
    }
    
    public function updateGeoInfo()
    {
        $countryCode = Mage::getSingleton('allure_geolocation/geoLocation')->getCountryCode();
        
        if (empty($countryCode)) {
            Mage::log("Country code returned empty. Please ensure you have at least one GeoIP method installed/enabled", Zend_Log::DEBUG, "allure_geostore.log", $this->_helper->getDebugMode());
        }
        
        if ($this->_helper->isEnabled()) {
        	$pairArr = $this->_helper->getMappingArray();
	        foreach ($pairArr as $searchArr){
	            if (in_array($countryCode, $searchArr)){
	                return $this->_setStore($searchArr);
	            }
	        }
        }
    }
    
    protected function _setStore($searchArr)
    {
    	$storeCode = Mage::app()->getStore($searchArr['store'])->getCode();
    	$geolocation = Mage::getSingleton('allure_geolocation/geoLocation');
    
    	if ($storeCode) {
    		$store = Mage::getModel('core/store')->load($storeCode);
    		if ($store->getName() != Mage::app()->getStore()->getName()) {
    			//Needs to return store URL for observer to redirect using event
    		    Mage::log(sprintf("IP::%s, COUNTRY:: %s, STORE:: %s",$geolocation->getIpAddress(), $geolocation->getCountryCode(), $storeCode), Zend_Log::DEBUG, "allure_geostore.log", $this->_helper->getDebugMode());
    			return $store->getCurrentUrl(false);
    		}
    	}
    }
}