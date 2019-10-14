<?php
/**
 * Display suggestions in catalog search results
 *
 * @category    Allure
 * @package     Allure_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2019 Allure Software, Inc. (https://www.allureinc.co)
 */
class Allure_ElasticSearch_Block_Catalogsearch_Category extends Allure_ElasticSearch_Block_Catalogsearch_Result
{
    /**
     * @var Mage_Catalog_Model_Resource_Category_Collection
     */
    protected $_categories;

    /**
     * Retrieve categories matching text query
     *
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getCategoryCollection()
    {
        if (!$this->_helper->isSearchEnabled('category')) {
            return new Varien_Data_Collection(); // empty collection
        }

        if (!$this->_categories) {
            $this->_categories = parent::getCategoryCollection();
        }

        if ($limit = $this->getLimit()) {
            $this->_categories->getSelect()->limit($limit);
        }

        return $this->_categories;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return (int) Mage::getStoreConfig('elasticsearch/category/limit');
    }
}