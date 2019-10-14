<?php
/**
 * @category    Allure
 * @package     Allure_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2019 Allure Software, Inc. (https://www.allureinc.co)
 */
class Allure_ElasticSearch_Block_Autocomplete_Category extends Allure_ElasticSearch_Block_Autocomplete_Abstract
{
    /**
     * @var string
     */
    protected $_title = 'Categories';

    /**
     * @var string
     */
    protected $_template = 'allure/elasticsearch/autocomplete/category.phtml';

    /**
     * @param Varien_Object $category
     * @return string
     */
    public function getCategoryPathName(Varien_Object $category)
    {
        if ($this->_config->getConfig('category/show_path', true)) {
            return $category->getData('_path');
        }

        return $category->getName();
    }

    /**
     * @param Varien_Object $category
     * @return string
     */
    public function getCategoryUrl(Varien_Object $category)
    {
        return $this->cleanUrl($category->getData('_url'));
    }
}