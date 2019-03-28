<?php


class Teamwork_CEGiftcards_Block_Sales_Cart_Total extends Mage_Checkout_Block_Total_Default
{
    protected $_template = 'teamwork_cegiftcards/sales/cart/total.phtml';

    public function getQuoteGiftCards()
    {
        return $this->getData('total')->getData(Teamwork_CEGiftcards_Model_Quote_Address_Total::TOTAL_DATA_KEY);
    }
}
