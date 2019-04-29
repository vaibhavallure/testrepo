<?php

class Allure_BrownThomas_Model_Filetransfer extends Mage_Core_Model_Abstract{
    protected function _construct()
    {
        $this->_init('brownthomas/filetransfer');
    }

    protected function _beforeSave() {
        parent::_beforeSave();
        $currentTime = Varien_Date::now();
        if ((!$this->getRowId() || $this->isObjectNew()) && !$this->getTransferDate()) {
            $this->setTransferDate($currentTime);
        }
        return $this;
    }

}