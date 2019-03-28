<?php

class Teamwork_Common_Model_Staging_Dcsssubclass1 extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_guidField = 'subclass1_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/dcsssubclass1');
    }
}