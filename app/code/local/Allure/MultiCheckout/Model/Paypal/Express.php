<?php
class Allure_MultiCheckout_Model_Paypal_Express extends Mage_Paypal_Model_Express
{
    //protected $_canUseForMultishipping = true;
    
    /**
     * Using for multiple shipping address
     *
     * @return bool
     */
    public function canUseForMultishipping()
    {
        $quote = Mage::getSingleton("checkout/session")->getQuote();
        if($quote->getIsMultiShipping()){
            $shippingAddresses = $quote->getAllShippingAddresses();
            if(count($shippingAddresses) == 1){
                return true;
            }
        }
        return false;
    }
}

