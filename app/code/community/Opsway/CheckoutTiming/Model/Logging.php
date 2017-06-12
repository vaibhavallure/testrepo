<?php

class OpsWay_CheckoutTiming_Model_Logging extends Mage_Core_Model_Abstract
{

    private $_defaultTiming;

    public function __construct()
    {
        $className = Mage::getStoreConfig('opsway_checkout_timing/settings/logger');
        $this->logger = Mage::getModel($className);
    }


    public function prepareTiming($events)
    {
        foreach ($events AS $key => $event) {
            $this->_defaultTiming[$key] = array(
                'event' => $event,
                'time' => 0
            );
        }
    }


    public function log(Array $timing)
    {
        $latestTime = 0;
        $clearTiming = $this->_defaultTiming;
        array_walk($clearTiming, function (&$item, $key, $timing) {
            if (array_key_exists($key, $timing)) {
                $item = $timing[$key];
            }
        }, $timing);

        for ($i = 0; $i < count($clearTiming); $i++) {
            $diff = $clearTiming[$i]['time'] - $latestTime;
            if ($diff < 0 || $latestTime <= 0) {
                $diff = 0;
            }
            if ($clearTiming[$i]['time'] > 0) {
                $latestTime = $clearTiming[$i]['time'];
            }
            $this->logger->log($clearTiming[$i]['event'], $clearTiming[$i]['time'], $diff, $i);
        }

    }

}