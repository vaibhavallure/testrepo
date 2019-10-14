<?php
/**
 * @category    Allure
 * @package     Allure_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2019 Allure Software, Inc. (https://www.allureinc.co)
 */
class Allure_ElasticSearch_Helper_Catalogsearch extends Mage_CatalogSearch_Helper_Data
{
    /**
     * @return string
     */
    public function getSuggestUrl()
    {
        $url = parent::getSuggestUrl();

        $isLoggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
        if ($isLoggedIn) {
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        } else {
            $customerGroupId = 0;
        }

        $helper = Mage::helper('elasticsearch/autocomplete');
        if ($helper->isActiveEngine() && $helper->isFastAutocompleteEnabled()) {
            $url = sprintf('%sautocomplete.php?store=%s&currency=%s&cg=%d&fallback_url=%s',
                Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, Mage::app()->getStore()->isCurrentlySecure()),
                Mage::app()->getStore()->getCode(),
                Mage::app()->getStore()->getCurrentCurrencyCode(),
                $customerGroupId,
                $url
            );
        }

        return $url;
    }

    /**
     * Get ElasticSearch engine
     *
     * @return Allure_ElasticSearch_Model_Resource_Engine
     */
    public function getEngine()
    {
        return Mage::getResourceSingleton('elasticsearch/engine');
    }
}
