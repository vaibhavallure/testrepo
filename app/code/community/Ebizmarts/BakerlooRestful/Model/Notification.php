<?php

class Ebizmarts_BakerlooRestful_Model_Notification extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     * @var string
     */
    protected $_eventPrefix = 'bakerloo_restful_notification';

    /**
     * Parameter name in event
     * @var string
     */
    protected $_eventObject = 'notification';

    public function _construct()
    {
        $this->_init('bakerloo_restful/notification');
    }
}
