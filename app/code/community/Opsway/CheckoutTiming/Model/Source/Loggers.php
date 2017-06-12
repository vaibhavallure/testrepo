<?php

class OpsWay_CheckoutTiming_Model_Source_Loggers
{

    public static $loggers = array(
        array(
            'value' => 'OpsWay_CheckoutTiming_Model_Logging_MageLog',
            'label' => 'Mage Logger'
        ),
        array(
            'value' => 'OpsWay_CheckoutTiming_Model_Logging_SplunkLog',
            'label' => 'Splunk Logger'
        )
    );


    public function toOptionArray()
    {
        return self::$loggers;
    }

}