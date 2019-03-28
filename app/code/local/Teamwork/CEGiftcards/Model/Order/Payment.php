<?php

class Teamwork_CEGiftcards_Model_Order_Payment
{
    protected $_parentObject;
    
    public function __construct($parentObject)
    {
        $this->_parentObject = $parentObject;
    }
    
    public function getConfigPaymentAction()
    {
        return Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE;
    }
    
    public function __call($method, $args)
    {
        return call_user_func_array( array(&$this->_parentObject, $method), $args );
    }
}