<?php
/**
 * Created by allure.
 * User: indrajitpatil
 * Date: 28/03/19
 * Time: 15:30 PM
 */

class Allure_BrownThomas_Model_Resource_Product_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract{
    public function _construct()
    {
        parent::_construct();
        $this->_init('brownthomas/product');
    }
}