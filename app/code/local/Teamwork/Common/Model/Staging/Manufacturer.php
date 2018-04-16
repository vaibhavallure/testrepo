<?php

class Teamwork_Common_Model_Staging_Manufacturer extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_guidField = 'manufacturer_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/manufacturer');
    }
}