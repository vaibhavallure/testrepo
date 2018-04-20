<?php

class Teamwork_Common_Model_Staging_Feestatus extends Teamwork_Common_Model_Staging_Abstractchanneled
{
    protected $_guidField = 'fee_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/feestatus');
    }
}