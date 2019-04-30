<?php
class Allure_BrownThomas_Model_Resource_Product extends Mage_Core_Model_Resource_Db_Abstract{
    protected function _construct()
    {
        $this->_init('brownthomas/product','row_id');
    }
}