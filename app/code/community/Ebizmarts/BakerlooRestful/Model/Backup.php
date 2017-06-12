<?php

class Ebizmarts_BakerlooRestful_Model_Backup extends Mage_Core_Model_Abstract
{

    /**
     * Prefix of model events names
     * @var string
     */
    protected $_eventPrefix = 'bakerloo_restful_backup';

    /**
     * Parameter name in event
     * @var string
     */
    protected $_eventObject = 'backup';

    public function _construct()
    {
        $this->_init('bakerloo_restful/backup');
    }
}
