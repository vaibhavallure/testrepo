<?php

/**
 * Queue resource collection
 */
class Mage_Core_Model_Resource_Uniquesession_Customer_Session_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Internal constructor
     *
     * @return null
     */
    protected function _construct()
    {
        $this->_init('core/uniquesession_customer_session');
    }
}
