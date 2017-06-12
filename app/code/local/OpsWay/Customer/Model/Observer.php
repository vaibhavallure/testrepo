<?php

class OpsWay_Customer_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function updateGeoInfo(Varien_Event_Observer $observer)
    {
        Mage::getSingleton('opsway_customer/geoInfoService')
            ->updateCustomerGeoInfo($observer->getEvent()->getCustomerSession());
    }
}
