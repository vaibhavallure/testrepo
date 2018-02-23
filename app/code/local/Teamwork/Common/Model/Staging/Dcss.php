<?php

class Teamwork_Common_Model_Staging_Dcss extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_guidField = 'dcss_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/dcss');
    }
}