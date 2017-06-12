<?php
class Allure_GeoLocation_Model_Adminhtml_Source_TestMode
{
    public function toOptionArray()
    {        
        $array = array(
            array('value' => '0', 'label' => Mage::helper('allure_geolocation')->__('Disabled')),
            array('value' => '1', 'label' => Mage::helper('allure_geolocation')->__('Current Administrators')),
            array('value' => '2', 'label' => Mage::helper('allure_geolocation')->__('Specific IP Addresses')),
            array('value' => '3', 'label' => Mage::helper('allure_geolocation')->__('Everyone')),           
        );       
    	return $array;
    }
}