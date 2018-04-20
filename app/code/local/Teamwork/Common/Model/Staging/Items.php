<?php

class Teamwork_Common_Model_Staging_Items extends Teamwork_Common_Model_Staging_Abstractchanneled
{
    protected $_guidField = 'item_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/items');
    }
}