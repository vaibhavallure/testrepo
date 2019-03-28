<?php

abstract class Teamwork_CEGiftcards_Block_Adminhtml_Sales_Order_Total_Abstract
    extends Mage_Adminhtml_Block_Sales_Order_Totals
{

    abstract protected function _getKeyObject();

    public function initTotals()
    {
        $data = $this->_getKeyObject();
        $object = $data['object'];
        $key = $data['key'];
        $linkCollection = $data['link_collection'];

        /*if new page*/
        if ($object->hasData($key)) {
            $objectGCLinks = $object->getData($key);
        } else {
            /*else if view page*/
            $objectGCLinks = Mage::getResourceModel($linkCollection)->addObjectFilter($object);
        }
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
