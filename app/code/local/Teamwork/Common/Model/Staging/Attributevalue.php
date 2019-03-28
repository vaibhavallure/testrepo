<?php

class Teamwork_Common_Model_Staging_Attributevalue extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_guidField = 'attribute_value_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/attributevalue');
    }
}