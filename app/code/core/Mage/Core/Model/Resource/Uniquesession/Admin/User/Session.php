<?php

class Mage_Core_Model_Resource_Uniquesession_Admin_User_Session extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize admin user unique session resource model
     *
     */
    protected function _construct()
    {
        $this->_init('core/uniquesession_admin_user_session', 'id');
    }

    /**
     * Prepare object data for saving
     *
     * @param Mage_Core_Model_Uniquesession_Admin_User_Session|Mage_Core_Model_Abstract $object
     * @return self
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $object->setCreatedAt($this->formatDate(true));

        return parent::_beforeSave($object);
    }
}
