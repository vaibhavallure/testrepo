<?php

/**
 * Admin User Session Model
 *
 * @method int getAdminUserId()
 * @method string getSessionId()
 *
 * @method self setAdminUserId(int $value)
 * @method self setSessionId(string $value)
 */
class Mage_Core_Model_Uniquesession_Admin_User_Session extends Mage_Core_Model_Abstract
{
    /**
     * Initialize object
     */
    protected function _construct()
    {
        $this->_init('core/uniquesession_admin_user_session');
    }
}
