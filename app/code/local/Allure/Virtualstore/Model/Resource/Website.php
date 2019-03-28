<?php

class Allure_Virtualstore_Model_Resource_Website extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('allure_virtualstore/website', 'website_id');
    }
    
}
