<?php
/**
 * 
 * @author allure
 *
 */
class Allure_WaitWhile_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * wait-while configuration path
     */
    const XML_WAITWHILE_BOOKING_ENABLED     = 'allure_waitwhile/general/enabled';
    const XML_WAITWHILE_BOOKING_HOST        = 'allure_waitwhile/general/host';
    const XML_WAITWHILE_BOOKING_API_KEY     = 'allure_waitwhile/general/api_key';
    const XML_WAITWHILE_BOOKING_DEBUG_LOG   = 'allure_waitwhile/general/debug';
    
    /**
     * Get wait-while booking status
     * @return mixed|string|NULL
     */
    public function isBookingEnabled()
    {
        return Mage::getStoreConfig(self::XML_WAITWHILE_BOOKING_ENABLED);
    }
    
    /**
     * Get wait-while booking host
     * @return mixed|string|NULL
     */
    public function getBookingHost()
    {
        return Mage::getStoreConfig(self::XML_WAITWHILE_BOOKING_HOST);
    }
    
    /**
     * Get wait-while booking api key
     * @return mixed|string|NULL
     */
    public function getBookingApiKey()
    {
        return Mage::getStoreConfig(self::XML_WAITWHILE_BOOKING_API_KEY);
    }
    
    /**
     * Get debug log status
     * @return mixed|string|NULL
     */
    public function isDebugLog()
    {
        return Mage::getStoreConfig(self::XML_WAITWHILE_BOOKING_DEBUG_LOG);
    }
}
