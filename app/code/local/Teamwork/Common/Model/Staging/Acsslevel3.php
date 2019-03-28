<?php

class Teamwork_Common_Model_Staging_Acsslevel3 extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_guidField = 'level3_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/acsslevel3');
    }
}