<?php

interface OpsWay_CheckoutTiming_Model_LoggingInterface
{
    public function log($event, $time, $diff, $step);
}