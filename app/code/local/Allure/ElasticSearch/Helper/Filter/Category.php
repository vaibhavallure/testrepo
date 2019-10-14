<?php

/**
 * Filter for the Category catalog search
 * @category    Allure
 * @package     Allure_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2019 Allure Software, Inc. (https://www.allureinc.co)
 */
class Allure_ElasticSearch_Helper_Filter_Category extends Allure_ElasticSearch_Helper_Filter_Abstract
{

    /**
     * Add any additional queries to the main query
     *
     * @param BoolQuery the query that has to be extended
     * @param string the query string
     */
    function addAdditionalFilters(\Elastica\Query\BoolQuery $query, $q)
    {
        return;
    }
}
