<?php
class Allure_SmartAnalytics_Block_Checkout_Cart extends Mage_Core_Block_Template
{
    public function getItems()
    {
        return $this->getLayout()->getBlockSingleton('checkout/cart_crosssell')->getItems();
    }
}
