<?php
class Ebizmarts_BakerlooEmail_Model_Mysql4_Unsent extends Mage_Core_Model_Mysql4_Abstract
{

    protected function _construct()
    {
        $this->_init('bakerloo_email/unsent', 'id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getId()) {
            $object->setCreatedAt($this->formatDate(Mage::getModel('core/date')->gmtTimestamp()));
        }

        $object->setUpdatedAt($this->formatDate(Mage::getModel('core/date')->gmtTimestamp()));

        return $this;
    }
}
