<?php

class Teamwork_CEGiftcards_Model_Resource_Giftcard_Transaction_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract//Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('teamwork_cegiftcards/giftcard_transaction');
    }

    public function addGCLinkFilter($GCLink)
    {
        if ($GCLink instanceof Teamwork_CEGiftcards_Model_Giftcard_Link) {
            $linkId = $GCLink->getId();
        } else {
            $linkId = $GCLink;
        }
        $this->addFieldToFilter("gc_link_id", $linkId);
        return $this;
    }
}
