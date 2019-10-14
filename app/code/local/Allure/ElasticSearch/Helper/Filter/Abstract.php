<?php

/**
 * @category    Allure
 * @package     Allure_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2019 Allure Software, Inc. (https://www.allureinc.co)
 */
abstract class Allure_ElasticSearch_Helper_Filter_Abstract
{

    /**
     * Add any additional queries to the main query
     *
     * @param BoolQuery the query that has to be extended
     * @param string the query string
     */
    abstract function addAdditionalFilters(\Elastica\Query\BoolQuery $query, $q);
}
