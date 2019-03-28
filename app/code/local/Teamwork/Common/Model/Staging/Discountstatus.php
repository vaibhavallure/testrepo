<?php

class Teamwork_Common_Model_Staging_Discountstatus extends Teamwork_Common_Model_Staging_Abstractchanneled
{
    protected $_guidField = 'discount_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/discountstatus');
    }
}