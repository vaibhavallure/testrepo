<?php

class Allure_MultiCheckout_Model_Observer
{
    // after logout the customer change custom quote status
    public function changeQuoteStatus ()
    {
        Mage::getModel('checkout/type_onepage')->changeCustomQuoteStatus();
    }
}
