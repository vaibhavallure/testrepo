<?php

class Teamwork_Common_Model_Staging_Acsslevel1 extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_guidField = 'level1_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/acsslevel1');
    }
}