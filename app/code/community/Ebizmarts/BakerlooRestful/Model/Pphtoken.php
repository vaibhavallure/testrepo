<?php

class Ebizmarts_BakerlooRestful_Model_Pphtoken extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     * @var string
     */
    protected $_eventPrefix = 'bakerloo_restful_pphtoken';

    /**
     * Parameter name in event
     * @var string
     */
    protected $_eventObject = 'pphtoken';

    public function _construct()
    {
        $this->_init('bakerloo_restful/pphtoken');
    }
}