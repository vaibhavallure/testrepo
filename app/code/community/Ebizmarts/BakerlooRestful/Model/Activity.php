<?php

class Ebizmarts_BakerlooRestful_Model_Activity extends Mage_Core_Model_Abstract
{

    const TYPE_OPEN_SHIFT  = 'open_shift';
    const TYPE_CLOSE_SHIFT = 'close_shift';
    const TYPE_ADD_TO_TILL = 'add_to_till';
    const TYPE_TRANSACTION = 'transaction';
    const TYPE_ADJUSTMENT  = 'adjustment';
    const TYPE_SALE        = 'sale';

    /**
     * Prefix of model events names
     * @var string
     */
    protected $_eventPrefix = 'bakerloo_restful_activity';

    /**
     * Parameter name in event
     * @var string
     */
    protected $_eventObject = 'activity';

    public function _construct()
    {
        $this->_init('bakerloo_restful/shift_activity');
    }

    public function getMovements()
    {
        $movements = Mage::getResourceModel('bakerloo_restful/shift_movement_collection')
            ->addFieldToFilter('activity_id', array('eq' => $this->getId()))
            ->getItems();

        return $movements;
    }
}
