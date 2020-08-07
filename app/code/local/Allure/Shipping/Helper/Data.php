<?php
/**
 * 
 * @author allure
 *
 */
class Allure_Shipping_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_SHIPPING_ENABLED = 'allure_shipping/general/enabled'; 
    const XML_PATH_SHIPPING_MAPPING = 'allure_shipping/general/shipping_mapping';
    
    /**
     * Check shipping settings is enabled or not
     * @return mixed|string|NULL
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_SHIPPING_ENABLED);
    }
    
    /**
     * Get shipping configuration mapping data
     * @return array
     */
    public function getShippingConfigMapping()
    {
        $configDataStr = Mage::getStoreConfig(self::XML_PATH_SHIPPING_MAPPING); 
        if(!$configDataStr) return array();
        $data = unserialize($configDataStr);
        $configArr = array();
        foreach ($data as $val){
            $configArr[$val['shipping_carrier']] = $val;
        }
        return $configArr;
    }
}
