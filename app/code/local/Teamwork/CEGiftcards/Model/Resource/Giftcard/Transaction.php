<?php

class Teamwork_CEGiftcards_Model_Resource_Giftcard_Transaction extends Mage_Core_Model_Mysql4_Abstract//Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('teamwork_cegiftcards/giftcard_transaction', 'entity_id');
    }

}
