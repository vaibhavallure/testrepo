<?php
class Allure_PromoBox_Model_Resource_Banner extends Mage_Core_Model_Resource_Db_Abstract{
    protected function _construct()
    {
        $this->_init('promobox/Banner','id');
    }

}