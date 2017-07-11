<?php

class Ebizmarts_BakerlooShipping_Model_Carrier_ShipToStore extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code    = 'bakerloo_ship_to_store';
    protected $_isFixed = true;
    protected $_active  = false;

    public function __construct()
    {
        parent::__construct();

        $isPosRequest = Mage::helper('bakerloo_restful')->isPosRequest(Mage::app()->getRequest());

        if ($isPosRequest) {
            $this->_active = true;
        }
    }

    /**
     * Allows free shipping when all product items have free shipping (promotions etc.)
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return bool
     */
    protected function _updateFreeMethodQuote($request)
    {
        return true;
    }

    /**
     * FreeShipping Rates Collector
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {

        if (!$this->getConfigFlag('active') || !$this->_active) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');

        $this->_updateFreeMethodQuote($request);

        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);

        $locationId = Mage::getSingleton('checkout/session')->getPosShipToStoreId();
        $location   = Mage::getModel('bakerloo_location/store')->load($locationId);

        if ($location->getId()) {
            $methodTitle = "{$this->getConfigData('name')} - {$location->getTitle()}";
        } else {
            $methodTitle = $this->getConfigData('name');
        }

        $method->setMethodTitle($methodTitle);

        $method->setPrice($this->getConfigData('price'));
        $method->setCost($this->getConfigData('price'));

        $result->append($method);

        return $result;
    }

    public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('name'));
    }
}
