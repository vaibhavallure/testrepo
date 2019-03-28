<?php

class Teamwork_Common_Model_Staging_Fee extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_guidField = 'fee_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/fee');
    }
}