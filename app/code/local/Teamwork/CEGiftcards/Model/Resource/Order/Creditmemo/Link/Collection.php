<?php

class Teamwork_CEGiftcards_Model_Resource_Order_Creditmemo_Link_Collection
    extends Teamwork_CEGiftcards_Model_Resource_Order_Link_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('teamwork_cegiftcards/order_creditmemo_link');
    }

    public function addCreditmemoFilter($creditmemo)
    {
        return $this->addObjectFilter($creditmemo);
    }

    public function addObjectFilter($object)
    {
        return $this->_addIdFilter($object, "creditmemo_id");
    }

}
