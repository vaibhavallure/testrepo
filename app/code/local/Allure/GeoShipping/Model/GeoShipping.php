<?php
class Allure_GeoShipping_Model_GeoShipping
{
    private $_session;
    private $_helper;
    private $_cart;
    
    public function __construct()
    {
    	$this->_session = Mage::getSingleton('core/session');
    	
    	$this->_helper = Mage::helper('allure_geoshipping');
    }
    
    public function updateGeoAddress($cart)
    {
    	$this->_cart = $cart;
    	
    	$helper = Mage::helper("allure_geolocation");
    	$geolocation = Mage::getSingleton('allure_geolocation/geoLocation');
    	
    	$checkoutSession = Mage::getSingleton('checkout/session');

        if ($this->_helper->isEnabled() && !$helper->isPrivateIp() && !$helper->isCrawler() && !$helper->isApi()) {
        
	        if ($this->_canUpdateGeoInfo() && !$checkoutSession->getIsSetGeoShippingAddress()) {
	        	
	        	$geoAddress = $this->_session->getGeoAddress();
	        	
	        	if ($geoAddress && $geoAddress->getData()) {

	        		$estimatedSessionAddressData = $geoAddress->getData();
	        		
	        		Mage::log(sprintf("IP::%s, COUNTRY:: %s, DATA:: %s",$geolocation->getIpAddress(), $geolocation->getCountryCode(), json_encode($estimatedSessionAddressData)), Zend_Log::DEBUG, 'allure_geoshipping.log', $this->_helper->getDebugMode());
	        		 
		        	$checkoutSession->setEstimatedShippingAddressData(array(
			            'country_id' => $geoAddress->getCountryId(),
			            'postcode'   => $geoAddress->getPostcode(),
			            'city'       => $geoAddress->getCity(),
			            'region_id'  => $geoAddress->getRegionId(),
			            'region'     => $geoAddress->getRegion()
			        ));
		        	
		        	$cart->getQuote()->getShippingAddress()
	                    ->setCountryId($estimatedSessionAddressData['country_id'])
	                    ->setCity($estimatedSessionAddressData['city'])
	                    ->setPostcode($estimatedSessionAddressData['postcode'])
	                    ->setRegionId($estimatedSessionAddressData['region_id'])
	                    ->setRegion($estimatedSessionAddressData['region'])
		        		->setShippingMethod('flatrate_flatrate')
		        		->save();
		        	
	        		$cart->getQuote()->getShippingAddress()->setCollectShippingRates(true);
	        		$cart->getQuote()->setTotalsCollectedFlag(false);
	        		$cart->getQuote()->collectTotals();
	        		$cart->getQuote()->save();
		        	
		        	$checkoutSession->setIsSetGeoShippingAddress(true);
		        	
		        	$checkoutSession->setCartWasUpdated(false);
	        	}
	        }
        }
    }
    
    private function _canUpdateGeoInfo () 
    {
        $customerSession = Mage::getSingleton('customer/session');
        
    	return (Mage::app()->getRequest()->getControllerName() == 'cart' && Mage::app()->getRequest()->getActionName() == 'index' && $this->_cart && $this->_cart->getQuote() && $this->_cart->getQuote()->getItemsCount() && (!$customerSession->isLoggedIn() || !$this->_helper->ignoreCustomerDefault()));
    }
}
