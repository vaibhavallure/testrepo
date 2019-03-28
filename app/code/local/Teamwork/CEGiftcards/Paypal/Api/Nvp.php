<?php
class Teamwork_CEGiftcards_Paypal_Api_Nvp extends Mage_Paypal_Model_Api_Nvp
{
    protected function &_exportToRequest(array $privateRequestMap, array $request = array())
    {
        $request = parent::_exportToRequest($privateRequestMap, $request);
        $this->prepareTwRequest($request);
        
        return $request;
    }
    
    protected function prepareTwRequest(&$request)
    {
        // capture order with virtual giftcard in cart
        if( $this->_cart &&
            Mage::getStoreConfig(Teamwork_CEGiftcards_Model_Config_Source_Virtualcapture::CONFIG_PATH_VIRTUAL_CAPTURE) == Teamwork_CEGiftcards_Model_Config_Source_Virtualcapture::FORCE_PAYMENT &&
            Mage::helper('teamwork_cegiftcards/invoice')->checkVirtualProductInOrder($this->_cart->getSalesEntity()) &&
            (!empty($request['PAYMENTACTION']) && $request['PAYMENTACTION'] == Mage_Paypal_Model_Config::PAYMENT_ACTION_AUTH)
        )
        {
            $request['PAYMENTACTION'] = Mage_Paypal_Model_Config::PAYMENT_ACTION_SALE;
        }
    }
}