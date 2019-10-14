<?php
/**
 * Defines list of available search engines
 *
 * @category    Allure
 * @package     Allure_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2019 Allure Software, Inc. (https://www.allureinc.co)
 */
class Allure_ElasticSearch_Model_System_Config_Source_Transportmode
{
    public function toOptionArray()
    {
        $transportModes = array(
            'Http'  => Mage::helper('adminhtml')->__('Http'),
            'AwsAuthV4' => Mage::helper('adminhtml')->__('AwsAuthV4'),
        );

        $options = array();
        foreach ($transportModes as $k => $v) {
            $options[] = array(
                'value' => $k,
                'label' => $v
            );
        }

        return $options;
    }
}
