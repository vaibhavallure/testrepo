<?php
class Doddle_Returns_Model_Resource_Order_Sync_Queue_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();

        $this->_init('doddle_returns/order_sync_queue');
    }
}
