<?php

class Allure_GeoLocation_Model_Mysql4_GeoInfo_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('allure_geolocation/geoinfo');
    }

    public function addIpFilter($ip)
    {
        $this->getSelect()->where('ip = ?', $id);
        return $this;
    }

    /**
     * Covers bug in Magento function
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
        return $countSelect->reset()->from($this->getSelect(), array())->columns('COUNT(*)');
    }
}