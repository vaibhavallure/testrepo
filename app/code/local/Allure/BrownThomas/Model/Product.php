<?php

class Allure_BrownThomas_Model_Product extends Mage_Core_Model_Abstract{
    protected function _construct()
    {
        $this->_init('brownthomas/product');
    }

    protected function _beforeSave() {
        parent::_beforeSave();
        $currentTime = Varien_Date::now();
        if ((!$this->getRowId() || $this->isObjectNew()) && !$this->getCreatedDate()) {
            $this->setCreatedDate($currentTime);
        }
        $this->setUpdatedDate($currentTime);
        return $this;
    }

}