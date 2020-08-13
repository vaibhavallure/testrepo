<?php
/**
 * 
 * @author allure
 *
 */
class Allure_CustomUrl_Model_Resource_Url_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('allure_customurl/url');
    }

}
