<?php
/**
 * Display suggestions in catalog search results
 *
 * @category    Allure
 * @package     Allure_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2019 Allure Software, Inc. (https://www.allureinc.co)
 */
class Allure_ElasticSearch_Block_Catalogsearch_Suggest extends Mage_CatalogSearch_Block_Result
{
    /**
     * @var string
     */
    protected $_suggestion;

    /**
     * Returns one suggestion
     *
     * @return string
     */
    public function getSuggestion()
    {
        if (null === $this->_suggestion) {
            $this->_suggestion = false;
            /** @var Allure_ElasticSearch_Model_Resource_Fulltext_Collection $collection */
            $layer = Mage::getSingleton('catalogsearch/layer');
            $collection = $layer->getProductCollection();
            if ($collection->hasFlag('suggest')) {
                $this->_suggestion = $collection->getFlag('suggest');
            }
        }

        return $this->_suggestion;
    }

    /**
     * Builds search URL for specified text query
     *
     * @param $q
     * @return string
     */
    public function getQueryUrl($q)
    {
        return $this->getUrl('catalogsearch/result', array('_query' => array('q' => $q)));
    }
}