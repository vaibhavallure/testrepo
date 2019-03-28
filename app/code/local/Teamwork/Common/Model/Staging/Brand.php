<?php

class Teamwork_Common_Model_Staging_Brand extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_guidField = 'brand_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/brand');
    }
}