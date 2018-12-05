<?php
/*
 * Allure Inc
 * @author allure
 * 
 */

class Allure_MultiCheckout_Block_Paypal_Express_Review extends Mage_Paypal_Block_Express_Review
{
    public function getQuote(){
    	return $this->_quote;
    }
}
