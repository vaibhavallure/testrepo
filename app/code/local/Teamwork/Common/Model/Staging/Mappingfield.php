<?php

class Teamwork_Common_Model_Staging_Mappingfield extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_guidField = 'value';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/mappingfield');
    }
}