<?php

class Teamwork_CEGiftcards_Block_Sales_Order_View_Total extends Mage_Core_Block_Template
{

    public function initTotals()
    {
        $order = $this->getParentBlock()->getOrder();
        $appliedGCs = Mage::getModel('teamwork_cegiftcards/giftcard_link')->getCollection()->addOrderFilter($order);
        foreach($appliedGCs as $gc) {
            $giftcardAmountTotal = new Varien_Object(array(
                'code'  => 'teamwork_cegiftcards_' . $gc->getData('gc_code'),
                'label' => $this->__('Gift Card (%s)', $gc->getData('gc_code')),
                'value' => '-' . $gc->getData('amount'),
            ));
            $this->getParentBlock()->addTotal($giftcardAmountTotal);
        }

        return $this;
    }

}
