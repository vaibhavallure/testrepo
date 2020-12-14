<?php
class Doddle_Returns_Model_Resource_Order_Sync_Queue extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('doddle_returns/order_sync_queue', 'sync_id');
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract|void
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getCreatedAt()) {
            $object->setCreatedAt($this->formatDate(true));
        } else {
            $object->setUpdatedAt($this->formatDate(true));
        }

        parent::_beforeSave($object);
    }
}
