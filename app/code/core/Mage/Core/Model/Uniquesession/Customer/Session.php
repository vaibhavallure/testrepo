<?php

/**
 * Customer Session Model
 *
 * @method int getCustomerId()
 * @method string getSessionId()
 *
 * @method self setCustomerId(int $value)
 * @method self setSessionId(string $value)
 */
class Mage_Core_Model_Uniquesession_Customer_Session extends Mage_Core_Model_Abstract
{
    /**
     * Initialize object
     */
    protected function _construct()
    {
        $this->_init('core/uniquesession_customer_session');
    }
}
