<?php

class Teamwork_Common_Model_Staging_Dcssdepartment extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_guidField = 'department_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/dcssdepartment');
    }
}