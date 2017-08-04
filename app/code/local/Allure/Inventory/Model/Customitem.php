<?php
/**
 * Created by PhpStorm.
 * User: ajay
 * Date: 2/8/17
 * Time: 4:46 PM
 */

class Allure_Inventory_Model_Customitem extends Mage_Core_Model_Abstract
{
    protected  function _construct()
    {
        parent::_construct();
        $this->_init('inventory/customitem');
    }

}