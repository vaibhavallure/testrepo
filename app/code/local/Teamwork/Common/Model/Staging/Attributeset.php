<?php

class Teamwork_Common_Model_Staging_Attributeset extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_guidField = 'attribute_set_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/attributeset');
    }
}