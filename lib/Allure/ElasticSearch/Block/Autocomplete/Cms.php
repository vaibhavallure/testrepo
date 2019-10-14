<?php
/**
 * @category    Allure
 * @package     Allure_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2019 Allure Software, Inc. (https://www.allureinc.co)
 */
class Allure_ElasticSearch_Block_Autocomplete_Cms extends Allure_ElasticSearch_Block_Autocomplete_Abstract
{
    /**
     * @var string
     */
    protected $_title = 'Pages';

    /**
     * @var string
     */
    protected $_template = 'allure/elasticsearch/autocomplete/cms.phtml';

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_config->getValue('base_url', '');
    }
}