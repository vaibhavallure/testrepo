<?php
class Allure_GeoStore_Model_Observer
{
    public function updateGeoStore(Varien_Event_Observer $observer)
    {
    	$storeHelper = Mage::helper("allure_geostore");
    	$helper = Mage::helper("allure_geolocation");
    	
        if ($storeHelper->isEnabled() && !$helper->isPrivateIp() && !$helper->isCrawler() && !$helper->isApi()) {
            
            $redirStore = Mage::getModel('allure_geostore/geoStore')->updateGeoInfo();
            
            if ($redirStore){
            	$observer->getControllerAction()->getResponse()->setRedirect($redirStore)->sendResponse();
            }
        }
    }
}