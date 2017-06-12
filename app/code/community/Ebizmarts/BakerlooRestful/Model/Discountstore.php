<?php

class Ebizmarts_BakerlooRestful_Model_Discountstore extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     * @var string
     */
    protected $_eventPrefix = 'bakerloo_restful_discountstore';

    /**
     * Parameter name in event
     * @var string
     */
    protected $_eventObject = 'discountstore';

    public function _construct()
    {
        $this->_init('bakerloo_restful/discountstore');
    }
}
