<?php

class Teamwork_Common_Model_Staging_Dcsssubclass2 extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_guidField = 'subclass2_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/dcsssubclass2');
    }
}