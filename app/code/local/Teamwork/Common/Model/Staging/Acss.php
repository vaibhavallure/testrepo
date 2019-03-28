<?php

class Teamwork_Common_Model_Staging_Acss extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_guidField = 'acss_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/acss');
    }
}