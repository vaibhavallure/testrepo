<?php

class Teamwork_CEGiftcards_Model_Resource_Giftcard_Link_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract//Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('teamwork_cegiftcards/giftcard_link');
    }

    public function addQuoteFilter($quote)
    {
        if ($quote instanceof Mage_Sales_Model_Quote) {
            $quoteId = $quote->getId();
        } else {
            $quoteId = $quote;
        }
        $this->addFieldToFilter("quote_id", $quoteId);
        return $this;
    }

    public function addGCFilter($gcCode)
    {
        $this->addFieldToFilter("gc_code", $gcCode);
        return $this;
    }

    public function _initSelect()
    {
        parent::_initSelect();
        $this->addOrder('position',  self::SORT_ORDER_ASC);
    }

    public function addOrderFilter($order)
    {
        if ($order instanceof Mage_Sales_Model_Order) {
            $orderId = $order->getId();
        } else {
            $orderId = $order;
        }
        $this->addFieldToFilter("order_id", $orderId);
        return $this;
    }

}
