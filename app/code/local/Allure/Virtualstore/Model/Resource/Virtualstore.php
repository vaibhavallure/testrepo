<?php
/**
 * Created by PhpStorm.
 * User: swapnil
 * Date: 8/14/18
 * Time: 6:37 PM
 */
class Allure_Virtualstore_Model_Resource_Virtualstore extends  Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init("allure_virtualstore/virtualstore", "store_id");
    }
}