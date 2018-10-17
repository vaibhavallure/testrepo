<?php
class Allure_SmartAnalytics_Block_Checkout_Onepage extends Mage_Core_Block_Template
{
    public function getCartItems()
    {
        $quote = Mage::getSingleton('checkout/type_onepage')->getQuote();
        $cartItems = $quote->getAllVisibleItems();

        return $cartItems;
    }
}
