<?php

class Teamwork_Common_Model_Staging_Acsslevel2 extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_guidField = 'level2_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/acsslevel2');
    }
}