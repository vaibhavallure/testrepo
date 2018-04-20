<?php
class Allure_Metadata_Model_Mysql4_Metadata extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("metadata/metadata", "id");
    }
}