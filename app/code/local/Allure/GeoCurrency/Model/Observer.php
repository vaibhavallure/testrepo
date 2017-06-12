<?php

class Allure_GeoCurrency_Model_Observer
{

    public function updateGeoCurrency (Varien_Event_Observer $observer)
    {
        $currencyHelper = Mage::helper("allure_geocurrency");
        $helper = Mage::helper("allure_geolocation");
        
        $controllerAction = $observer->getEvent()->getControllerAction();
        
        if (get_class($controllerAction) === 'Allure_CurrencyManager_CurrencyController') {
            return;
        } else {
             
            $currencyChangeInformation = Mage::getSingleton('customer/session')->getCurrencyChangeInformation();
             
            if (isset($currencyChangeInformation) && !empty($currencyChangeInformation)) {
                return;
            }
        }
        
        
        if ($currencyHelper->isEnabled() && ! $helper->isPrivateIp() &&
                 ! $helper->isCrawler() && ! $helper->isApi()) {
            
            Mage::getModel('allure_geocurrency/geoCurrency')->updateGeoInfo();
        }
    }
}