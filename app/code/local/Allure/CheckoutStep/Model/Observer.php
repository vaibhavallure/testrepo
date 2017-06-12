<?php

class Allure_CheckoutStep_Model_Observer //extends Mage_Checkout_Model_Observer
{
	//after logout the customer change custom quote status
    public function changeCustsomQuoteStatus()
    {
    	//Mage::log('logout',Zend_log::DEBUG,'abc',true);
    	Mage::getModel('checkout/type_onepage')->changeCustsomQuoteStatus();
    }
   
}
