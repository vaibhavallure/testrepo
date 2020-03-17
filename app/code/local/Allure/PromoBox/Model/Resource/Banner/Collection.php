<?php
class Allure_PromoBox_Model_Resource_Banner_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('promobox/banner');
    }
}