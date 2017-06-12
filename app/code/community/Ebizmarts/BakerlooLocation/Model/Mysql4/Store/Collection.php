<?php

class Ebizmarts_BakerlooLocation_Model_Mysql4_Store_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        $this->_init('bakerloo_location/store');
    }
}
