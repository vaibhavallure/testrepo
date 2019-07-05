<?php
class Allure_CustomerLoginMonitor_Model_Log
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'all', 'label' => 'All'),
            array('value' => 'success', 'label' => 'Success Only'),
            array('value' => 'failed', 'label' =>'Failed Only')
        );
    }
}

