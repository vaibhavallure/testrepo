<?php
/**
 * Created by PhpStorm.
 * User: ajay
 * Date: 2/8/17
 * Time: 4:47 PM
 */


class Allure_Inventory_Model_Resource_Customitem extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('inventory/customitem', 'id');
    }
}