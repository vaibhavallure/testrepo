<?php

class Teamwork_Common_Model_Staging_Identifier extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_guidField = 'identifier_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/identifier');
    }
}