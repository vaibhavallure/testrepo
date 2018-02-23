<?php

class Teamwork_Common_Model_Staging_Discount extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_guidField = 'discount_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/discount');
    }
}