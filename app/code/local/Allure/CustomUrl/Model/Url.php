<?php
/**
 * 
 * @author allure
 *
 */
class Allure_CustomUrl_Model_Url extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('allure_customurl/url');
    }
}

