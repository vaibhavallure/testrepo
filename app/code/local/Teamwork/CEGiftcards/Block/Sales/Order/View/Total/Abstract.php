<?php

abstract class Teamwork_CEGiftcards_Block_Sales_Order_View_Total_Abstract extends Mage_Core_Block_Template
{
    abstract protected function _getObjectData();

    public function initTotals()
    {
        $data = $this->_getObjectData();
        $object = $data['object'];
        $linkCollection = $data['link_collection'];

        $objectGCLinks = Mage::getResourceModel($linkCollection)->addObjectFilter($object);
        foreach($objectGCLinks as $objectGCLink) {
            $gcLink = Mage::getModel('teamwork_cegiftcards/giftcard_link')->load($objectGCLink->getData('gc_link_id'));
            $giftcardPaidTotal = new Varien_Object(array(
                'code'      => 'teamwork_cegiftcards_amount_' . $gcLink->getData('gc_code'),
                'label' => $this->__('Gift Card (%s)', $gcLink->getData('gc_code')),
                'value' => '-' . $objectGCLink->getData('amount_used'),
            ));
            $this->getParentBlock()->addTotal($giftcardPaidTotal);
        }

        return $this;
    }
}
