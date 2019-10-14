<?php
/**
 * @category    Allure
 * @package     Allure_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2019 Allure Software, Inc. (https://www.allureinc.co)
 */
/**
 * @method  Mage_Cms_Model_Page getEntity()
 * @method  $this setEntity(Mage_Cms_Model_Page $page)
 */
class Allure_ElasticSearch_Block_Catalogsearch_Autocomplete_Cms
    extends Allure_ElasticSearch_Block_Catalogsearch_Result
{
    /**
     * @var string
     */
    protected $_autocompleteTitle = 'Pages';

    /**
     * Initialization
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('allure/elasticsearch/autocomplete/cms.phtml');
    }
}
