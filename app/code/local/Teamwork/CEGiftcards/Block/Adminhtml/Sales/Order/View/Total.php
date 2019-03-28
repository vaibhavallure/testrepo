<?php

class Teamwork_CEGiftcards_Block_Adminhtml_Sales_Order_View_Total extends Mage_Adminhtml_Block_Sales_Order_Totals
{

    public function initTotals()
    {
        $order = $this->getOrder();
        $appliedGCs = Mage::getModel('teamwork_cegiftcards/giftcard_link')->getCollection()->addOrderFilter($order);
        foreach($appliedGCs as $gc) {
            $total = new Varien_Object(array(
                'code'  => 'teamwork_cegiftcards_' . $gc->getData('gc_code'),
                'label' => $this->__('Gift Card (%s)', $gc->getData('gc_code')),
                'value' => '-' . $gc->getData('amount'),
            ));
            $this->getParentBlock()->addTotal($total);
        }
        return $this;
    }

}
