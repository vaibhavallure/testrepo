<?php

class Allure_Counterpoint_Model_Entity_ShippingMethods
{
    public function toOptionArray(){
        $options = array();
        $options[''] = array('label' => "",'value' => "");
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
        foreach($methods as $_code => $_method){
            if(!$_title = Mage::getStoreConfig("carriers/$_code/title"))
                $_title = $_code;
            $options[$_code] = array('value' => $_code, 'label' => $_title );
        }
        return $options;
    }
}