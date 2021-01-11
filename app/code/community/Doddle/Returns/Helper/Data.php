<?php
class Doddle_Returns_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_API_KEY               = 'doddle_returns/api/key';
    const XML_PATH_API_SECRET            = 'doddle_returns/api/secret';
    const XML_PATH_API_MODE              = 'doddle_returns/api/mode';
    const XML_PATH_API_LIVE_URL          = 'doddle_returns/api/live_url';
    const XML_PATH_API_TEST_URL          = 'doddle_returns/api/test_url';
    const XML_PATH_COMPANY_ID            = 'doddle_returns/order_sync/company_id';
    const XML_PATH_ORDER_SYNC_ENABLED    = 'doddle_returns/order_sync/enabled';
    const XML_PATH_ORDER_SYNC_BATCH_SIZE = 'doddle_returns/order_sync/batch_size';
    const XML_PATH_ORDER_SYNC_MAX_FAILS  = 'doddle_returns/order_sync/max_fails';

    /**
     * @return string
     */
    public function getApiKey()
    {
        return (string) Mage::getStoreConfig(self::XML_PATH_API_KEY);
    }

    /**
     * @return string
     */
    public function getApiSecret()
    {
        return (string) Mage::getStoreConfig(self::XML_PATH_API_SECRET);
    }

    /**
     * @return string
     */
    public function getApiMode()
    {
        return (string) Mage::getStoreConfig(self::XML_PATH_API_MODE);
    }

    /**
     * @return string
     */
    public function getLiveApiUrl()
    {
        return (string) Mage::getStoreConfig(self::XML_PATH_API_LIVE_URL);
    }

    /**
     * @return string
     */
    public function getTestApiUrl()
    {
        return (string) Mage::getStoreConfig(self::XML_PATH_API_TEST_URL);
    }

    /**
     * @return string
     */
    public function getCompanyId()
    {
        return (string) Mage::getStoreConfig(self::XML_PATH_COMPANY_ID);
    }

    /**
     * @return bool
     */
    public function getOrderSyncEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ORDER_SYNC_ENABLED);
    }

    /**
     * @return int
     */
    public function getOrderSyncBatchSize()
    {
        return (int) Mage::getStoreConfig(self::XML_PATH_ORDER_SYNC_BATCH_SIZE);
    }

    /**
     * @return int
     */
    public function getOrderSyncMaxFails()
    {
        return (int) Mage::getStoreConfig(self::XML_PATH_ORDER_SYNC_MAX_FAILS);
    }
}
