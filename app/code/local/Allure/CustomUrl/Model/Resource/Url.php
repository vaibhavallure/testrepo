<?php
/**
 * 
 * @author allure
 *
 */
class Allure_CustomUrl_Model_Resource_Url extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('allure_customurl/url', 'url_id');
    }
    
}
