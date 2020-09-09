<?php
class Allure_TeamworkDam_Model_Resource_Process extends Mage_Core_Model_Resource_Db_Abstract{
    protected function _construct()
    {
        $this->_init('teamworkdam/process','process_id');
    }

}