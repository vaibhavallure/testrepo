<?php

abstract class Teamwork_CEGiftcards_Model_Resource_Order_Link_Collection_Abstract
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{

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

    protected function _addIdFilter($object, $columnName)
    {
        if ($object instanceof Mage_Sales_Model_Abstract) {
            $objectId = $object->getId();
        } else {
            $objectId = $object;
        }
        $this->addFieldToFilter($columnName, $objectId);
        return $this;
    }

    abstract public function addObjectFilter($object);

}
