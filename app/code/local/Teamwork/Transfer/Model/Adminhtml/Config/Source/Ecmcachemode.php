<?php

class Teamwork_Transfer_Model_Adminhtml_Config_Source_Ecmcachemode
{
    public function toOptionArray()
    {
        return array(
            array(
                'label' => 'Flush Magento Cache',
                'value' => Teamwork_Transfer_Helper_Config::ECM_CACHE_MODE_FLUSH_SYSTEM
            ),
            array(
                'label' => 'Flush Cache Storage',
                'value' => Teamwork_Transfer_Helper_Config::ECM_CACHE_MODE_FLUSH_ALL
            ),
            array(
                'label' => 'Refresh all cache',
                'value' => Teamwork_Transfer_Helper_Config::ECM_CACHE_MODE_REFRESH_ALL
            ),
            array(
                'label' => 'Do nothing',
                'value' => Teamwork_Transfer_Helper_Config::ECM_CACHE_MODE_DO_NOTHING
            ),
        );
    }
}
