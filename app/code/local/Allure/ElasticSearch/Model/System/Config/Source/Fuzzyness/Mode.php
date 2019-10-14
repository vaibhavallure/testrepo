<?php
/**
 * Query default operator configuration
 *
 * @category    Allure
 * @package     Allure_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2019 Allure Software, Inc. (https://www.allureinc.co)
 */
class Allure_ElasticSearch_Model_System_Config_Source_Fuzzyness_Mode
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'AUTO', 'label' => __('AUTO')),
            array('value' => '0', 'label' => __('0')),
            array('value' => '1', 'label' => __('1')),
            array('value' => '2', 'label' => __('2'))
        );
    }
}
