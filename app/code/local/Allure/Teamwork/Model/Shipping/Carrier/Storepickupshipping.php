<?php
/**
 * @author allure
 */
class Allure_Teamwork_Model_Shipping_Carrier_Storepickupshipping extends Mage_Shipping_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = "tm_storepickupshipping";
    
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        //$arr = array('onepage','sales_order','sales_order_create','cart');
        $arr = array('onepage','sales_order','sales_order_create','cart','paypal','express','multishipping','sales_shipping');
        $controllerName = Mage::app()->getRequest()->getControllerName();
        if(in_array($controllerName, $arr)){
            return false;
        }
        $result = Mage::getModel("shipping/rate_result");
        $result->append($this->_getDefaultRate());
        return $result;
    }

    public function getAllowedMethods()
    {
        return array(
          'tm_storepickupshipping'  => $this->getConfigData('name')
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
