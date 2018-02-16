<?php

class Teamwork_CEGiftcards_Block_Adminhtml_Sales_Order_Create_Gcpayment extends Mage_Core_Block_Template
{
    protected function _getOrderCreateModel()
    {
        return Mage::getSingleton('adminhtml/sales_order_create');
    }

    public function getGiftCards()
    {
        $quote = $this->_getOrderCreateModel()->getQuote();
        return Mage::getModel('teamwork_cegiftcards/giftcard_link')->getCollection()->addQuoteFilter($quote);
    }

    public function formatAmount($card)
    {
        return Mage::helper('core')->currency($card->getData('amount'), true, false);
    }

}
