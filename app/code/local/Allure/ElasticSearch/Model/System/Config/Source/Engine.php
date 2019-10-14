<?php
/**
 * Defines list of available search engines
 *
 * @category    Allure
 * @package     Allure_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2019 Allure Software, Inc. (https://www.allureinc.co)
 */
class Allure_ElasticSearch_Model_System_Config_Source_Engine
{
    /**
     * Return list of search engines for config
     *
     * @return array
     */
    public function toOptionArray()
    {
        $engines = array(
            'catalogsearch/fulltext_engine'  => Mage::helper('adminhtml')->__('MySQL'),
            'elasticsearch/engine' => Mage::helper('adminhtml')->__('ElasticSearch'),
        );

        $options = array();
        foreach ($engines as $k => $v) {
            $options[] = array(
                'value' => $k,
                'label' => $v
            );
        }

        return $options;
    }
}
