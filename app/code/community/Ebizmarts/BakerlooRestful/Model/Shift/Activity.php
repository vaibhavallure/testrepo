<?php

class Ebizmarts_BakerlooRestful_Model_Shift_Activity extends Mage_Core_Model_Abstract
{

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
