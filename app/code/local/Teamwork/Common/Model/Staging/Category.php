<?php

class Teamwork_Common_Model_Staging_Category extends Teamwork_Common_Model_Staging_Abstractchanneled
{
    protected $_guidField = 'category_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/category');
    }
}