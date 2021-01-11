<?php
class Doddle_Returns_Helper_Log extends Mage_Core_Helper_Abstract
{
    const LOG_FILENAME = 'doddle_returns.log';

    public function log($data)
    {
        Mage::log($data, null, self::LOG_FILENAME);
    }
}
