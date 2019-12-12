<?php
class Allure_Matrixrate_Model_Carrier_Source_Freemethod
{
    public function toOptionArray()
    {
        $code = "matrixrate";
        $arr  = array();
        $arr[] = array('value' => '', 'label'=>Mage::helper('shipping')->__('None'));
        $shippingRates = Mage::getResourceModel('matrixrate_shipping/carrier_matrixrate_collection');
        foreach ($shippingRates as $rate){
            $value = $code."_".$rate->getPk()."#".$rate->getIsSignature()."#".$rate->getIsInternational();
            $arr[] = array("label" => $rate->getShippingName(), "value" => $value);
        }
        return $arr;
    }
}

