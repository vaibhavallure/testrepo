<?php

/**
 * Queue resource collection
 */
class Mage_Core_Model_Resource_Uniquesession_Admin_User_Session_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Internal constructor
     *
     * @return null
     */
    protected function _construct()
    {
        $this->_init('core/uniquesession_admin_user_session');
    }
}
