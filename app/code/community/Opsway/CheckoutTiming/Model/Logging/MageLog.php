<?php

class OpsWay_CheckoutTiming_Model_Logging_MageLog extends Mage_Core_Model_Abstract
    implements OpsWay_CheckoutTiming_Model_LoggingInterface
{

    public function log($event, $time, $diff, $step)
    {
        Mage::log("Step $step: " . number_format($diff, 2) . 'sec [' . $event . ']', null, 'checkout_timing.log');
    }

}