<?php
/**
 * Query default operator configuration
 *
 * @category    Allure
 * @package     Allure_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2019 Allure Software, Inc. (https://www.allureinc.co)
 */
class Allure_ElasticSearch_Model_System_Config_Source_Query_Operator
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'OR', 'label' => Mage::helper('elasticsearch')->__('OR')),
            array('value' => 'AND', 'label' => Mage::helper('elasticsearch')->__('AND')),
        );
    }
}