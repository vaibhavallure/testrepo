<?php

class Teamwork_Common_Model_Staging_Style extends Teamwork_Common_Model_Staging_Abstractchanneled
{
    protected $_guidField = 'style_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/style');
    }
}