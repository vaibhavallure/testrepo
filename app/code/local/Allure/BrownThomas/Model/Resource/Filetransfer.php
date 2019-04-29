<?php
class Allure_BrownThomas_Model_Resource_Filetransfer extends Mage_Core_Model_Resource_Db_Abstract{
    protected function _construct()
    {
        $this->_init('brownthomas/filetransfer','row_id');
    }
}