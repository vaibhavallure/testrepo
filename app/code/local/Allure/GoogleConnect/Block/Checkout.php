<?php

class Allure_GoogleConnect_Block_Checkout extends Mage_Core_Block_Template
{
    protected $client = null;  
    protected $oauth2 = null;    
    
    protected function _construct() {
        parent::_construct();
        
        $model = Mage::getSingleton('allure_googleconnect/client');
        
        if(!($this->client = $model->getClient()) ||
                !($this->oauth2 = $model->getOauth2())) 
                return;
        
        $this->setTemplate('allure/googleconnect/checkout.phtml');
    }   
    
}
