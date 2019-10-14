<?php
/**
 * @category    Allure
 * @package     Allure_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2019 Allure Software, Inc. (https://www.allureinc.co)
 */
/**
 * @method  Mage_Catalog_Model_Category getEntity()
 * @method  $this setEntity(Mage_Catalog_Model_Category $category)
 */
class Allure_ElasticSearch_Block_Catalogsearch_Autocomplete_Category
    extends Allure_ElasticSearch_Block_Catalogsearch_Result
{
    /**
     * @var string
     */
    protected $_autocompleteTitle = 'Categories';

    /**
     * Initialization
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('allure/elasticsearch/autocomplete/category.phtml');
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     * @return string
     */
    public function getCategoryUrl(Mage_Catalog_Model_Category $category)
    {
        return $category->getUrl();
    }
}
