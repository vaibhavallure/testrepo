<?php

/**
 * One page checkout order review
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Allure_MultiCheckout_Block_Checkout_Onepage_Review_Info extends Mage_Sales_Block_Items_Abstract
{

    public function getItems ()
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
    }

    public function getTotals ()
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getTotals();
    }

    // new code added by mt-allure
    public function getOrderedItems ()
    {
        return Mage::getSingleton('allure_multicheckout/ordered_session')->getQuote()->getAllVisibleItems();
    }

    // new code added by mt-allure
    public function getBackorderedItems ()
    {
        return Mage::getSingleton('allure_multicheckout/backordered_session')->getQuote()->getAllVisibleItems();
    }
}
