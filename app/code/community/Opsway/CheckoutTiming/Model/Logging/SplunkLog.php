<?php

class OpsWay_CheckoutTiming_Model_Logging_SplunkLog extends Mage_Core_Model_Abstract
    implements OpsWay_CheckoutTiming_Model_LoggingInterface
{

    public function log($event, $time, $diff, $step)
    {
        Mage::dispatchEvent('splunk_log_default', array(
            'metric' => 'magento.checkout_time.' . $event,
            'value' => $diff
        ));
    }

}