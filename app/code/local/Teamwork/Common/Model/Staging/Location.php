<?php

class Teamwork_Common_Model_Staging_Location extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_guidField = 'location_id';
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/location');
    }
}