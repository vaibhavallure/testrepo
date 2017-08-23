<?php

class Allure_MultiCheckout_Model_Observer
{
    // after logout the customer change custom quote status
    public function changeQuoteStatus ()
    {
        // Mage::log('logout',Zend_log::DEBUG,'abc',true);
        Mage::getModel('checkout/type_onepage')->changeCustsomQuoteStatus();
    }
}
