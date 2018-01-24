<?php
class Allure_Pickinstore_Model_Pickinstore extends Mage_Shipping_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = "allure_pickinstore";
    
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $result = Mage::getModel("shipping/rate_result");
        $result->append($this->_getDefaultRate());
        return $result;
        
    }

    public function getAllowedMethods()
    {
        return array(
          'allure_pickinstore'  => $this->getConfigData('name')
        );
    }
    
    protected function _getDefaultRate(){
        $rate = Mage::getModel('shipping/rate_result_method');
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod($this->_code);
        $rate->setMethodTitle($this->getConfigData('name'));
        $rate->setPrice($this->getConfigData('price'));
        $rate->setCost(0);
        return $rate;
    }

    
}
