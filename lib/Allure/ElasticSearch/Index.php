<?php
/**
 * @category    Allure
 * @package     Allure_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2019 Allure Software, Inc. (https://www.allureinc.co)
 */
class Allure_ElasticSearch_Index extends \Elastica\Index
{
    /**
     * @var array
     */
    protected $_analyzers = array();

    /**
     * @return array
     */
    public function getAnalyzers()
    {
        return $this->_analyzers;
    }

    /**
     * @param array $analyzers
     * @return $this
     */
    public function setAnalyzers(array $analyzers)
    {
        $this->_analyzers = $analyzers;

        return $this;
    }
}