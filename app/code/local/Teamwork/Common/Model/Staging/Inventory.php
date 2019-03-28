<?php

class Teamwork_Common_Model_Staging_Inventory extends Teamwork_Common_Model_Staging_Abstractchanneled
{
    protected $_guidField = 'location_id';
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/inventory');
    }
}